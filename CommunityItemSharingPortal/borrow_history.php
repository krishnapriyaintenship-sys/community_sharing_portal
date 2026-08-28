<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include("includes/db.php");

$user_id = $_SESSION['user_id'];

$query = mysqli_query($conn, "
SELECT
    br.request_id,
    i.item_name,
    u.full_name AS owner_name,
    br.borrow_date,
    br.expected_return_date,
    br.status,
    br.request_date
FROM borrow_requests br
JOIN items i ON br.item_id = i.item_id
JOIN users u ON br.owner_id = u.user_id
WHERE br.borrower_id = '$user_id'
ORDER BY br.request_date DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Borrow History</title>
    <link rel="stylesheet" href="css/borrow_history.css">
</head>
<body>

<div class="container">

<h2>Borrow History</h2>

<table>

<tr>
    <th>Request ID</th>
    <th>Item</th>
    <th>Owner</th>
    <th>Borrow Date</th>
    <th>Expected Return</th>
    <th>Status</th>
    <th>Requested On</th>
</tr>

<?php
if(mysqli_num_rows($query)>0)
{
    while($row=mysqli_fetch_assoc($query))
    {
?>
<tr>

<td><?php echo $row['request_id']; ?></td>

<td><?php echo htmlspecialchars($row['item_name']); ?></td>

<td><?php echo htmlspecialchars($row['owner_name']); ?></td>

<td>
<?php
echo $row['borrow_date'] ? $row['borrow_date'] : "-";
?>
</td>

<td>
<?php
echo $row['expected_return_date'] ? $row['expected_return_date'] : "-";
?>
</td>

<td><?php echo $row['status']; ?></td>

<td><?php echo $row['request_date']; ?></td>

</tr>

<?php
    }
}
else
{
?>
<tr>
<td colspan="7">No borrow history found.</td>
</tr>
<?php
}
?>

</table>

<br>

<a href="dashboard.php" class="btn">Back to Dashboard</a>

</div>

</body>
</html>