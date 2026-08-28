<?php
session_start();

if(!isset($_SESSION['admin_id']))
{
    header("Location:admin_login.php");
    exit();
}

include("includes/db.php");

$search="";

$sql="SELECT
items.*,
users.full_name,
categories.category_name
FROM items
INNER JOIN users
ON items.user_id=users.user_id
INNER JOIN categories
ON items.category_id=categories.category_id";

if(isset($_GET['search']) && $_GET['search']!="")
{
    $search=mysqli_real_escape_string($conn,$_GET['search']);

    $sql.=" WHERE
    items.item_name LIKE '%$search%'
    OR users.full_name LIKE '%$search%'
    OR categories.category_name LIKE '%$search%'";
}

$sql.=" ORDER BY items.item_id DESC";

$query=mysqli_query($conn,$sql);
?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Manage Items</title>

<link rel="stylesheet"
href="css/manage_items.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body>

<div class="sidebar">

<h2>CampusShare</h2>

<ul>

<li>

<a href="admin_dashboard.php">

<i class="fa fa-gauge"></i>

Dashboard

</a>

</li>

<li>

<a href="manage_users.php">

<i class="fa fa-users"></i>

Manage Users

</a>

</li>

<li class="active">

<a href="manage_items.php">

<i class="fa fa-box"></i>

Manage Items

</a>

</li>

<li>

<a href="manage_requests.php">

<i class="fa fa-handshake"></i>

Borrow Requests

</a>

</li>

<li>

<a href="manage_categories.php">

<i class="fa fa-list"></i>

Categories

</a>

</li>

<li>

<a href="admin_logout.php">

<i class="fa fa-right-from-bracket"></i>

Logout

</a>

</li>

</ul>

</div>

<div class="main">

<header>

<h1>Manage Items</h1>

</header>

<form method="GET" class="search-box">

<input
type="text"
name="search"
placeholder="Search Item, Owner or Category"
value="<?php echo htmlspecialchars($search); ?>">

<button type="submit">

<i class="fa fa-search"></i>

Search

</button>

</form>

<table>

<tr>

<th>ID</th>

<th>Image</th>

<th>Item</th>

<th>Owner</th>

<th>Category</th>

<th>Status</th>

<th>Action</th>

</tr>
<tr>

<th>ID</th>

<th>Image</th>

<th>Item</th>

<th>Owner</th>

<th>Category</th>

<th>Status</th>

<th>Action</th>

</tr>
<?php

if(mysqli_num_rows($query) > 0)
{

while($item = mysqli_fetch_assoc($query))
{

?>

<tr>

<td><?php echo $item['item_id']; ?></td>

<td>

<?php

if(!empty($item['image']) && file_exists("uploads/items/".$item['image']))
{

?>

<img src="uploads/items/<?php echo $item['image']; ?>" width="70" height="70" style="object-fit:cover;border-radius:5px;">

<?php

}
else
{

?>

<img src="images/default.png" width="70" height="70">

<?php

}

?>

</td>

<td><?php echo htmlspecialchars($item['item_name']); ?></td>

<td><?php echo htmlspecialchars($item['full_name']); ?></td>

<td><?php echo htmlspecialchars($item['category_name']); ?></td>

<td><?php echo htmlspecialchars($item['availability']); ?></td>

<td>

<a href="delete_item.php?item_id=<?php echo $item['item_id']; ?>"
class="delete-btn"
onclick="return confirm('Are you sure you want to delete this item?');">

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

<td colspan="7" style="text-align:center;">

No Items Found

</td>

</tr>

<?php

}

?>

</table>

</div>

</body>

</html>