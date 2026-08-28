<?php
session_start();

if(!isset($_SESSION['user_id']))
{
    header("Location: login.php");
    exit();
}

include("includes/db.php");

$user_id = $_SESSION['user_id'];

if(!isset($_GET['item_id']))
{
    header("Location: browse_items.php");
    exit();
}

$item_id = (int)$_GET['item_id'];

$query = mysqli_query($conn,"
SELECT
items.*,
users.full_name,
categories.category_name
FROM items
INNER JOIN users
ON items.user_id = users.user_id
INNER JOIN categories
ON items.category_id = categories.category_id
WHERE items.item_id='$item_id'
");

if(mysqli_num_rows($query)==0)
{
    echo "<script>
    alert('Item not found.');
    window.location='browse_items.php';
    </script>";
    exit();
}

$item = mysqli_fetch_assoc($query);
?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Item Details</title>

<link rel="stylesheet" href="css/item_details.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body>

<div class="container">

<h1>Item Details</h1>

<div class="card">

<div class="image">

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

<div class="details">

<h2><?php echo htmlspecialchars($item['item_name']); ?></h2>

<p><strong>Owner :</strong>
<?php echo htmlspecialchars($item['full_name']); ?>
</p>

<p><strong>Category :</strong>
<?php echo htmlspecialchars($item['category_name']); ?>
</p>

<p><strong>Condition :</strong>
<?php echo htmlspecialchars($item['item_condition']); ?>
</p>

<p><strong>Availability :</strong>
<?php echo htmlspecialchars($item['availability']); ?>
</p>

<p><strong>Location :</strong>
<?php echo htmlspecialchars($item['location']); ?>
</p>

<p><strong>Description :</strong><br>
<?php echo nl2br(htmlspecialchars($item['description'])); ?>
</p>

<br>

<?php
if($item['user_id']==$user_id)
{
?>

<button disabled class="btn">
Your Item
</button>

<?php
}
else
{
?>

<a href="borrow_request.php?item_id=<?php echo $item['item_id']; ?>" class="btn">
Borrow Item
</a>

<?php
}
?>

<a href="browse_items.php" class="back">
Back
</a>

</div>

</div>

</div>

</body>

</html>