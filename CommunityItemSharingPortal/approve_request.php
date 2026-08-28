<?php
session_start();
include("includes/db.php");

if(!isset($_GET['request_id']))
{
    header("Location:manage_requests.php");
    exit();
}

$request_id=$_GET['request_id'];

/* Get Item */

$result=mysqli_query($conn,"
SELECT item_id
FROM borrow_requests
WHERE request_id='$request_id'
");

$row=mysqli_fetch_assoc($result);

$item_id=$row['item_id'];

/* Update Request */

mysqli_query($conn,"
UPDATE borrow_requests
SET status='Approved'
WHERE request_id='$request_id'
");

/* Update Item */

mysqli_query($conn,"
UPDATE items
SET availability='Borrowed'
WHERE item_id='$item_id'
");

echo "<script>
alert('Request Approved Successfully');
window.location='manage_requests.php';
</script>";
?>