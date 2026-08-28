<?php
session_start();

if(!isset($_SESSION['user_id']))
{
    header("Location:login.php");
    exit();
}

include("includes/db.php");

$user_id=$_SESSION['user_id'];

/* Logged in User */

$user_query=mysqli_query($conn,
"SELECT * FROM users WHERE user_id='$user_id'");

$user=mysqli_fetch_assoc($user_query);

/* Borrow Requests */

$request_query=mysqli_query($conn,"
SELECT
borrow_requests.*,
items.item_name,
items.image,
users.full_name

FROM borrow_requests

INNER JOIN items
ON borrow_requests.item_id=items.item_id

INNER JOIN users
ON borrow_requests.owner_id=users.user_id

WHERE borrow_requests.borrower_id='$user_id'

ORDER BY borrow_requests.request_date DESC
");
?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>My Borrow Requests</title>

<link rel="stylesheet"
href="css/my_borrow_requests.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body>

<div class="sidebar">

<div class="logo">

<img src="images/logo.png">

<h2>CampusShare</h2>

</div>

<ul>

<li>
<a href="dashboard.php">
<i class="fa fa-house"></i>
Dashboard
</a>
</li>

<li>
<a href="browse_items.php">
<i class="fa fa-box"></i>
Browse Items
</a>
</li>

<li class="active">
<a href="my_borrow_requests.php">
<i class="fa fa-handshake"></i>
My Borrow Requests
</a>
</li>

<li>
<a href="logout.php">
<i class="fa fa-right-from-bracket"></i>
Logout
</a>
</li>

</ul>

</div>

<div class="main">

<header>

<h1>My Borrow Requests</h1>

<div class="profile">

<img src="images/default.png">

<span><?php echo $user['full_name']; ?></span>

</div>

</header>

<div class="request-container">
    <?php

if(mysqli_num_rows($request_query) > 0)
{

while($request = mysqli_fetch_assoc($request_query))
{

?>

<div class="request-card">

<div class="request-image">

<?php
if(!empty($request['image']) && file_exists("uploads/items/".$request['image']))
{
?>

<img src="uploads/items/<?php echo $request['image']; ?>" alt="Item Image">

<?php
}
else
{
?>

<img src="images/default.png" alt="No Image">

<?php
}
?>

</div>

<div class="request-details">

<h2><?php echo htmlspecialchars($request['item_name']); ?></h2>

<p>

<strong>Owner :</strong>

<?php echo htmlspecialchars($request['full_name']); ?>

</p>

<p>

<strong>Borrow Date :</strong>

<?php echo htmlspecialchars($request['borrow_date']); ?>

</p>

<p>

<strong>Expected Return :</strong>

<?php echo htmlspecialchars($request['expected_return_date']); ?>

</p>

<p>

<strong>Status :</strong>

<?php
$status = $request['status'];

if($status=="Pending")
{
    echo "<span class='pending'>Pending</span>";
}
elseif($status=="Approved")
{
    echo "<span class='approved'>Approved</span>";
}
elseif($status=="Rejected")
{
    echo "<span class='rejected'>Rejected</span>";
}
elseif($status=="Returned")
{
    echo "<span class='returned'>Returned</span>";
}
?>

</p>

</div>

</div>

<?php

}

}
else
{

?>

<div class="no-request">

<h2>No Borrow Requests</h2>

<p>You have not requested any items yet.</p>

</div>

<?php

}

?>

</div>

</div>

</body>

</html>
