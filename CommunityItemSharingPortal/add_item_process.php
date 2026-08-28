<?php
session_start();

include("includes/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* Get Form Data */

$item_name = mysqli_real_escape_string($conn, $_POST['item_name']);
$category_id = intval($_POST['category']);
$description = mysqli_real_escape_string($conn, $_POST['description']);
$item_condition = mysqli_real_escape_string($conn, $_POST['item_condition']);
$location = mysqli_real_escape_string($conn, $_POST['location']);

$image = "";

/* Upload Image */

if(isset($_FILES['item_image']) && $_FILES['item_image']['error'] == 0)
{

    if(!is_dir("uploads/items"))
    {
        mkdir("uploads/items",0777,true);
    }

    $image = time()."_".basename($_FILES['item_image']['name']);

    $target = "uploads/items/".$image;

    if(!move_uploaded_file($_FILES['item_image']['tmp_name'],$target))
    {
        die("Image upload failed.");
    }

}

/* Insert Item */

$sql = "INSERT INTO items
(
user_id,
category_id,
item_name,
description,
item_condition,
availability,
location,
image
)

VALUES
(
?,
?,
?,
?,
?,
'Available',
?,
?
)";

$stmt = mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt,
"iisssss",
$user_id,
$category_id,
$item_name,
$description,
$item_condition,
$location,
$image
);

if(mysqli_stmt_execute($stmt))
{

    echo "<script>
    alert('Item Added Successfully');
    window.location='my_items.php';
    </script>";

}
else
{

    echo "Database Error : ".mysqli_error($conn);

}

mysqli_stmt_close($stmt);

mysqli_close($conn);

?>