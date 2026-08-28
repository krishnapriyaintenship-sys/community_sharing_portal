<?php
session_start();

include("includes/db.php");

/* Add Category */

if (isset($_POST['add_category'])) {

    $category_name = trim($_POST['category_name']);

    if ($category_name != "") {

        $check = mysqli_query($conn,
        "SELECT * FROM categories
         WHERE category_name='$category_name'");

        if (mysqli_num_rows($check) > 0) {

            echo "<script>
            alert('Category already exists.');
            </script>";

        } else {

            mysqli_query($conn,
            "INSERT INTO categories(category_name)
             VALUES('$category_name')");

            echo "<script>
            alert('Category Added Successfully');
            window.location='manage_categories.php';
            </script>";

            exit();
        }
    }
}

/* Get Categories */

$query = mysqli_query($conn,
"SELECT *
 FROM categories
 ORDER BY category_id DESC");
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Manage Categories</title>

<link rel="stylesheet"
href="css/manage_categories.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body>

<div class="main">

<header>

<h1>

<i class="fa fa-list"></i>

Manage Categories

</h1>

</header>

<div class="add-box">

<form method="POST">

<input
type="text"
name="category_name"
placeholder="Enter Category Name"
required>

<button
type="submit"
name="add_category">

<i class="fa fa-plus"></i>

Add Category

</button>

</form>

</div>

<table>

<tr>

<th>ID</th>

<th>Category Name</th>

<th>Action</th>

</tr>

<?php

if(mysqli_num_rows($query)>0)
{

while($category=mysqli_fetch_assoc($query))
{

?>

<tr>

<td>

<?php echo $category['category_id']; ?>

</td>

<td>

<?php echo htmlspecialchars($category['category_name']); ?>

</td>

<td>

<a
href="edit_category.php?category_id=<?php echo $category['category_id']; ?>"
class="edit-btn">

<i class="fa fa-pen"></i>

Edit

</a>

&nbsp;

<a
href="delete_category.php?category_id=<?php echo $category['category_id']; ?>"
class="delete-btn"
onclick="return confirm('Are you sure you want to delete this category?');">

<i class="fa fa-trash"></i>

Delete

</a>

</td>

</tr>

<?php

}

}
else
{

?>

<tr>

<td colspan="3" style="text-align:center;">

No Categories Found

</td>

</tr>

<?php

}

?>

</table>

<br>

<p>

<a href="dashboard.php">

← Back to Dashboard

</a>

</p>

</div>

</body>

</html>