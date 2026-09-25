<?php
session_start();

/* =====================================================
   LOGIN CHECK
===================================================== */

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


/* =====================================================
   DATABASE
===================================================== */

require_once "includes/db.php";


$user_id = (int) $_SESSION['user_id'];


/* =====================================================
   GET USER DETAILS
===================================================== */

$user_stmt = $conn->prepare("
    SELECT
        user_id,
        full_name,
        email,
        profile_image
    FROM users
    WHERE user_id = ?
    LIMIT 1
");

$user_stmt->bind_param(
    "i",
    $user_id
);

$user_stmt->execute();

$user_result = $user_stmt->get_result();

$user = $user_result->fetch_assoc();

$user_stmt->close();


/* =====================================================
   USER NOT FOUND
===================================================== */

if (!$user) {

    session_destroy();

    header("Location: login.php");
    exit();
}


/* =====================================================
   SEARCH
===================================================== */

$search = trim(
    $_GET['search'] ?? ''
);


/* =====================================================
   CATEGORY
===================================================== */

$category_id = (int)(
    $_GET['category_id'] ?? 0
);


/* =====================================================
   SUCCESS / ERROR
===================================================== */

$success = $_GET['success'] ?? '';

$error = $_GET['error'] ?? '';


/* =====================================================
   CATEGORY LIST
===================================================== */

$categories = [];

$category_query = mysqli_query(
    $conn,
    "
    SELECT
        category_id,
        category_name
    FROM categories
    ORDER BY category_name ASC
    "
);

if ($category_query) {

    while (
        $category = mysqli_fetch_assoc($category_query)
    ) {

        $categories[] = $category;
    }
}


/* =====================================================
   BUILD ITEMS QUERY
===================================================== */

$sql = "
    SELECT
        i.item_id,
        i.user_id,
        i.category_id,
        i.item_name,
        i.description,
        i.item_condition,
        i.availability,
        i.location,
        i.image,
        i.created_at,

        c.category_name,

        u.full_name AS owner_name

    FROM items i

    LEFT JOIN categories c
        ON i.category_id = c.category_id

    INNER JOIN users u
        ON i.user_id = u.user_id

    WHERE 1 = 1
";


$params = [];
$types = "";


/* =====================================================
   SEARCH FILTER
===================================================== */

if ($search !== '') {

    $sql .= "
        AND (
            i.item_name LIKE ?
            OR i.description LIKE ?
            OR i.location LIKE ?
            OR i.item_condition LIKE ?
            OR c.category_name LIKE ?
            OR u.full_name LIKE ?
        )
    ";

    $search_value =
        "%" . $search . "%";

    $params[] = $search_value;
    $params[] = $search_value;
    $params[] = $search_value;
    $params[] = $search_value;
    $params[] = $search_value;
    $params[] = $search_value;

    $types .= "ssssss";
}


/* =====================================================
   CATEGORY FILTER
===================================================== */

if ($category_id > 0) {

    $sql .= "
        AND i.category_id = ?
    ";

    $params[] = $category_id;

    $types .= "i";
}


/* =====================================================
   ORDER
===================================================== */

$sql .= "
    ORDER BY i.created_at DESC
";


/* =====================================================
   PREPARE ITEM QUERY
===================================================== */

$stmt = $conn->prepare($sql);


if (!$stmt) {

    die(
        "Unable to load items. Database error."
    );
}


/* =====================================================
   BIND PARAMETERS
===================================================== */

if (!empty($params)) {

    $stmt->bind_param(
        $types,
        ...$params
    );
}


/* =====================================================
   EXECUTE
===================================================== */

$stmt->execute();

$item_result = $stmt->get_result();


/* =====================================================
   STATISTICS
===================================================== */


/* -----------------------------------------------------
   MY SHARED ITEMS
----------------------------------------------------- */

$count_stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM items
    WHERE user_id = ?
");

$count_stmt->bind_param(
    "i",
    $user_id
);

$count_stmt->execute();

$count_result =
    $count_stmt->get_result();

$row =
    $count_result->fetch_assoc();

$shared_items =
    (int)$row['total'];

$count_stmt->close();


/* -----------------------------------------------------
   BORROWED ITEMS
----------------------------------------------------- */

$count_stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM borrow_requests
    WHERE borrower_id = ?
    AND status IN (
        'Approved',
        'Item Received',
        'Return Requested'
    )
");

$count_stmt->bind_param(
    "i",
    $user_id
);

$count_stmt->execute();

$count_result =
    $count_stmt->get_result();

$row =
    $count_result->fetch_assoc();

$borrowed_items =
    (int)$row['total'];

$count_stmt->close();


/* -----------------------------------------------------
   AVAILABLE ITEMS
----------------------------------------------------- */

$count_result = mysqli_query(
    $conn,
    "
    SELECT COUNT(*) AS total
    FROM items
    WHERE availability = 'Available'
    "
);

$row =
    mysqli_fetch_assoc($count_result);

$available_items =
    (int)$row['total'];


/* -----------------------------------------------------
   PENDING REQUESTS RECEIVED
----------------------------------------------------- */

$count_stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM borrow_requests
    WHERE owner_id = ?
    AND status = 'Pending'
");

$count_stmt->bind_param(
    "i",
    $user_id
);

$count_stmt->execute();

$count_result =
    $count_stmt->get_result();

$row =
    $count_result->fetch_assoc();

$pending_requests =
    (int)$row['total'];

$count_stmt->close();


/* =====================================================
   NOTIFICATION COUNT
===================================================== */

$today =
    date("Y-m-d");

$tomorrow =
    date(
        "Y-m-d",
        strtotime("+1 day")
    );


$notification_stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM borrow_requests
    WHERE borrower_id = ?

    AND status IN (
        'Approved',
        'Item Received',
        'Return Requested'
    )

    AND (
        expected_return_date = ?
        OR expected_return_date = ?
        OR expected_return_date < ?
    )
");

$notification_stmt->bind_param(
    "isss",
    $user_id,
    $today,
    $tomorrow,
    $today
);

$notification_stmt->execute();

$notification_result =
    $notification_stmt->get_result();

$row =
    $notification_result->fetch_assoc();

$notification_count =
    (int)$row['total'];

$notification_stmt->close();

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Browse Items | CampusShare
    </title>


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


    <style>

        /* =====================================================
           RESET
        ===================================================== */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        /* =====================================================
           BODY
        ===================================================== */

        body {

            font-family: 'Poppins', sans-serif;

            background: #f5f7fb;

            color: #1f2937;
        }


        /* =====================================================
           SIDEBAR
        ===================================================== */

        .sidebar {

            position: fixed;

            left: 0;
            top: 0;

            width: 240px;

            height: 100vh;

            background: #111827;

            color: white;

            padding: 25px 15px;

            overflow-y: auto;
        }


        .logo {

            text-align: center;

            margin-bottom: 30px;
        }


        .logo img {

            width: 55px;
            height: 55px;

            object-fit: contain;

            margin-bottom: 8px;
        }


        .logo h2 {

            font-size: 20px;

            margin-bottom: 3px;
        }


        .logo p {

            font-size: 11px;

            color: #9ca3af;
        }


        .sidebar ul {

            list-style: none;
        }


        .sidebar li {

            margin-bottom: 7px;
        }


        .sidebar a {

            display: flex;

            align-items: center;

            gap: 12px;

            padding: 12px 15px;

            border-radius: 8px;

            color: #d1d5db;

            text-decoration: none;

            font-size: 14px;

            transition: 0.2s;
        }


        .sidebar a:hover {

            background: #1f2937;

            color: white;
        }


        .sidebar li.active a {

            background: #2563eb;

            color: white;
        }


        .sidebar i {

            width: 20px;

            text-align: center;
        }


        /* =====================================================
           MAIN
        ===================================================== */

        .main {

            margin-left: 240px;

            min-height: 100vh;

            padding: 25px 35px;
        }


        /* =====================================================
           HEADER
        ===================================================== */

        header {

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 20px;

            margin-bottom: 30px;
        }


        /* =====================================================
           SEARCH
        ===================================================== */

        .search-box {

            display: flex;

            width: 100%;

            max-width: 550px;
        }


        .search-box input {

            flex: 1;

            padding: 12px 15px;

            border: 1px solid #d1d5db;

            border-right: none;

            border-radius: 8px 0 0 8px;

            font-size: 14px;

            outline: none;
        }


        .search-box input:focus {

            border-color: #2563eb;
        }


        .search-box button {

            width: 50px;

            border: none;

            background: #2563eb;

            color: white;

            border-radius: 0 8px 8px 0;

            cursor: pointer;
        }


        .search-box button:hover {

            background: #1d4ed8;
        }


        /* =====================================================
           PROFILE
        ===================================================== */

        .profile {

            display: flex;

            align-items: center;

            gap: 10px;

            font-weight: 600;
        }


        .profile img {

            width: 42px;
            height: 42px;

            border-radius: 50%;

            object-fit: cover;
        }


        .notification-bell {

            position: relative;

            color: #374151;

            font-size: 20px;

            margin-right: 10px;

            text-decoration: none;
        }


        .notification-bell:hover {

            color: #2563eb;
        }


        .notification-count {

            position: absolute;

            top: -8px;
            right: -9px;

            background: #dc2626;

            color: white;

            width: 18px;
            height: 18px;

            border-radius: 50%;

            font-size: 10px;

            display: flex;

            align-items: center;

            justify-content: center;
        }


        /* =====================================================
           PAGE TITLE
        ===================================================== */

        .page-title {

            margin-bottom: 25px;
        }


        .page-title h1 {

            font-size: 30px;

            margin-bottom: 7px;
        }


        .page-title p {

            color: #6b7280;
        }


        /* =====================================================
           ALERTS
        ===================================================== */

        .alert {

            padding: 13px 16px;

            border-radius: 8px;

            margin-bottom: 20px;

            font-size: 14px;
        }


        .alert.success {

            background: #dcfce7;

            color: #166534;

            border: 1px solid #86efac;
        }


        .alert.error {

            background: #fee2e2;

            color: #991b1b;

            border: 1px solid #fca5a5;
        }


        /* =====================================================
           STATISTICS
        ===================================================== */

        .cards {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 18px;

            margin-bottom: 30px;
        }


        .card {

            background: white;

            border-radius: 12px;

            padding: 20px;

            display: flex;

            align-items: center;

            gap: 15px;

            box-shadow:
                0 3px 12px rgba(0,0,0,0.06);
        }


        .card-icon {

            width: 48px;
            height: 48px;

            border-radius: 10px;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 20px;
        }


        .blue {

            background: #dbeafe;

            color: #2563eb;
        }


        .green {

            background: #dcfce7;

            color: #16a34a;
        }


        .orange {

            background: #ffedd5;

            color: #ea580c;
        }


        .red {

            background: #fee2e2;

            color: #dc2626;
        }


        .card h2 {

            font-size: 24px;
        }


        .card p {

            color: #6b7280;

            font-size: 13px;
        }


        /* =====================================================
           CATEGORY SECTION
        ===================================================== */

        .category-section {

            background: white;

            padding: 20px;

            border-radius: 12px;

            margin-bottom: 25px;

            box-shadow:
                0 3px 12px rgba(0,0,0,0.05);
        }


        .category-section h2 {

            font-size: 20px;

            margin-bottom: 15px;
        }


        .category-buttons {

            display: flex;

            flex-wrap: wrap;

            gap: 10px;
        }


        .category-btn {

            display: inline-block;

            padding: 9px 16px;

            background: #f3f4f6;

            color: #374151;

            text-decoration: none;

            border-radius: 20px;

            font-size: 13px;

            transition: 0.2s;
        }


        .category-btn:hover {

            background: #dbeafe;

            color: #2563eb;
        }


        .category-btn.active {

            background: #2563eb;

            color: white;
        }


        /* =====================================================
           ITEMS HEADER
        ===================================================== */

        .items-header {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 18px;
        }


        .items-header h2 {

            font-size: 22px;
        }


        .clear-btn {

            color: #2563eb;

            text-decoration: none;

            font-size: 14px;

            font-weight: 600;
        }


        .clear-btn:hover {

            text-decoration: underline;
        }


        /* =====================================================
           ITEMS GRID
        ===================================================== */

        .items {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 22px;
        }


        /* =====================================================
           ITEM CARD
        ===================================================== */

        .item-card {

            background: white;

            border-radius: 12px;

            overflow: hidden;

            box-shadow:
                0 3px 15px rgba(0,0,0,0.07);

            transition: 0.2s;
        }


        .item-card:hover {

            transform: translateY(-3px);

            box-shadow:
                0 7px 20px rgba(0,0,0,0.10);
        }


        /* =====================================================
           ITEM IMAGE
        ===================================================== */

        .item-image {

            width: 100%;

            height: 210px;

            object-fit: cover;

            background: #f3f4f6;
        }


        .no-image {

            width: 100%;

            height: 210px;

            display: flex;

            align-items: center;

            justify-content: center;

            background: #f3f4f6;

            color: #9ca3af;

            font-size: 35px;
        }


        /* =====================================================
           ITEM DETAILS
        ===================================================== */

        .item-details {

            padding: 18px;
        }


        .item-details h3 {

            font-size: 19px;

            margin-bottom: 12px;

            color: #111827;
        }


        .item-details p {

            font-size: 13px;

            color: #6b7280;

            margin-bottom: 7px;

            line-height: 1.5;
        }


        .item-details strong {

            color: #374151;
        }


        /* =====================================================
           AVAILABILITY
        ===================================================== */

        .available {

            display: inline-block;

            background: #dcfce7;

            color: #166534;

            padding: 5px 10px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: 600;
        }


        .notavailable {

            display: inline-block;

            background: #fee2e2;

            color: #991b1b;

            padding: 5px 10px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: 600;
        }


        /* =====================================================
           BORROW BUTTON
        ===================================================== */

        .borrow-btn {

            display: block;

            width: 100%;

            text-align: center;

            padding: 11px 15px;

            margin-top: 15px;

            border-radius: 7px;

            text-decoration: none;

            font-size: 14px;

            font-weight: 600;

            transition: 0.2s;
        }


        /* =====================================================
           AVAILABLE - BORROW ITEM
        ===================================================== */

        .borrow-btn.available-btn {

            background: #2563eb;

            color: white;
        }


        .borrow-btn.available-btn:hover {

            background: #1d4ed8;

            transform: translateY(-1px);
        }


        /* =====================================================
           OWN ITEM
        ===================================================== */

        .borrow-btn.own-btn {

            background: #e5e7eb;

            color: #6b7280;

            cursor: not-allowed;
        }


        /* =====================================================
           CURRENTLY BORROWED
        ===================================================== */

        .borrow-btn.disabled-btn {

            background: #f3f4f6;

            color: #9ca3af;

            cursor: not-allowed;
        }


        /* =====================================================
           EMPTY
        ===================================================== */

        .empty-box {

            background: white;

            padding: 50px 20px;

            text-align: center;

            border-radius: 12px;

            box-shadow:
                0 3px 12px rgba(0,0,0,0.05);
        }


        .empty-box i {

            font-size: 45px;

            color: #9ca3af;

            margin-bottom: 15px;
        }


        .empty-box h3 {

            margin-bottom: 8px;
        }


        .empty-box p {

            color: #6b7280;
        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 1100px) {

            .cards {

                grid-template-columns:
                    repeat(2, 1fr);
            }

            .items {

                grid-template-columns:
                    repeat(2, 1fr);
            }
        }


        @media (max-width: 768px) {

            .sidebar {

                position: static;

                width: 100%;

                height: auto;
            }


            .main {

                margin-left: 0;

                padding: 20px;
            }


            header {

                flex-direction: column;

                align-items: stretch;
            }


            .search-box {

                max-width: 100%;
            }


            .profile {

                justify-content: flex-end;
            }


            .cards {

                grid-template-columns: 1fr;
            }


            .items {

                grid-template-columns: 1fr;
            }
        }


        @media (max-width: 500px) {

            .items-header {

                flex-direction: column;

                align-items: flex-start;

                gap: 8px;
            }


            .page-title h1 {

                font-size: 25px;
            }
        }

    </style>

</head>


<body>


<!-- =====================================================
     SIDEBAR
===================================================== -->

<div class="sidebar">


    <div class="logo">

        <?php if (
            file_exists(
                __DIR__ . "/images/logo.png"
            )
        ): ?>

            <img
                src="images/logo.png"
                alt="CampusShare Logo"
            >

        <?php endif; ?>


        <h2>
            CampusShare
        </h2>

        <p>
            Community Sharing
        </p>

    </div>


    <ul>


        <!-- Dashboard -->

        <li>

            <a href="dashboard.php">

                <i class="fa-solid fa-house"></i>

                <span>
                    Dashboard
                </span>

            </a>

        </li>


        <!-- Add Item -->

        <li>

            <a href="add_item.php">

                <i class="fa-solid fa-plus"></i>

                <span>
                    Add Item
                </span>

            </a>

        </li>


        <!-- Browse Items -->

        <li class="active">

            <a href="browse_items.php">

                <i class="fa-solid fa-box"></i>

                <span>
                    Browse Items
                </span>

            </a>

        </li>


        <!-- My Items -->

        <li>

            <a href="my_items.php">

                <i class="fa-solid fa-book"></i>

                <span>
                    My Items
                </span>

            </a>

        </li>


        <!-- My Borrow Requests -->

        <li>

            <a href="my_borrow_requests.php">

                <i class="fa-solid fa-handshake"></i>

                <span>
                    My Borrow Requests
                </span>

            </a>

        </li>


        <!-- Notifications -->

        <li>

            <a href="notifications.php">

                <i class="fa-solid fa-bell"></i>

                <span>
                    Notifications
                </span>


                <?php if ($notification_count > 0): ?>

                    <span
                        style="
                            background:#dc2626;
                            color:white;
                            padding:2px 6px;
                            border-radius:10px;
                            font-size:10px;
                            margin-left:auto;
                        "
                    >

                        <?php
                        echo $notification_count;
                        ?>

                    </span>

                <?php endif; ?>

            </a>

        </li>


        <!-- Profile -->

        <li>

            <a href="profile.php">

                <i class="fa-solid fa-user"></i>

                <span>
                    Profile
                </span>

            </a>

        </li>


        <!-- Logout -->

        <li>

            <a href="logout.php">

                <i class="fa-solid fa-right-from-bracket"></i>

                <span>
                    Logout
                </span>

            </a>

        </li>


    </ul>

</div>


<!-- =====================================================
     MAIN
===================================================== -->

<div class="main">


    <!-- =================================================
         HEADER
    ================================================== -->

    <header>


        <!-- SEARCH -->

        <form
            method="GET"
            action="browse_items.php"
            class="search-box"
        >


            <?php if ($category_id > 0): ?>

                <input
                    type="hidden"
                    name="category_id"
                    value="<?php
                    echo $category_id;
                    ?>"
                >

            <?php endif; ?>


            <input
                type="text"
                name="search"
                placeholder="Search items..."
                value="<?php
                echo htmlspecialchars($search);
                ?>"
            >


            <button
                type="submit"
                title="Search"
            >

                <i class="fa-solid fa-search"></i>

            </button>


        </form>


        <!-- PROFILE -->

        <div class="profile">


            <!-- Notification -->

            <a
                href="notifications.php"
                class="notification-bell"
                title="Notifications"
            >

                <i class="fa-solid fa-bell"></i>


                <?php if ($notification_count > 0): ?>

                    <span class="notification-count">

                        <?php
                        echo $notification_count;
                        ?>

                    </span>

                <?php endif; ?>

            </a>


            <!-- Profile Image -->

            <?php

            $profile_image =
                basename(
                    $user['profile_image'] ?? ''
                );


            if (
                !empty($profile_image) &&
                file_exists(
                    __DIR__ .
                    "/uploads/" .
                    $profile_image
                )
            ):

            ?>

                <img
                    src="uploads/<?php
                    echo rawurlencode(
                        $profile_image
                    );
                    ?>"
                    alt="Profile"
                >

            <?php else: ?>

                <img
                    src="images/default.jpg"
                    alt="Profile"
                >

            <?php endif; ?>


            <span>

                <?php
                echo htmlspecialchars(
                    $user['full_name']
                );
                ?>

            </span>


        </div>


    </header>


    <!-- =================================================
         PAGE TITLE
    ================================================== -->

    <div class="page-title">

        <h1>
            Browse Items
        </h1>

        <p>
            Find items shared by other members
            of the community.
        </p>

    </div>


    <!-- =================================================
         ALERTS
    ================================================== -->

    <?php if (!empty($success)): ?>

        <div class="alert success">

            <i class="fa-solid fa-circle-check"></i>

            <?php
            echo htmlspecialchars($success);
            ?>

        </div>

    <?php endif; ?>


    <?php if (!empty($error)): ?>

        <div class="alert error">

            <i class="fa-solid fa-circle-exclamation"></i>

            <?php
            echo htmlspecialchars($error);
            ?>

        </div>

    <?php endif; ?>


    <!-- =================================================
         STATISTICS
    ================================================== -->

    <section class="cards">


        <!-- My Shared Items -->

        <div class="card">

            <div class="card-icon blue">

                <i class="fa-solid fa-box"></i>

            </div>

            <div>

                <h2>
                    <?php
                    echo $shared_items;
                    ?>
                </h2>

                <p>
                    My Shared Items
                </p>

            </div>

        </div>


        <!-- Borrowed Items -->

        <div class="card">

            <div class="card-icon green">

                <i class="fa-solid fa-handshake"></i>

            </div>

            <div>

                <h2>
                    <?php
                    echo $borrowed_items;
                    ?>
                </h2>

                <p>
                    Borrowed Items
                </p>

            </div>

        </div>


        <!-- Available Items -->

        <div class="card">

            <div class="card-icon orange">

                <i class="fa-solid fa-circle-check"></i>

            </div>

            <div>

                <h2>
                    <?php
                    echo $available_items;
                    ?>
                </h2>

                <p>
                    Available Items
                </p>

            </div>

        </div>


        <!-- Pending Requests -->

        <div class="card">

            <div class="card-icon red">

                <i class="fa-solid fa-hourglass-half"></i>

            </div>

            <div>

                <h2>
                    <?php
                    echo $pending_requests;
                    ?>
                </h2>

                <p>
                    Pending Requests
                </p>

            </div>

        </div>


    </section>


    <!-- =================================================
         CATEGORIES
    ================================================== -->

    <section class="category-section">


        <h2>
            Browse by Category
        </h2>


        <div class="category-buttons">


            <!-- ALL ITEMS -->

            <a
                href="browse_items.php"
                class="category-btn
                <?php
                echo $category_id === 0
                    ? 'active'
                    : '';
                ?>"
            >

                All Items

            </a>


            <!-- CATEGORIES -->

            <?php foreach (
                $categories as $category
            ): ?>


                <a
                    href="browse_items.php?category_id=<?php
                    echo (int)
                        $category['category_id'];
                    ?>"
                    class="category-btn
                    <?php
                    echo $category_id ===
                        (int)$category['category_id']
                        ? 'active'
                        : '';
                    ?>"
                >

                    <?php
                    echo htmlspecialchars(
                        $category['category_name']
                    );
                    ?>

                </a>


            <?php endforeach; ?>


        </div>


    </section>


    <!-- =================================================
         ITEMS HEADER
    ================================================== -->

    <div class="items-header">


        <h2>

            <?php if ($category_id > 0): ?>

                Selected Category

            <?php elseif ($search !== ''): ?>

                Search Results

            <?php else: ?>

                Available Items

            <?php endif; ?>

        </h2>


        <?php if (
            $search !== '' ||
            $category_id > 0
        ): ?>

            <a
                href="browse_items.php"
                class="clear-btn"
            >

                Clear Filters

            </a>

        <?php endif; ?>


    </div>


    <!-- =================================================
         ITEMS
    ================================================== -->

    <div class="items">


        <?php if ($item_result->num_rows > 0): ?>


            <?php while (
                $item =
                $item_result->fetch_assoc()
            ): ?>


                <div class="item-card">


                    <!-- =================================================
                         IMAGE
                    ================================================= -->

                    <?php

                    $image_file =
                        basename(
                            $item['image'] ?? ''
                        );


                    $image_path = "";


                    if (
                        !empty($image_file) &&
                        file_exists(
                            __DIR__ .
                            "/uploads/items/" .
                            $image_file
                        )
                    ) {

                        $image_path =
                            "uploads/items/" .
                            rawurlencode(
                                $image_file
                            );
                    }

                    ?>


                    <?php if (!empty($image_path)): ?>

                        <img
                            src="<?php
                            echo htmlspecialchars(
                                $image_path
                            );
                            ?>"
                            alt="<?php
                            echo htmlspecialchars(
                                $item['item_name']
                            );
                            ?>"
                            class="item-image"
                        >

                    <?php else: ?>

                        <div class="no-image">

                            <i class="fa-solid fa-image"></i>

                        </div>

                    <?php endif; ?>


                    <!-- =================================================
                         DETAILS
                    ================================================== -->

                    <div class="item-details">


                        <h3>

                            <?php
                            echo htmlspecialchars(
                                $item['item_name']
                            );
                            ?>

                        </h3>


                        <!-- Category -->

                        <p>

                            <strong>
                                Category:
                            </strong>

                            <?php
                            echo htmlspecialchars(
                                $item['category_name']
                                ?? 'Uncategorized'
                            );
                            ?>

                        </p>


                        <!-- Owner -->

                        <p>

                            <strong>
                                Owner:
                            </strong>

                            <?php
                            echo htmlspecialchars(
                                $item['owner_name']
                            );
                            ?>

                        </p>


                        <!-- Location -->

                        <p>

                            <strong>
                                Location:
                            </strong>

                            <?php
                            echo htmlspecialchars(
                                $item['location']
                                ?: 'Not specified'
                            );
                            ?>

                        </p>


                        <!-- Condition -->

                        <p>

                            <strong>
                                Condition:
                            </strong>

                            <?php
                            echo htmlspecialchars(
                                $item['item_condition']
                                ?: 'Not specified'
                            );
                            ?>

                        </p>


                        <!-- Availability -->

                        <p>

                            <strong>
                                Availability:
                            </strong>


                            <?php if (
                                $item['availability']
                                === 'Available'
                            ): ?>

                                <span class="available">

                                    Available

                                </span>

                            <?php else: ?>

                                <span class="notavailable">

                                    Not Available

                                </span>

                            <?php endif; ?>

                        </p>


                        <!-- =================================================
                             BORROW BUTTON
                        ================================================== -->

                        <?php if (
                            (int)$item['user_id']
                            === $user_id
                        ): ?>


                            <!-- Own Item -->

                            <div class="borrow-btn own-btn">

                                <i class="fa-solid fa-user"></i>

                                Your Item

                            </div>


                        <?php elseif (
                            $item['availability']
                            === 'Available'
                        ): ?>


                            <!-- =================================================
                                 IMPORTANT:
                                 ONLY borrow_request.php
                                 NO borrow_items.php
                            ================================================= -->

                            <a
                                href="borrow_request.php?item_id=<?php
                                echo (int)
                                    $item['item_id'];
                                ?>"
                                class="borrow-btn available-btn"
                            >

                                <i
                                    class="fa-solid fa-hand-holding"
                                ></i>

                                Borrow Item

                            </a>


                        <?php else: ?>


                            <!-- Currently Borrowed -->

                            <div class="borrow-btn disabled-btn">

                                <i
                                    class="fa-solid fa-ban"
                                ></i>

                                Currently Borrowed

                            </div>


                        <?php endif; ?>


                    </div>


                </div>


            <?php endwhile; ?>


        <?php else: ?>


            <!-- =================================================
                 NO ITEMS
            ================================================== -->

            <div
                class="empty-box"
                style="grid-column: 1 / -1;"
            >

                <i class="fa-solid fa-box-open"></i>


                <h3>
                    No Items Found
                </h3>


                <p>

                    <?php if ($search !== ''): ?>

                        No items matched your search.

                    <?php elseif ($category_id > 0): ?>

                        No items are available
                        in this category.

                    <?php else: ?>

                        There are currently
                        no items to browse.

                    <?php endif; ?>

                </p>


                <?php if (
                    $search !== '' ||
                    $category_id > 0
                ): ?>

                    <br>

                    <a
                        href="browse_items.php"
                        class="clear-btn"
                    >

                        View All Items

                    </a>

                <?php endif; ?>


            </div>


        <?php endif; ?>


    </div>


</div>


</body>

</html>


<?php

/* =====================================================
   CLOSE DATABASE
===================================================== */

$stmt->close();

$conn->close();

?>