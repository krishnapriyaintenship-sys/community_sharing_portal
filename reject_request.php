<?php
session_start();

include("includes/db.php");

if(!isset($_GET['request_id']))
{
    header("Location:manage_requests.php");
    exit();
}

$request_id=$_GET['request_id'];

mysqli_query($conn,"
UPDATE borrow_requests
SET status='Rejected'
WHERE request_id='$request_id'
");

echo "<script>
alert('Request Rejected');
window.location='manage_requests.php';
</script>";
?>