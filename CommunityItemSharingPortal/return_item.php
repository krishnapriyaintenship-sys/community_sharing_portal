<?php
session_start();
include("includes/db.php");

/* ===========================
   Check Login
=========================== */

if(!isset($_SESSION['user_id']))
{
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ===========================
   Check Request ID
=========================== */

if(!isset($_GET['request_id']))
{
    header("Location: manage_requests.php");
    exit();
}

$request_id = intval($_GET['request_id']);

/* ===========================
   Get Request Details
=========================== */

$query = mysqli_query($conn,"
SELECT
br.*,
i.item_name
FROM borrow_requests br
INNER JOIN items i
ON br.item_id=i.item_id
WHERE br.request_id='$request_id'
");

if(mysqli_num_rows($query)==0)
{
    die("Invalid Request.");
}

$request = mysqli_fetch_assoc($query);

/* ===========================
   Security Check
=========================== */

if($request['owner_id'] != $user_id)
{
    die("Access Denied.");
}

/* ===========================
   Already Returned
=========================== */

if($request['status']=="Returned")
{
    echo "<script>
    alert('This item has already been returned.');
    window.location='manage_requests.php';
    </script>";
    exit();
}

/* ===========================
   Only Approved Requests
=========================== */

if($request['status']!="Approved")
{
    echo "<script>
    alert('Only approved requests can be returned.');
    window.location='manage_requests.php';
    </script>";
    exit();
}

$item_id = $request['item_id'];
/* ===========================
   Update Borrow Request
=========================== */

$update_request = mysqli_query($conn,"
UPDATE borrow_requests
SET
status='Returned',
actual_return_date=CURDATE(),
returned_date=NOW()
WHERE request_id='$request_id'
");

/* ===========================
   Update Item Availability
=========================== */

$update_item = mysqli_query($conn,"
UPDATE items
SET availability='Available'
WHERE item_id='$item_id'
");

/* ===========================
   Check Update Success
=========================== */

if(!$update_request || !$update_item)
{
    die("Database Error : " . mysqli_error($conn));
}
/* ===========================
   Create Notification
=========================== */

$title = "Item Returned";

$message = "The item \"" . $request['item_name'] . "\" has been marked as returned successfully.";

// Insert notification safely
$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO notifications
    (
        user_id,
        request_id,
        title,
        message,
        type,
        status
    )
    VALUES
    (
        ?, ?, ?, ?, 'Returned', 'Unread'
    )"
);

mysqli_stmt_bind_param(
    $stmt,
    "iiss",
    $request['borrower_id'],
    $request_id,
    $title,
    $message
);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);
/* ===========================
   Success Message
=========================== */

echo "<script>
alert('Item returned successfully.');
window.location='manage_requests.php';
</script>";

exit();

?>
