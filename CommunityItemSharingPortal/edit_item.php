<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include("includes/db.php");

$user_id = $_SESSION['user_id'];

/* Check Item ID */

if (!isset($_GET['item_id']) || empty($_GET['item_id'])) {
    die("Invalid Item ID");
}

$item_id = intval($_GET['item_id']);

/* Get Item Details */

$query = mysqli_query($conn,"
SELECT *
FROM items
WHERE item_id='$item_id'
AND user_id='$user_id'
");

if(mysqli_num_rows($query)==0)
{
    die("Item not found.");
}

$item = mysqli_fetch_assoc($query);

/* Update Item */

if(isset($_POST['update_item']))
{

$item_name = mysqli_real_escape_string($conn,$_POST['item_name']);

$category_id = intval($_POST['category_id']);

$description = mysqli_real_escape_string($conn,$_POST['description']);

$item_condition = mysqli_real_escape_string($conn,$_POST['item_condition']);

$location = mysqli_real_escape_string($conn,$_POST['location']);

$availability = mysqli_real_escape_string($conn,$_POST['availability']);

$image = $item['image'];
/* Upload New Image */

if(isset($_FILES['image']) && $_FILES['image']['name'] != "")
{

    $image = time()."_".$_FILES['image']['name'];

    move_uploaded_file(

        $_FILES['image']['tmp_name'],

        "uploads/items/".$image

    );

}
$update = mysqli_query($conn,"
UPDATE items SET

item_name='$item_name',

category_id='$category_id',

description='$description',

item_condition='$item_condition',

location='$location',

availability='$availability',

image='$image'

WHERE item_id='$item_id'
");

if($update)
{

echo "<script>

alert('Item Updated Successfully');

window.location='my_items.php';

</script>";

exit();

}
else
{

echo "<script>

alert('Failed to Update Item');

</script>";

}

}
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Edit Item</title>

<link rel="stylesheet"
href="css/edit_item.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body>

<div class="container">

<h2>

<i class="fa fa-pen-to-square"></i>

Edit Item

</h2>

<form method="POST" enctype="multipart/form-data">

<label>Item Name</label>

<input
type="text"
name="item_name"
value="<?php echo htmlspecialchars($item['item_name']); ?>"
required>

<label>Category</label>

<select name="category_id" required>

<?php

$cat = mysqli_query($conn,"
SELECT *
FROM categories
ORDER BY category_name
");

while($row = mysqli_fetch_assoc($cat))
{

?>

<option
value="<?php echo $row['category_id']; ?>"
<?php if($row['category_id']==$item['category_id']) echo "selected"; ?>>

<?php echo htmlspecialchars($row['category_name']); ?>

</option>

<?php

}

?>

</select>

<label>Description</label>

<textarea
name="description"
rows="5"
required><?php echo htmlspecialchars($item['description']); ?></textarea>

<label>Condition</label>

<select name="item_condition" required>

<option value="New"
<?php if($item['item_condition']=="New") echo "selected"; ?>>
New
</option>

<option value="Good"
<?php if($item['item_condition']=="Good") echo "selected"; ?>>
Good
</option>

<option value="Fair"
<?php if($item['item_condition']=="Fair") echo "selected"; ?>>
Fair
</option>

</select>

<label>Location</label>

<input
type="text"
name="location"
value="<?php echo htmlspecialchars($item['location']); ?>"
required>
<label>Availability</label>

<select name="availability" required>

<option value="Available"
<?php if($item['availability']=="Available") echo "selected"; ?>>
Available
</option>

<option value="Borrowed"
<?php if($item['availability']=="Borrowed") echo "selected"; ?>>
Borrowed
</option>

</select>

<br><br>

<label>Current Image</label>

<br><br>

<?php

if(!empty($item['image']) && file_exists("uploads/items/".$item['image']))
{

?>

<img
src="uploads/items/<?php echo htmlspecialchars($item['image']); ?>"
width="180"
height="180"
style="border:1px solid #ccc;border-radius:10px;object-fit:cover;">

<?php

}
else
{

?>

<img
src="images/default.png"
width="180"
height="180"
style="border:1px solid #ccc;border-radius:10px;object-fit:cover;">

<?php

}

?>

<br><br>

<label>Choose New Image (Optional)</label>

<input
type="file"
name="image"
accept="image/*">

<br><br>

<button
type="submit"
name="update_item"
class="update-btn">

<i class="fa fa-save"></i>

Update Item

</button>

&nbsp;

<a
href="my_items.php"
class="back-btn">

<i class="fa fa-arrow-left"></i>

Back

</a>

</form>

</div>

</body>

</html>