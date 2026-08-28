<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include("includes/db.php");

$user_id = $_SESSION['user_id'];

/* Check Item ID */

if (!isset($_GET['id']) || empty($_GET['id'])) {

    echo "<script>
    alert('Invalid Item ID');
    window.location='my_items.php';
    </script>";

    exit();
}

$item_id = intval($_GET['id']);

/* Get Item */

$query = mysqli_query($conn,"
SELECT *
FROM items
WHERE item_id='$item_id'
AND user_id='$user_id'
");

if (!$query) {

    die(mysqli_error($conn));

}

if (mysqli_num_rows($query) == 0) {

    echo "<script>
    alert('Item not found.');
    window.location='my_items.php';
    </script>";

    exit();
}

$item = mysqli_fetch_assoc($query);

/* Delete Image */

if (!empty($item['image'])) {

    $image_path = "uploads/items/" . $item['image'];

    if (file_exists($image_path)) {

        unlink($image_path);

    }

}

/* Delete Related Borrow Requests */

$delete_requests = mysqli_query($conn,"
DELETE FROM borrow_requests
WHERE item_id='$item_id'
");

if (!$delete_requests) {

    die("Borrow Request Delete Error : " . mysqli_error($conn));

}

/* Delete Item */

$delete_item = mysqli_query($conn,"
DELETE FROM items
WHERE item_id='$item_id'
AND user_id='$user_id'
");

if (!$delete_item) {

    die("Item Delete Error : " . mysqli_error($conn));

}

/* Success */

echo "<script>

alert('Item Deleted Successfully.');

window.location='my_items.php';

</script>";

exit();

?>