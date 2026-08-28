<?php
session_start();
include("includes/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ==========================================
   APPROVE REQUEST
========================================== */

if (isset($_GET['approve'])) {

    $request_id = intval($_GET['approve']);

    $query = mysqli_query($conn, "
    SELECT
        br.*,
        i.item_name,
        u.full_name
    FROM borrow_requests br
    INNER JOIN items i
        ON br.item_id = i.item_id
    INNER JOIN users u
        ON br.borrower_id = u.user_id
    WHERE br.request_id = '$request_id'
    LIMIT 1
    ");

    if (mysqli_num_rows($query) > 0) {

        $row = mysqli_fetch_assoc($query);

        // Approve Request
        mysqli_query($conn, "
        UPDATE borrow_requests
        SET
            status='Approved',
            approved_date=NOW()
        WHERE request_id='$request_id'
        ");

        // Make Item Unavailable
        mysqli_query($conn, "
        UPDATE items
        SET availability='Unavailable'
        WHERE item_id='".$row['item_id']."'
        ");

        // Notification
        $title = "Request Approved";
        $message = "Your borrow request for ".$row['item_name']." has been approved.";
        $type = "Approved";
        $status = "Unread";

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO notifications
            (user_id, request_id, title, message, type, status)
            VALUES (?, ?, ?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "iissss",
            $row['borrower_id'],
            $request_id,
            $title,
            $message,
            $type,
            $status
        );

        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    header("Location: manage_requests.php");
    exit();
}

/* ==========================================
   REJECT REQUEST
========================================== */

if (isset($_GET['reject'])) {

    $request_id = intval($_GET['reject']);

    $query = mysqli_query($conn, "
    SELECT
        br.*,
        i.item_name
    FROM borrow_requests br
    INNER JOIN items i
        ON br.item_id=i.item_id
    WHERE br.request_id='$request_id'
    LIMIT 1
    ");

    if (mysqli_num_rows($query) > 0) {

        $row = mysqli_fetch_assoc($query);

        mysqli_query($conn, "
        UPDATE borrow_requests
        SET status='Rejected'
        WHERE request_id='$request_id'
        ");

        $title = "Request Rejected";
        $message = "Your borrow request for ".$row['item_name']." has been rejected.";
        $type = "Rejected";
        $status = "Unread";

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO notifications
            (user_id, request_id, title, message, type, status)
            VALUES (?, ?, ?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "iissss",
            $row['borrower_id'],
            $request_id,
            $title,
            $message,
            $type,
            $status
        );

        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    header("Location: manage_requests.php");
    exit();
}

/* ==========================================
   LOAD REQUESTS
========================================== */

$sql = "
SELECT
    br.*,
    i.item_name,
    u.full_name
FROM borrow_requests br
INNER JOIN items i
    ON br.item_id = i.item_id
INNER JOIN users u
    ON br.borrower_id = u.user_id
WHERE br.owner_id='$user_id'
ORDER BY br.request_date DESC
";

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Manage Requests | CampusShare</title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:Arial,sans-serif;
}

body{
background:#f4f6fb;
}

.sidebar{
position:fixed;
left:0;
top:0;
width:240px;
height:100vh;
background:#2563eb;
padding-top:20px;
overflow:auto;
}

.sidebar h2{
color:#fff;
text-align:center;
margin-bottom:30px;
}

.sidebar a{
display:block;
padding:15px 25px;
color:#fff;
text-decoration:none;
font-size:16px;
transition:.3s;
}

.sidebar a i{
width:25px;
}

.sidebar a:hover,
.sidebar .active{
background:#1d4ed8;
}

.main{
margin-left:240px;
padding:30px;
}

.main h2{
margin-bottom:25px;
color:#222;
}

table{
width:100%;
background:#fff;
border-collapse:collapse;
border-radius:10px;
overflow:hidden;
box-shadow:0 4px 10px rgba(0,0,0,.1);
}

th{
background:#2563eb;
color:#fff;
padding:15px;
}

td{
padding:15px;
border-bottom:1px solid #ddd;
text-align:center;
}

.approve{
display:inline-block;
padding:8px 15px;
background:#16a34a;
color:#fff;
text-decoration:none;
border-radius:5px;
margin:2px;
}

.reject{
display:inline-block;
padding:8px 15px;
background:#dc2626;
color:#fff;
text-decoration:none;
border-radius:5px;
margin:2px;
}

.return{
display:inline-block;
padding:8px 15px;
background:#2563eb;
color:#fff;
text-decoration:none;
border-radius:5px;
margin:2px;
}

.pending{
color:#f59e0b;
font-weight:bold;
}

.approved{
color:#16a34a;
font-weight:bold;
}

.rejected{
color:#dc2626;
font-weight:bold;
}

.returned{
color:#2563eb;
font-weight:bold;
}

</style>

</head>

<body>

<div class="sidebar">

<h2>CampusShare</h2>

<a href="dashboard.php">
<i class="fa fa-house"></i>
Dashboard
</a>

<a href="browse_items.php">
<i class="fa fa-box"></i>
Browse Items
</a>

<a href="my_items.php">
<i class="fa fa-book"></i>
My Items
</a>

<a href="manage_requests.php" class="active">
<i class="fa fa-handshake"></i>
Manage Requests
</a>

<a href="notifications.php">
<i class="fa fa-bell"></i>
Notifications
</a>

<a href="profile.php">
<i class="fa fa-user"></i>
Profile
</a>

<a href="logout.php">
<i class="fa fa-right-from-bracket"></i>
Logout
</a>

</div>

<div class="main">

<h2>Manage Borrow Requests</h2>

<table>

<tr>

<th>Item</th>

<th>Borrower</th>

<th>Borrow Date</th>

<th>Expected Return</th>

<th>Status</th>

<th>Action</th>

</tr>
<?php

if(mysqli_num_rows($result) > 0)
{

while($row = mysqli_fetch_assoc($result))
{

?>

<tr>

<td>

<?php echo htmlspecialchars($row['item_name']); ?>

</td>

<td>

<?php echo htmlspecialchars($row['full_name']); ?>

</td>

<td>

<?php echo date("d-m-Y", strtotime($row['borrow_date'])); ?>

</td>

<td>

<?php echo date("d-m-Y", strtotime($row['expected_return_date'])); ?>

</td>

<td>

<span class="<?php echo strtolower($row['status']); ?>">

<?php echo htmlspecialchars($row['status']); ?>

</span>

</td>

<td>

<?php

if($row['status']=="Pending")
{

?>

<a
class="approve"
href="manage_requests.php?approve=<?php echo $row['request_id']; ?>"
onclick="return confirm('Approve this borrow request?');">

Approve

</a>

<a
class="reject"
href="manage_requests.php?reject=<?php echo $row['request_id']; ?>"
onclick="return confirm('Reject this borrow request?');">

Reject

</a>

<?php

}
elseif($row['status']=="Approved")
{

?>

<a
class="return"
href="return_item.php?request_id=<?php echo $row['request_id']; ?>"
onclick="return confirm('Mark this item as returned?');">

Mark Returned

</a>

<?php

}
elseif($row['status']=="Returned")
{

echo "<span class='returned'>Returned</span>";

}
elseif($row['status']=="Rejected")
{

echo "<span class='rejected'>Rejected</span>";

}

?>

</td>

</tr>

<?php

}

}
else
{

?>

<tr>

<td colspan="6">

No Borrow Requests Found

</td>

</tr>

<?php

}

?>

</table>

</div>

</body>

</html>
