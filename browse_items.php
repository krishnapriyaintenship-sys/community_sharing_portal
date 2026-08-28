<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include("includes/db.php");

$user_id = $_SESSION['user_id'];

/* Logged-in User */
$user_query = mysqli_query($conn, "SELECT * FROM users WHERE user_id='$user_id'");
$user = mysqli_fetch_assoc($user_query);

/* Fetch Available Items */
$item_query = mysqli_query($conn, "
SELECT
    items.*,
    users.full_name,
    categories.category_name
FROM items
INNER JOIN users
    ON items.user_id = users.user_id
INNER JOIN categories
    ON items.category_id = categories.category_id
WHERE items.availability='Available'
ORDER BY items.item_id DESC
");
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Browse Items | CampusShare</title>

<link rel="stylesheet" href="css/browse_items.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body>

<!-- Sidebar -->

<div class="sidebar">

<div class="logo">

<img src="images/logo.png" alt="Logo">

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

<h1>Browse Available Items</h1>

<div class="profile">

<img src="images/default.png" alt="Profile">

<span><?php echo htmlspecialchars($user['full_name']); ?></span>

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

<img src="uploads/items/<?php echo htmlspecialchars($item['image']); ?>" alt="Item">

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
<strong>Owner :</strong>
<?php echo htmlspecialchars($item['full_name']); ?>
</p>

<p>
<strong>Category :</strong>
<?php echo htmlspecialchars($item['category_name']); ?>
</p>

<p>
<strong>Condition :</strong>
<?php echo htmlspecialchars($item['item_condition']); ?>
</p>

<p>
<strong>Location :</strong>
<?php echo htmlspecialchars($item['location']); ?>
</p>

<p>
<strong>Status :</strong>
<span class="status">
<?php echo htmlspecialchars($item['availability']); ?>
</span>
</p>

<p>
<strong>Description :</strong>
<?php echo nl2br(htmlspecialchars($item['description'])); ?>
</p>

<div class="buttons">

<?php
if($item['user_id'] == $user_id)
{
?>

<button class="borrow-btn" disabled>
Your Item
</button>

<?php
}
else
{
?>

<a href="borrow_request.php?item_id=<?php echo $item['item_id']; ?>" class="borrow-btn">

<i class="fa fa-handshake"></i>

Borrow

</a>

<a href="item_details.php?item_id=<?php echo $item['item_id']; ?>" class="view-btn">

<i class="fa fa-eye"></i>

View Details

</a>

<?php
}
?>

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

<h2>No Items Available</h2>

<p>There are currently no available items.</p>

</div>

<?php

}

?>
</div>

</div>

</body>

</html>
