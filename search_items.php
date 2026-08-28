<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location:login.php");
    exit();
}

include("includes/db.php");

$user_id = $_SESSION['user_id'];

/* Logged-in user */
$user_query = mysqli_query($conn,
"SELECT * FROM users WHERE user_id='$user_id'");
$user = mysqli_fetch_assoc($user_query);

/* Load Categories */
$category_query = mysqli_query($conn,
"SELECT * FROM categories ORDER BY category_name ASC");

/* Search Values */
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : "";
$category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : "";
$location = isset($_GET['location']) ? mysqli_real_escape_string($conn, $_GET['location']) : "";

/* Search Query */
$sql = "
SELECT
items.*,
users.full_name,
categories.category_name
FROM items
INNER JOIN users
ON items.user_id=users.user_id
INNER JOIN categories
ON items.category_id=categories.category_id
WHERE items.availability='Available'
";

if($search!=""){
    $sql .= " AND items.item_name LIKE '%$search%'";
}

if($category!=""){
    $sql .= " AND items.category_id='$category'";
}

if($location!=""){
    $sql .= " AND items.location LIKE '%$location%'";
}

$sql .= " ORDER BY items.item_id DESC";

$item_query = mysqli_query($conn,$sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Search Items</title>

<link rel="stylesheet"
href="css/search_items.css">

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

<li class="active">
<a href="search_items.php">
<i class="fa fa-search"></i>
Search Items
</a>
</li>

<li>
<a href="browse_items.php">
<i class="fa fa-box"></i>
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

<h1>Search Available Items</h1>

<div class="profile">

<img src="images/default.png">

<span><?php echo htmlspecialchars($user['full_name']); ?></span>

</div>

</header>

<form method="GET" class="search-form">
    <div class="search-box">

<input
type="text"
name="search"
placeholder="Search Item Name..."
value="<?php echo htmlspecialchars($search); ?>">

<select name="category">

<option value="">All Categories</option>

<?php
while($cat=mysqli_fetch_assoc($category_query))
{
?>

<option value="<?php echo $cat['category_id']; ?>"
<?php if($category==$cat['category_id']) echo "selected"; ?>>

<?php echo htmlspecialchars($cat['category_name']); ?>

</option>

<?php
}
?>

</select>

<input
type="text"
name="location"
placeholder="Location"
value="<?php echo htmlspecialchars($location); ?>">

<button type="submit">

<i class="fa fa-search"></i>

Search

</button>

</div>

</form>

<div class="items-container">

<?php

if(mysqli_num_rows($item_query)>0)
{

while($item=mysqli_fetch_assoc($item_query))
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

<strong>Owner:</strong>

<?php echo htmlspecialchars($item['full_name']); ?>

</p>

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

<strong>Description:</strong>

<?php echo nl2br(htmlspecialchars($item['description'])); ?>

</p>

<div class="buttons">

<?php
if($item['user_id']==$user_id)
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

<?php
}
?>

<a href="item_details.php?item_id=<?php echo $item['item_id']; ?>" class="view-btn">

<i class="fa fa-eye"></i>

View Details

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

<p>No items match your search criteria.</p>

</div>

<?php

}

?>

</div>

</div>

</body>

</html>
