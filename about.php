<?php
session_start();

if(!isset($_SESSION['user_id']))
{
    header("Location:login.php");
    exit();
}

include("includes/db.php");

$user_id = $_SESSION['user_id'];

$user_query = mysqli_query($conn,"SELECT * FROM users WHERE user_id='$user_id'");
$user = mysqli_fetch_assoc($user_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>About | CampusShare</title>

<link rel="stylesheet" href="css/about.css">

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

<li><a href="dashboard.php"><i class="fa fa-house"></i> Dashboard</a></li>

<li><a href="browse_items.php"><i class="fa fa-box"></i> Browse Items</a></li>

<li><a href="profile.php"><i class="fa fa-user"></i> My Profile</a></li>

<li class="active"><a href="about.php"><i class="fa fa-circle-info"></i> About</a></li>

<li><a href="logout.php"><i class="fa fa-right-from-bracket"></i> Logout</a></li>

</ul>

</div>

<div class="main">

<header>

<h1>About CampusShare</h1>

<div class="profile">

<img src="images/default.png">

<span><?php echo htmlspecialchars($user['full_name']); ?></span>

</div>

</header>

<div class="about-card">

<h2>Community Item Sharing Portal</h2>

<p>
CampusShare is a web-based platform that allows students to share and borrow useful items within their campus community.
Instead of purchasing new items, students can borrow books, calculators, lab equipment, sports items, electronics, and many other resources from fellow students.
</p>

<h3>Objectives</h3>

<ul>

<li>Promote resource sharing among students.</li>

<li>Reduce unnecessary expenses.</li>

<li>Encourage sustainable reuse of items.</li>

<li>Provide a secure borrowing system.</li>

</ul>

<h3>Main Features</h3>

<ul>

<li>User Registration & Login</li>

<li>Add Items</li>

<li>Browse Available Items</li>

<li>Search Items</li>

<li>Borrow Request Management</li>

<li>Notifications</li>

<li>Profile Management</li>

<li>Password Change</li>

</ul>

<h3>Technologies Used</h3>

<ul>

<li>HTML5</li>

<li>CSS3</li>

<li>PHP</li>

<li>MySQL</li>

<li>JavaScript</li>

<li>XAMPP</li>

</ul>

</div>

</div>

</body>

</html>