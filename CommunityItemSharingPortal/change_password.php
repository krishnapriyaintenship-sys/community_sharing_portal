<?php
session_start();

if(!isset($_SESSION['user_id']))
{
    header("Location:login.php");
    exit();
}

include("includes/db.php");

$user_id = $_SESSION['user_id'];

$user_query = mysqli_query($conn,
"SELECT * FROM users WHERE user_id='$user_id'");

$user = mysqli_fetch_assoc($user_query);
?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Change Password | CampusShare</title>

<link rel="stylesheet"
href="css/change_password.css">

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

<a href="profile.php">

<i class="fa fa-user"></i>

My Profile

</a>

</li>

<li class="active">

<a href="change_password.php">

<i class="fa fa-key"></i>

Change Password

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

<h1>Change Password</h1>

</header>

<form action="change_password_process.php" method="POST">

<div class="password-box">
    <div class="input-group">

<label>Current Password</label>

<input
type="password"
name="current_password"
placeholder="Enter Current Password"
required>

</div>

<div class="input-group">

<label>New Password</label>

<input
type="password"
name="new_password"
placeholder="Enter New Password"
required>

</div>

<div class="input-group">

<label>Confirm New Password</label>

<input
type="password"
name="confirm_password"
placeholder="Confirm New Password"
required>

</div>

<div class="buttons">

<button type="submit" class="save-btn">

<i class="fa fa-floppy-disk"></i>

Update Password

</button>

<a href="profile.php" class="cancel-btn">

<i class="fa fa-arrow-left"></i>

Cancel

</a>

</div>

</div>

</form>

</div>

</body>

</html>
