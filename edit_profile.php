<?php
session_start();

if(!isset($_SESSION['user_id']))
{
    header("Location:login.php");
    exit();
}

include("includes/db.php");

$user_id=$_SESSION['user_id'];

$user_query=mysqli_query($conn,"
SELECT *
FROM users
WHERE user_id='$user_id'
");

$user=mysqli_fetch_assoc($user_query);
?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Edit Profile | CampusShare</title>

<link rel="stylesheet"
href="css/edit_profile.css">

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

<a href="edit_profile.php">

<i class="fa fa-user-pen"></i>

Edit Profile

</a>

</li>

<li>

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

<div class="main">

<header>

<h1>Edit Profile</h1>

</header>

<form action="update_profile.php"
method="POST"
enctype="multipart/form-data">

<div class="form-container">
    <div class="image-section">

<?php

if(!empty($user['profile_image']) && file_exists("uploads/profile/".$user['profile_image']))
{
?>

<img src="uploads/profile/<?php echo $user['profile_image']; ?>" class="profile-img">

<?php
}
else
{
?>

<img src="images/default.png" class="profile-img">

<?php
}
?>

<label>Change Profile Picture</label>

<input type="file" name="profile_image" accept="image/*">

</div>

<div class="form-section">

<div class="input-group">

<label>Full Name</label>

<input
type="text"
name="full_name"
value="<?php echo htmlspecialchars($user['full_name']); ?>"
required>

</div>

<div class="input-group">

<label>Email</label>

<input
type="email"
value="<?php echo htmlspecialchars($user['email']); ?>"
readonly>

</div>

<div class="input-group">

<label>Phone Number</label>

<input
type="text"
name="phone"
value="<?php echo htmlspecialchars($user['phone']); ?>">

</div>

<div class="input-group">

<label>Department</label>

<input
type="text"
name="department"
value="<?php echo htmlspecialchars($user['department']); ?>">

</div>

<div class="input-group">

<label>Year of Study</label>

<select name="year_of_study">

<option value="">Select Year</option>

<option value="1st Year"
<?php if($user['year_of_study']=="1st Year") echo "selected"; ?>>
1st Year
</option>

<option value="2nd Year"
<?php if($user['year_of_study']=="2nd Year") echo "selected"; ?>>
2nd Year
</option>

<option value="3rd Year"
<?php if($user['year_of_study']=="3rd Year") echo "selected"; ?>>
3rd Year
</option>

<option value="4th Year"
<?php if($user['year_of_study']=="4th Year") echo "selected"; ?>>
4th Year
</option>

</select>

</div>

<div class="buttons">

<button type="submit" class="save-btn">

<i class="fa fa-floppy-disk"></i>

Save Changes

</button>

<a href="profile.php" class="cancel-btn">

<i class="fa fa-arrow-left"></i>

Cancel

</a>

</div>

</div>

</div>

</form>

</div>

</body>

</html>
