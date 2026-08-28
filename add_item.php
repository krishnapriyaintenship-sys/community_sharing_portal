<?php
session_start();

if(!isset($_SESSION['user_id']))
{
    header("Location: login.php");
    exit();
}

include("includes/db.php");

$user_id = $_SESSION['user_id'];

/* Get Logged-in User */

$user_query = mysqli_query($conn,
"SELECT * FROM users WHERE user_id='$user_id'");

$user = mysqli_fetch_assoc($user_query);

/* Get Categories */

$category_query = mysqli_query($conn,
"SELECT * FROM categories ORDER BY category_name ASC");

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Add Item | CampusShare</title>

<link rel="stylesheet"
href="css/add_item.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
rel="stylesheet">

</head>

<body>

<!-- Sidebar -->

<div class="sidebar">

<div class="logo">

<img src="images/logo.png">

<h2>CampusShare</h2>

</div>

<ul>

<li>

<a href="dashboard.php">

<i class="fa fa-house"></i>

Dashboard

</a>

</li>

<li class="active">

<a href="add_item.php">

<i class="fa fa-plus"></i>

Add Item

</a>

</li>

<li>

<a href="browse_items.php">

<i class="fa fa-box"></i>

Browse Items

</a>

</li>

<li>

<a href="my_items.php">

<i class="fa fa-book"></i>

My Items

</a>

</li>

<li>

<a href="profile.php">

<i class="fa fa-user"></i>

Profile

</a>

</li>

<li>

<a href="logout.php">

<i class="fa fa-right-from-bracket"></i>

Logout

</a>

</li>

</ul>

</div>

<!-- Main Content -->

<div class="main">

<header>

<h1>

Add New Item

</h1>

<div class="profile">
    <?php

if(!empty($user['profile_image']))
{

?>

<img src="uploads/<?php echo $user['profile_image']; ?>">

<?php

}
else
{

?>

<img src="images/default.png">

<?php

}

?>

<span>

<?php echo $user['full_name']; ?>

</span>

</div>

</header>

<div class="form-container">

<form

action="add_item_process.php"

method="POST"

enctype="multipart/form-data">

<div class="input-box">

<label>

Item Name

</label>

<input

type="text"

name="item_name"

placeholder="Enter Item Name"

required>

</div>

<div class="input-box">

<label>

Category

</label>

<select

name="category"

required>

<option value="">

Select Category

</option>

<?php

while($cat=mysqli_fetch_assoc($category_query))
{

?>

<option value="<?php echo $cat['category_id'];?>">

<?php echo $cat['category_name'];?>

</option>

<?php

}

?>

</select>

</div>

<div class="input-box">

<label>

Description

</label>

<textarea

name="description"

rows="5"

required

placeholder="Describe your item"></textarea>

</div>

<div class="row">

<div class="input-box">

<label>

Condition

</label>

<select name="item_condition">

<option>New</option>

<option>Like New</option>

<option>Good</option>

<option>Fair</option>

</select>

</div>

<div class="input-box">

<label>

Location

</label>

<input

type="text"

name="location"

required

placeholder="Library / Hostel / Department">

</div>

</div>

<div class="input-box">

<label>

Upload Image

</label>

<input

type="file"

name="item_image"

accept=".jpg,.jpeg,.png"

required>

</div>

<button type="submit">

<i class="fa fa-upload"></i>

Add Item

</button>

</form>

</div>
</div>
</div>

<script src="js/add_item.js"></script>

</body>

</html>