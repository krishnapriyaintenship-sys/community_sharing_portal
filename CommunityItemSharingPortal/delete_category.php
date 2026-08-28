<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

include("includes/db.php");

if(isset($_GET['category_id']))
{
    $category_id = (int)$_GET['category_id'];

    // Check if category is used by any item
    $check = mysqli_query($conn,"
    SELECT COUNT(*) AS total
    FROM items
    WHERE category_id='$category_id'
    ");

    $row = mysqli_fetch_assoc($check);

    if($row['total'] > 0)
    {
        echo "<script>
        alert('Cannot delete this category because it is used by one or more items.');
        window.location='manage_categories.php';
        </script>";
        exit();
    }

    mysqli_query($conn,"
    DELETE FROM categories
    WHERE category_id='$category_id'
    ");

    echo "<script>
    alert('Category deleted successfully.');
    window.location='manage_categories.php';
    </script>";
}
else
{
    header("Location: manage_categories.php");
    exit();
}
?>