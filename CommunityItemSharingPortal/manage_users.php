<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

include("includes/db.php");

$search = "";

if(isset($_GET['search']))
{
    $search = mysqli_real_escape_string($conn,$_GET['search']);

    $query = mysqli_query($conn,"
    SELECT *
    FROM users
    WHERE full_name LIKE '%$search%'
    OR email LIKE '%$search%'
    ORDER BY user_id DESC
    ");
}
else
{
    $query = mysqli_query($conn,"
    SELECT *
    FROM users
    ORDER BY user_id DESC
    ");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Manage Users</title>

<link rel="stylesheet"
href="css/manage_users.css">

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

<li class="active">

<a href="manage_users.php">

<i class="fa fa-users"></i>

Manage Users

</a>

</li>

<li>

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

<h1>Manage Users</h1>

</header>

<form method="GET" class="search-box">

<input
type="text"
name="search"
placeholder="Search by Name or Email"
value="<?php echo htmlspecialchars($search); ?>">

<button type="submit">

<i class="fa fa-search"></i>

Search

</button>

</form>

<table>

<tr>

<th>ID</th>

<th>Name</th>

<th>Email</th>

<th>Phone</th>

<th>Department</th>

<th>Year</th>

<th>Action</th>

</tr>
<tr>

<th>ID</th>

<th>Name</th>

<th>Email</th>

<th>Phone</th>

<th>Department</th>

<th>Year</th>

<th>Action</th>

</tr>
<?php

if(mysqli_num_rows($query) > 0)
{

while($user = mysqli_fetch_assoc($query))
{

?>

<tr>

<td><?php echo $user['user_id']; ?></td>

<td><?php echo htmlspecialchars($user['full_name']); ?></td>

<td><?php echo htmlspecialchars($user['email']); ?></td>

<td><?php echo htmlspecialchars($user['phone']); ?></td>

<td><?php echo htmlspecialchars($user['department']); ?></td>

<td><?php echo htmlspecialchars($user['year_of_study']); ?></td>

<td>

<a href="delete_user.php?user_id=<?php echo $user['user_id']; ?>"
class="delete-btn"
onclick="return confirm('Are you sure you want to delete this user?');">

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

No Users Found

</td>

</tr>

<?php

}

?>

</table>

</div>

</body>

</html>