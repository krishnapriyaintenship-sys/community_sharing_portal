<?php
session_start();

/* =========================================================
   ADMIN LOGIN CHECK
   ========================================================= */

if (
    !isset($_SESSION['admin_id']) ||
    !isset($_SESSION['admin_role']) ||
    $_SESSION['admin_role'] !== 'admin'
) {
    header("Location: ../login.php");
    exit();
}


/* =========================================================
   DATABASE CONNECTION
   ========================================================= */

require_once "../includes/db.php";


/* =========================================================
   DASHBOARD COUNTS
   ========================================================= */

// Total Users
$user_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM users"
);

$user_data = mysqli_fetch_assoc($user_query);
$total_users = $user_data['total'];


// Total Items
$item_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM items"
);

$item_data = mysqli_fetch_assoc($item_query);
$total_items = $item_data['total'];


// Available Items
$available_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM items
     WHERE availability = 'Available'"
);

$available_data = mysqli_fetch_assoc($available_query);
$available_items = $available_data['total'];


// Borrowed Items
$borrowed_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM items
     WHERE availability = 'Borrowed'"
);

$borrowed_data = mysqli_fetch_assoc($borrowed_query);
$borrowed_items = $borrowed_data['total'];


// Overdue Items
$overdue_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM borrow_requests
     WHERE expected_return_date < CURDATE()
     AND actual_return_date IS NULL
     AND status IN (
         'Approved',
         'Item Received',
         'Return Requested'
     )"
);

$overdue_data = mysqli_fetch_assoc($overdue_query);
$overdue_items = $overdue_data['total'];


// Contact Messages
$contact_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM contact_messages"
);

$contact_data = mysqli_fetch_assoc($contact_query);
$total_messages = $contact_data['total'];


// Feedback
$feedback_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM feedback"
);

$feedback_data = mysqli_fetch_assoc($feedback_query);
$total_feedback = $feedback_data['total'];


/* =========================================================
   ADMIN EMAIL
   ========================================================= */

$admin_email = $_SESSION['admin_email'] ?? 'Admin';

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Admin Dashboard | CampusShare</title>


    <!-- Admin CSS -->
    <link
        rel="stylesheet"
        href="admin.css"
    >


    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >


    <!-- Google Font -->
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet"
    >

</head>


<body>


<!-- =========================================================
     SIDEBAR
     ========================================================= -->

<div class="sidebar">


    <!-- Logo -->

    <div class="sidebar-logo">

        <i class="fa-solid fa-users"></i>

        <h2>CampusShare</h2>

    </div>


    <!-- Admin label -->

    <div class="admin-label">

        <i class="fa-solid fa-user-shield"></i>

        <span>Admin Panel</span>

    </div>


    <!-- Navigation -->

    <ul class="sidebar-menu">


        <li class="active">

            <a href="dashboard.php">

                <i class="fa-solid fa-house"></i>

                <span>Dashboard</span>

            </a>

        </li>


        <li>

            <a href="users.php">

                <i class="fa-solid fa-users"></i>

                <span>Manage Users</span>

            </a>

        </li>


        <li>

            <a href="items.php">

                <i class="fa-solid fa-box"></i>

                <span>Manage Items</span>

            </a>

        </li>


        <li>

            <a href="overdue_items.php">

                <i class="fa-solid fa-triangle-exclamation"></i>

                <span>Overdue Items</span>

                <?php if ($overdue_items > 0): ?>

                    <span class="badge">
                        <?php echo $overdue_items; ?>
                    </span>

                <?php endif; ?>

            </a>

        </li>


        <li>

            <a href="user_messages.php">

                <i class="fa-solid fa-message"></i>

                <span>User Messages</span>

            </a>

        </li>
<li>

            <a href="contact_user.php">

                <i class="fa-solid fa-message"></i>

                <span>contact_user</span>

            </a>

        </li>


        <li>

            <a href="feedback.php">

                <i class="fa-solid fa-star"></i>

                <span>Feedback</span>

            </a>

        </li>


        <li class="logout-menu">

            <a href="logout.php">

                <i class="fa-solid fa-right-from-bracket"></i>

                <span>Logout</span>

            </a>

        </li>


    </ul>

</div>



<!-- =========================================================
     MAIN CONTENT
     ========================================================= -->

<div class="main-content">


    <!-- Header -->

    <div class="top-header">


        <div>

            <h1>Admin Dashboard</h1>

            <p>
                Welcome to the CampusShare management panel.
            </p>

        </div>


        <div class="admin-profile">

            <i class="fa-solid fa-circle-user"></i>

            <div>

                <strong>Administrator</strong>

                <small>
                    <?php echo htmlspecialchars($admin_email); ?>
                </small>

            </div>

        </div>


    </div>



    <!-- =====================================================
         STATISTICS
         ===================================================== -->

    <div class="stats-grid">


        <!-- Total Users -->

        <div class="stat-card users-card">

            <div class="stat-icon">

                <i class="fa-solid fa-users"></i>

            </div>

            <div>

                <h3>
                    <?php echo $total_users; ?>
                </h3>

                <p>Total Users</p>

            </div>

        </div>



        <!-- Total Items -->

        <div class="stat-card items-card">

            <div class="stat-icon">

                <i class="fa-solid fa-box"></i>

            </div>

            <div>

                <h3>
                    <?php echo $total_items; ?>
                </h3>

                <p>Total Items</p>

            </div>

        </div>



        <!-- Available Items -->

        <div class="stat-card available-card">

            <div class="stat-icon">

                <i class="fa-solid fa-circle-check"></i>

            </div>

            <div>

                <h3>
                    <?php echo $available_items; ?>
                </h3>

                <p>Available Items</p>

            </div>

        </div>



        <!-- Borrowed Items -->

        <div class="stat-card borrowed-card">

            <div class="stat-icon">

                <i class="fa-solid fa-hand-holding"></i>

            </div>

            <div>

                <h3>
                    <?php echo $borrowed_items; ?>
                </h3>

                <p>Borrowed Items</p>

            </div>

        </div>



        <!-- Overdue Items -->

        <div class="stat-card overdue-card">

            <div class="stat-icon">

                <i class="fa-solid fa-triangle-exclamation"></i>

            </div>

            <div>

                <h3>
                    <?php echo $overdue_items; ?>
                </h3>

                <p>Overdue Items</p>

            </div>

        </div>



        <!-- Messages -->

        <div class="stat-card message-card">

            <div class="stat-icon">

                <i class="fa-solid fa-envelope"></i>

            </div>

            <div>

                <h3>
                    <?php echo $total_messages; ?>
                </h3>

                <p>User Messages</p>

            </div>

        </div>



        <!-- Feedback -->

        <div class="stat-card feedback-card">

            <div class="stat-icon">

                <i class="fa-solid fa-star"></i>

            </div>

            <div>

                <h3>
                    <?php echo $total_feedback; ?>
                </h3>

                <p>Feedback</p>

            </div>

        </div>


    </div>



    <!-- =====================================================
         ADMIN INFORMATION
         ===================================================== -->

    <div class="dashboard-section">


        <div class="section-header">

            <h2>Admin Controls</h2>

            <p>
                Manage and monitor the CampusShare website.
            </p>

        </div>


        <div class="quick-actions">


            <!-- Manage Users -->

            <a
                href="users.php"
                class="quick-card"
            >

                <i class="fa-solid fa-users"></i>

                <h3>Manage Users</h3>

                <p>
                    View and manage registered users.
                </p>

            </a>



            <!-- Manage Items -->

            <a
                href="items.php"
                class="quick-card"
            >

                <i class="fa-solid fa-box"></i>

                <h3>Manage Items</h3>

                <p>
                    View and manage shared items.
                </p>

            </a>



            <!-- Overdue -->

            <a
                href="overdue_items.php"
                class="quick-card overdue-action"
            >

                <i class="fa-solid fa-clock"></i>

                <h3>Overdue Items</h3>

                <p>
                    Check items that have not been returned on time.
                </p>

            </a>



            <!-- Messages -->

            <a
                href="contact_messages.php"
                class="quick-card"
            >

                <i class="fa-solid fa-message"></i>

                <h3>Contact Messages</h3>

                <p>
                    View messages received from users.
                </p>

            </a>



            <!-- Feedback -->

            <a
                href="feedbackq.php"
                class="quick-card"
            >

                <i class="fa-solid fa-star"></i>

                <h3>Feedback</h3>

                <p>
                    View feedback submitted by users.
                </p>

            </a>


        </div>

    </div>


</div>


</body>

</html>