<?php
session_start();
include("includes/db.php");

/* ===========================
   Check Login
=========================== */

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ===========================
   Get Logged-in User
=========================== */

$user_query = mysqli_query($conn,"
SELECT *
FROM users
WHERE user_id='$user_id'
");

if(mysqli_num_rows($user_query)==0)
{
    die("User not found.");
}

$user = mysqli_fetch_assoc($user_query);

/* ===========================
   Today's Dates
=========================== */

$today = date("Y-m-d");
$tomorrow = date("Y-m-d",strtotime("+1 day"));

/* ===========================
   Auto Create Reminder Notifications
=========================== */

$borrow_query = mysqli_query($conn,"
SELECT
br.request_id,
br.expected_return_date,
br.status,
i.item_name
FROM borrow_requests br
INNER JOIN items i
ON br.item_id=i.item_id
WHERE br.borrower_id='$user_id'
AND br.status='Approved'
");

while($borrow=mysqli_fetch_assoc($borrow_query))
{

$request_id=$borrow['request_id'];

$item_name=$borrow['item_name'];

$return_date=$borrow['expected_return_date'];

$title="";
$message="";
$type="";

if($return_date==$tomorrow)
{

$title="Return Reminder";

$message="Your borrowed item '".$item_name."' should be returned tomorrow.";

$type="Reminder";

}
elseif($return_date==$today)
{

$title="Return Today";

$message="Please return '".$item_name."' today.";

$type="Reminder";

}
elseif($return_date<$today)
{

$title="Overdue Item";

$message="The return date for '".$item_name."' has passed. Please return it immediately.";

$type="Overdue";

}

if($type!="")
{

$check=mysqli_query($conn,"
SELECT notification_id
FROM notifications
WHERE request_id='$request_id'
AND type='$type'
");

if(mysqli_num_rows($check)==0)
{

$stmt=mysqli_prepare($conn,"
INSERT INTO notifications
(
user_id,
request_id,
title,
message,
type,
status
)
VALUES
(
?,
?,
?,
?,
?,
'Unread'
)
");

mysqli_stmt_bind_param(
$stmt,
"iisss",
$user_id,
$request_id,
$title,
$message,
$type
);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

}

}

}

/* ===========================
   Mark Notification Read
=========================== */

if(isset($_GET['read']))
{

$notification_id=intval($_GET['read']);

mysqli_query($conn,"
UPDATE notifications
SET status='Read'
WHERE notification_id='$notification_id'
AND user_id='$user_id'
");

header("Location: notifications.php");

exit();

}

/* ===========================
   Mark All Read
=========================== */

if(isset($_GET['readall']))
{

mysqli_query($conn,"
UPDATE notifications
SET status='Read'
WHERE user_id='$user_id'
");

header("Location: notifications.php");

exit();

}

/* ===========================
   Load Notifications
=========================== */

$notification_query=mysqli_query($conn,"
SELECT *
FROM notifications
WHERE user_id='$user_id'
ORDER BY created_at DESC
");

/* ===========================
   Unread Count
=========================== */

$count_query=mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM notifications
WHERE user_id='$user_id'
AND status='Unread'
");

$count=mysqli_fetch_assoc($count_query);

$unread=$count['total'];

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Notifications | CampusShare</title>

<link rel="stylesheet" href="css/notifications.css">

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

<i class="fa-solid fa-house"></i>

Dashboard

</a>

</li>

<li>

<a href="browse_items.php">

<i class="fa-solid fa-box"></i>

Browse Items

</a>

</li>

<li>

<a href="my_items.php">

<i class="fa-solid fa-book"></i>

My Items

</a>

</li>

<li>

<a href="manage_requests.php">

<i class="fa-solid fa-handshake"></i>

Manage Requests

</a>

</li>

<li class="active">

<a href="notifications.php">

<i class="fa-solid fa-bell"></i>

Notifications

<?php
if($unread > 0)
{
?>

<span class="badge">

<?php echo $unread; ?>

</span>

<?php
}
?>

</a>

</li>

<li>

<a href="profile.php">

<i class="fa-solid fa-user"></i>

Profile

</a>

</li>

<li>

<a href="logout.php">

<i class="fa-solid fa-right-from-bracket"></i>

Logout

</a>

</li>

</ul>

</div>

<!-- Main Content -->

<div class="main">

<header>

<h1>

Notifications

</h1>

<div class="profile">

<?php

if(!empty($user['profile_image']) &&
file_exists("uploads/".$user['profile_image']))
{

?>

<img
src="uploads/<?php echo $user['profile_image']; ?>"
alt="Profile">

<?php

}
else
{

?>

<img
src="images/default.jpg"
alt="Profile">

<?php

}

?>

<span>

<?php echo htmlspecialchars($user['full_name']); ?>

</span>

</div>

</header>

<div class="top-actions">

<a href="notifications.php?readall=1"
class="read-all">

<i class="fa-solid fa-check-double"></i>

Mark All as Read

</a>

</div>

<div class="notification-container">
    <?php

if(mysqli_num_rows($notification_query) > 0)
{

while($notification = mysqli_fetch_assoc($notification_query))
{

?>

<div class="notification-card <?php echo strtolower($notification['status']); ?>">

<div class="icon">

<?php

switch($notification['type'])
{

case "Request":

echo '<i class="fa-solid fa-handshake"></i>';

break;

case "Approved":

echo '<i class="fa-solid fa-circle-check"></i>';

break;

case "Rejected":

echo '<i class="fa-solid fa-circle-xmark"></i>';

break;

case "Reminder":

echo '<i class="fa-solid fa-bell"></i>';

break;

case "Returned":

echo '<i class="fa-solid fa-arrow-rotate-left"></i>';

break;

case "Overdue":

echo '<i class="fa-solid fa-triangle-exclamation"></i>';

break;

default:

echo '<i class="fa-solid fa-circle-info"></i>';

}

?>

</div>

<div class="content">

<h3>

<?php echo htmlspecialchars($notification['title']); ?>

</h3>

<p>

<?php echo htmlspecialchars($notification['message']); ?>

</p>

<small>

<i class="fa-regular fa-clock"></i>

<?php echo date("d M Y h:i A",strtotime($notification['created_at'])); ?>

</small>

</div>

<div class="status-area">

<?php

if($notification['status']=="Unread")
{

?>

<span class="unread">

Unread

</span>

<br><br>

<a
href="notifications.php?read=<?php echo $notification['notification_id']; ?>"
class="read-btn">

Mark as Read

</a>

<?php

}
else
{

?>

<span class="read">

Read

</span>

<?php

}

?>

</div>

</div>

<?php

}

}
else
{

?>

<div class="empty">

<i class="fa-regular fa-bell-slash"></i>

<h2>No Notifications</h2>

<p>

You don't have any notifications yet.

</p>

</div>

<?php

}

?>
</div>

</div>

</body>

</html>
