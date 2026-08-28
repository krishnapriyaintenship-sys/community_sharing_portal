<?php
session_start();

if(!isset($_SESSION['user_id']))
{
    header("Location: login.php");
    exit();
}

include("includes/db.php");

$user_id = $_SESSION['user_id'];

$user_query = mysqli_query($conn,
"SELECT * FROM users WHERE user_id='$user_id'");

$user = mysqli_fetch_assoc($user_query);

$item_query = mysqli_query($conn,"
SELECT items.*, categories.category_name
FROM items
INNER JOIN categories
ON items.category_id = categories.category_id
WHERE items.user_id='$user_id'
ORDER BY items.item_id DESC
");
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>My Items</title>

<link rel="stylesheet"
href="css/my_items.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body>

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

<li>
<a href="add_item.php">
<i class="fa fa-plus"></i>
Add Item
</a>
</li>

<li class="active">
<a href="my_items.php">
<i class="fa fa-box"></i>
My Items
</a>
</li>

<li>
<a href="browse_items.php">
<i class="fa fa-search"></i>
Browse Items
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

<div class="main">

<header>

<h1>My Shared Items</h1>

<div class="profile">

<img src="images/default.png">

<span><?php echo $user['full_name']; ?></span>

</div>

</header>

<div class="items-container">
<?php

if(mysqli_num_rows($item_query) > 0)
{

while($item = mysqli_fetch_assoc($item_query))
{

?>

<div class="item-card">

<div class="item-image">

<?php

if(!empty($item['image']) && file_exists("uploads/items/".$item['image']))
{

?>

<img src="uploads/items/<?php echo $item['image']; ?>" alt="Item Image">

<?php

}
else
{

?>

<img src="images/default.png" alt="No Image">

<?php

}

?>

</div>

<div class="item-details">

<h2><?php echo htmlspecialchars($item['item_name']); ?></h2>

<p>

<strong>Category:</strong>

<?php echo htmlspecialchars($item['category_name']); ?>

</p>

<p>

<strong>Condition:</strong>

<?php echo htmlspecialchars($item['item_condition']); ?>

</p>

<p>

<strong>Location:</strong>

<?php echo htmlspecialchars($item['location']); ?>

</p>

<p>

<strong>Status:</strong>

<span class="status">

<?php echo htmlspecialchars($item['availability']); ?>

</span>

</p>

<div class="buttons">

<a href="edit_item.php?item_id=<?php echo $item['item_id']; ?>" class="edit-btn">
    <i class="fa fa-pen"></i> Edit
</a>


<a href="delete_item.php?id=<?php echo $item['item_id']; ?>"
class="delete-btn"
onclick="return confirm('Are you sure you want to delete this item?');">

<i class="fa fa-trash"></i>

Delete

</a>

</div>

</div>

</div>

<?php

}

}
else
{

?>

<div class="no-items">

<h2>No Items Found</h2>

<p>You haven't added any items yet.</p>

<a href="add_item.php" class="add-btn">

<i class="fa fa-plus"></i>

Add Your First Item

</a>

</div>

<?php

}

?>
</div>

</div>

</body>

</html>
