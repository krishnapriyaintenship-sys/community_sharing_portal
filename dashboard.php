<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include("includes/db.php");

$user_id = $_SESSION['user_id'];

/* ===========================
   Logged-in User
=========================== */

$user_query = mysqli_query($conn,
"SELECT * FROM users WHERE user_id='$user_id'");

if(mysqli_num_rows($user_query)==0)
{
    session_destroy();
    header("Location: login.php");
    exit();
}

$user = mysqli_fetch_assoc($user_query);


/* ===========================
   Search
=========================== */

$search = "";

if(isset($_GET['search']))
{
    $search = mysqli_real_escape_string($conn, trim($_GET['search']));
}


/* ===========================
   Recently Added Items
=========================== */

$item_sql = "
SELECT
items.*,
categories.category_name

FROM items

LEFT JOIN categories
ON items.category_id = categories.category_id

WHERE
items.item_name LIKE '%$search%'
OR
items.description LIKE '%$search%'
OR
items.location LIKE '%$search%'

ORDER BY items.created_at DESC

LIMIT 6
";

$item_result = mysqli_query($conn,$item_sql);


/* ===========================
   Dashboard Statistics
=========================== */

// My Shared Items
$shared_items = mysqli_num_rows(
mysqli_query($conn,"
SELECT *
FROM items
WHERE user_id='$user_id'
")
);


// Borrowed Items
$borrowed_items = mysqli_num_rows(
mysqli_query($conn,"
SELECT *
FROM borrow_requests
WHERE borrower_id='$user_id'
AND status='Approved'
")
);


// Available Items
$available_items = mysqli_num_rows(
mysqli_query($conn,"
SELECT *
FROM items
WHERE availability='Available'
")
);


// Pending Requests
$pending_requests = mysqli_num_rows(
mysqli_query($conn,"
SELECT *
FROM borrow_requests
WHERE owner_id='$user_id'
AND status='Pending'
")
);


/* ===========================
   Notification Count
=========================== */

$today = date("Y-m-d");
$tomorrow = date("Y-m-d", strtotime("+1 day"));

$notification_query = mysqli_query($conn,"
SELECT *
FROM borrow_requests

WHERE borrower_id='$user_id'
AND status='Approved'

AND
(
expected_return_date='$today'
OR
expected_return_date='$tomorrow'
OR
expected_return_date<'$today'
)
");

$notification_count = mysqli_num_rows($notification_query);

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Dashboard | CampusShare</title>

<link rel="stylesheet" href="css/dashboard.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
rel="stylesheet">

</head>

<body>


<!-- =========================
     Sidebar
========================= -->

<div class="sidebar">

    <div class="logo">

        <img src="images/logo.png" alt="Logo">

        <h2>CampusShare</h2>

    </div>

    <ul>

        <!-- Dashboard -->

        <li class="active">

            <a href="dashboard.php">

                <i class="fa-solid fa-house"></i>

                Dashboard

            </a>

        </li>


        <!-- Add Item -->

        <li>

            <a href="add_item.php">

                <i class="fa-solid fa-plus"></i>

                Add Item

            </a>

        </li>


        <!-- Browse Items -->

        <li>

            <a href="browse_items.php">

                <i class="fa-solid fa-box"></i>

                Browse Items

            </a>

        </li>


        <!-- My Borrow Requests - ADDED -->

        <li>

            <a href="my_borrow_requests.php">

                <i class="fa-solid fa-handshake"></i>

                My Borrow Requests

            </a>

        </li>


        <!-- My Items -->

        <li>

            <a href="my_items.php">

                <i class="fa-solid fa-book"></i>

                My Items

            </a>

        </li>


        <!-- Borrow History -->

        <li>

            <a href="borrow_history.php">

                <i class="fa-solid fa-clock-rotate-left"></i>

                Borrow History

            </a>

        </li>


        <!-- Notifications -->

        <li>

            <a href="notifications.php">

                <i class="fa-solid fa-bell"></i>

                Notifications

                <?php
                if($notification_count>0)
                {
                ?>

                <span class="badge">

                    <?php echo $notification_count; ?>

                </span>

                <?php
                }
                ?>

            </a>

        </li>


        <!-- Profile -->

        <li>

            <a href="profile.php">

                <i class="fa-solid fa-user"></i>

                Profile

            </a>

        </li>

        <!-- Logout -->

        <li>
<li>

            <a href="feedback.php">

                <i class="fa-solid fa-clock-rotate-left"></i>

                Feedback

            </a>

        </li>
        <li>

            <a href="contact.php">

                <i class="fa-solid fa-clock-rotate-left"></i>

                contact admin

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



<!-- =========================
     Main Content
========================= -->

<div class="main">


<!-- =========================
     Header
========================= -->

<header>

<form method="GET" action="dashboard.php" class="search-box">

<input
type="text"
name="search"
placeholder="Search items..."
value="<?php echo htmlspecialchars($search); ?>">

<button type="submit">

<i class="fa-solid fa-search"></i>

</button>

</form>


<div class="profile">

<a href="notifications.php" class="notification-bell">

<i class="fa-solid fa-bell"></i>

<?php
if($notification_count > 0)
{
?>

<span class="notification-count">

<?php echo $notification_count; ?>

</span>

<?php
}
?>

</a>


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



<!-- =========================
     Welcome Section
========================= -->

<section class="welcome">

<div class="welcome-text">

<h1>

Welcome,
<?php echo htmlspecialchars($user['full_name']); ?> 👋

</h1>

<p>

Share books, calculators, laptops, project kits and help your college community.

</p>


<div class="welcome-buttons">

<a href="add_item.php" class="btn-primary">

<i class="fa-solid fa-plus"></i>

Add Item

</a>


<a href="browse_items.php" class="btn-secondary">

<i class="fa-solid fa-magnifying-glass"></i>

Browse Items

</a>

</div>

</div>


<div class="welcome-image">

<img
src="images/register-banner.jpg"
alt="CampusShare Banner">

</div>

</section>



<!-- =========================
     Statistics Cards
========================= -->

<section class="cards">


<div class="card">

<div class="card-icon blue">

<i class="fa-solid fa-box"></i>

</div>

<div class="card-content">

<h2><?php echo $shared_items; ?></h2>

<p>My Shared Items</p>

</div>

</div>



<div class="card">

<div class="card-icon green">

<i class="fa-solid fa-handshake"></i>

</div>

<div class="card-content">

<h2><?php echo $borrowed_items; ?></h2>

<p>Borrowed Items</p>

</div>

</div>



<div class="card">

<div class="card-icon orange">

<i class="fa-solid fa-circle-check"></i>

</div>

<div class="card-content">

<h2><?php echo $available_items; ?></h2>

<p>Available Items</p>

</div>

</div>



<div class="card">

<div class="card-icon red">

<i class="fa-solid fa-hourglass-half"></i>

</div>

<div class="card-content">

<h2><?php echo $pending_requests; ?></h2>

<p>Pending Requests</p>

</div>

</div>

</section>



<!-- =========================
     Quick Actions
========================= -->

<section class="actions">

<h2>Quick Actions</h2>


<div class="action-container">


<!-- Add Item -->

<a href="add_item.php" class="action-card">

<i class="fa-solid fa-plus"></i>

<h3>Add Item</h3>

<p>Share a new item with other students.</p>

</a>



<!-- Browse Items -->

<a href="browse_items.php" class="action-card">

<i class="fa-solid fa-magnifying-glass"></i>

<h3>Browse Items</h3>

<p>Find books, laptops, calculators and more.</p>

</a>



<!-- My Borrow Requests - ADDED -->

<a href="my_borrow_requests.php" class="action-card">

<i class="fa-solid fa-handshake"></i>

<h3>My Borrow Requests</h3>

<p>View your requests and return borrowed items.</p>

</a>



<!-- Borrow History -->

<a href="borrow_history.php" class="action-card">

<i class="fa-solid fa-clock-rotate-left"></i>

<h3>Borrow History</h3>

<p>View all your borrowing records.</p>

</a>


<!-- Notifications -->

<a href="notifications.php" class="action-card">

<i class="fa-solid fa-bell"></i>

<h3>Notifications</h3>

<?php if($notification_count > 0){ ?>

<p>

You have

<strong>

<?php echo $notification_count; ?>

</strong>

new reminder(s).

</p>

<?php } else { ?>

<p>No new notifications.</p>

<?php } ?>

</a>


</div>

</section>



<!-- =========================
     Recently Added Items
========================= -->

<section class="recent-items">

<h2>Recently Added Items</h2>


<div class="items">

<?php

if(mysqli_num_rows($item_result)>0)
{

while($item=mysqli_fetch_assoc($item_result))
{

?>

<div class="item-card">


<?php

if(!empty($item['image']) && file_exists("uploads/items/".$item['image']))
{

?>

<img
src="uploads/items/<?php echo $item['image']; ?>"
alt="Item Image">

<?php

}
else
{

?>

<img
src="images/no-image.png"
alt="No Image">

<?php

}

?>


<div class="item-details">


<h3>

<?php echo htmlspecialchars($item['item_name']); ?>

</h3>


<p>

<strong>Category :</strong>

<?php echo htmlspecialchars($item['category_name']); ?>

</p>


<p>

<strong>Location :</strong>

<?php echo htmlspecialchars($item['location']); ?>

</p>


<p>

<strong>Condition :</strong>

<?php echo htmlspecialchars($item['item_condition']); ?>

</p>


<p>

<?php

if($item['availability']=="Available")
{

?>

<span class="available">

Available

</span>

<?php

}
else
{

?>

<span class="notavailable">

Not Available

</span>

<?php

}

?>

</p>


<a
href="borrow_request.php?item_id=<?php echo $item['item_id']; ?>"
class="view-btn">

Borrow Item

</a>


</div>

</div>

<?php

}

}
else
{

?>

<h3 class="no-items">

No items found.

</h3>

<?php

}

?>

</div>

</section>



<!-- =========================
     Return Reminder Popup
========================= -->

<?php

$reminder = mysqli_query($conn,"
SELECT items.item_name, borrow_requests.expected_return_date
FROM borrow_requests
INNER JOIN items
ON borrow_requests.item_id = items.item_id
WHERE borrow_requests.borrower_id='$user_id'
AND borrow_requests.status='Approved'
AND
(
borrow_requests.expected_return_date='$today'
OR
borrow_requests.expected_return_date='$tomorrow'
)
LIMIT 1
");


if(mysqli_num_rows($reminder)>0)
{

$row = mysqli_fetch_assoc($reminder);

?>

<div class="reminder-popup">

<div class="popup-content">

<h3>

🔔 Return Reminder

</h3>


<p>

Please return

<strong>

<?php echo htmlspecialchars($row['item_name']); ?>

</strong>

before

<b>

<?php echo date("d-m-Y",strtotime($row['expected_return_date'])); ?>

</b>

</p>


<button onclick="closeReminder()">

OK

</button>

</div>

</div>

<?php

}

?>


<script>

function closeReminder()
{
    document.querySelector(".reminder-popup").style.display="none";
}

</script>


</div>

</body>

</html>