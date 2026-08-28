<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location:login.php");
    exit();
}

include("includes/db.php");

$user_id = $_SESSION['user_id'];

$user_query = mysqli_query($conn, "
SELECT *
FROM users
WHERE user_id='$user_id'
");

$user = mysqli_fetch_assoc($user_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>My Profile | CampusShare</title>

<link rel="stylesheet" href="css/profile.css">

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

<a href="manage_requests.php">

<i class="fa fa-handshake"></i>

Manage Requests

</a>

</li>

<li>

<a href="notifications.php">

<i class="fa fa-bell"></i>

Notifications

</a>

</li>

<li class="active">

<a href="profile.php">

<i class="fa fa-user"></i>

My Profile

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

<h1>My Profile</h1>

</header>

<div class="profile-card">

<div class="profile-image">

<?php
if(!empty($user['profile_image']) && file_exists("uploads/profile/".$user['profile_image']))
{
?>

<img src="uploads/profile/<?php echo $user['profile_image']; ?>" alt="Profile Image">

<?php
}
else
{
?>

<img src="images/default.png" alt="Default Image">

<?php
}
?>

</div>

<div class="profile-details">

<h2><?php echo htmlspecialchars($user['full_name']); ?></h2>

<table>

<tr>

<th>Email</th>

<td><?php echo htmlspecialchars($user['email']); ?></td>

</tr>

<tr>

<th>Phone</th>

<td><?php echo htmlspecialchars($user['phone']); ?></td>

</tr>

<tr>

<th>Department</th>

<td><?php echo htmlspecialchars($user['department']); ?></td>

</tr>

<tr>

<th>Year of Study</th>

<td><?php echo htmlspecialchars($user['year_of_study']); ?></td>

</tr>

<tr>

<th>Member Since</th>

<td><?php echo date("d M Y", strtotime($user['created_at'])); ?></td>

</tr>

</table>

<div class="buttons">

<a href="edit_profile.php" class="edit-btn">

<i class="fa fa-user-pen"></i>

Edit Profile

</a>

<a href="change_password.php" class="password-btn">

<i class="fa fa-key"></i>

Change Password

</a>

</div>

</div>

</div>

</div>

</body>

</html>