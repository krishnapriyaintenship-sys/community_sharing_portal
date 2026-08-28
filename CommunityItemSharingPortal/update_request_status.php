<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

include("includes/db.php");

if(isset($_GET['id']) && isset($_GET['status']))
{

    $request_id = (int)$_GET['id'];
    $status = mysqli_real_escape_string($conn,$_GET['status']);

    // Get request details
    $query = mysqli_query($conn,"
    SELECT item_id
    FROM borrow_requests
    WHERE request_id='$request_id'
    ");

    if(mysqli_num_rows($query)==0)
    {
        echo "<script>
        alert('Request not found.');
        window.location='manage_requests.php';
        </script>";
        exit();
    }

    $request = mysqli_fetch_assoc($query);

    $item_id = $request['item_id'];

    // Update borrow request status
    mysqli_query($conn,"
    UPDATE borrow_requests
    SET status='$status'
    WHERE request_id='$request_id'
    ");

    // Update item availability
    if($status=="Approved")
    {
        mysqli_query($conn,"
        UPDATE items
        SET availability='Borrowed'
        WHERE item_id='$item_id'
        ");
    }

    if($status=="Returned")
    {
        mysqli_query($conn,"
        UPDATE items
        SET availability='Available'
        WHERE item_id='$item_id'
        ");
    }

    if($status=="Rejected")
    {
        mysqli_query($conn,"
        UPDATE items
        SET availability='Available'
        WHERE item_id='$item_id'
        ");
    }

    echo "<script>
    alert('Request updated successfully.');
    window.location='manage_requests.php';
    </script>";

}
else
{

    header("Location:manage_requests.php");
    exit();

}
?>