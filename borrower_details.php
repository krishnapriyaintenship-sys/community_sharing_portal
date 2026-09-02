<?php
session_start();

require_once 'includes/db.php';


/* =========================================================
   CHECK LOGIN
   ========================================================= */

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];


/* =========================================================
   GET REQUEST ID
   ========================================================= */

if (!isset($_GET['request_id']) || !is_numeric($_GET['request_id'])) {
    header("Location: manage_requests.php");
    exit();
}

$request_id = (int) $_GET['request_id'];


/* =========================================================
   GET BORROWER DETAILS
   Only the owner of the item can see the details.
   ========================================================= */

$sql = "
    SELECT
        br.request_id,
        br.status,
        br.borrow_date,
        br.expected_return_date,
        br.actual_return_date,

        i.item_id,
        i.item_name,

        u.user_id AS borrower_id,
        u.full_name AS borrower_name,
        u.email AS borrower_email,
        u.phone AS borrower_phone

    FROM borrow_requests br

    INNER JOIN items i
        ON br.item_id = i.item_id

    INNER JOIN users u
        ON br.borrower_id = u.user_id

    WHERE br.request_id = ?
    AND i.user_id = ?

    LIMIT 1
";


$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Database error: " . mysqli_error($conn));
}


mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $request_id,
    $user_id
);


mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);


/* =========================================================
   REQUEST NOT FOUND
   ========================================================= */

if (mysqli_num_rows($result) === 0) {

    mysqli_stmt_close($stmt);

    header("Location: manage_requests.php");
    exit();
}


$borrower = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/* =========================================================
   STATUS CLASS
   ========================================================= */

$status = strtolower(
    trim($borrower['status'])
);

$status_class = "bd-pending";

if ($status === "approved") {
    $status_class = "bd-approved";
}

if ($status === "returned") {
    $status_class = "bd-returned";
}

if ($status === "rejected") {
    $status_class = "bd-rejected";
}

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
        Borrower Details - CampusShare
    </title>


    <!-- =====================================================
         IMPORTANT:
         SEPARATE CSS FOR BORROWER DETAILS
         ===================================================== -->

    <link
        rel="stylesheet"
        type="text/css"
        href="/CommunityItemSharingPortal/css/borrower_details.css?v=10"
    >

</head>


<body class="bd-page">


<!-- =========================================================
     SIDEBAR
     ========================================================= -->

<aside class="bd-sidebar">


    <!-- =====================================================
         LOGO
         ===================================================== -->

    <div class="bd-logo-box">

        <img
            src="/CommunityItemSharingPortal/images/logo.png"
            alt="CampusShare Logo"
            width="60"
            height="60"
            class="bd-logo"
        >

        <div class="bd-logo-name">
            CampusShare
        </div>

    </div>


    <!-- =====================================================
         NAVIGATION
         ===================================================== -->

    <nav class="bd-nav">


        <a href="dashboard.php">

            <span class="bd-icon">⌂</span>

            <span>
                Dashboard
            </span>

        </a>


        <a href="add_item.php">

            <span class="bd-icon">＋</span>

            <span>
                Add Item
            </span>

        </a>


        <a href="my_items.php">

            <span class="bd-icon">▣</span>

            <span>
                My Items
            </span>

        </a>


        <a href="browse_items.php">

            <span class="bd-icon">⌕</span>

            <span>
                Browse Items
            </span>

        </a>


        <a
            href="manage_requests.php"
            class="bd-active"
        >

            <span class="bd-icon">▤</span>

            <span>
                Manage Requests
            </span>

        </a>


        <a href="notifications.php">

            <span class="bd-icon">♢</span>

            <span>
                Notifications
            </span>

        </a>


        <a href="profile.php">

            <span class="bd-icon">♙</span>

            <span>
                Profile
            </span>

        </a>


        <a href="logout.php">

            <span class="bd-icon">⇥</span>

            <span>
                Logout
            </span>

        </a>


    </nav>

</aside>



<!-- =========================================================
     MAIN CONTENT
     ========================================================= -->

<main class="bd-main">


    <!-- =====================================================
         PAGE HEADER
         ===================================================== -->

    <div class="bd-header">


        <div class="bd-label">
            BORROWER INFORMATION
        </div>


        <h1>
            Borrower Details
        </h1>


        <p>
            View the contact and borrowing information of the borrower.
        </p>


    </div>



    <!-- =====================================================
         BACK BUTTON
         ===================================================== -->

    <a
        href="manage_requests.php"
        class="bd-back-button"
    >

        ← Back to Manage Requests

    </a>



    <!-- =====================================================
         DETAILS CARD
         ===================================================== -->

    <div class="bd-details-card">


        <!-- =================================================
             CARD HEADER
             ================================================= -->

        <div class="bd-card-header">


            <div class="bd-profile-section">


                <div class="bd-avatar">

                    <?php

                    $name = trim(
                        $borrower['borrower_name']
                    );

                    $first_letter = !empty($name)
                        ? strtoupper($name[0])
                        : "U";

                    echo htmlspecialchars(
                        $first_letter
                    );

                    ?>

                </div>


                <div>

                    <h2>
                        <?= htmlspecialchars(
                            $borrower['borrower_name']
                        ); ?>
                    </h2>

                    <p>
                        Borrower
                    </p>

                </div>


            </div>


            <!-- STATUS -->

            <span
                class="bd-status <?= $status_class; ?>"
            >

                <?= htmlspecialchars(
                    ucfirst($borrower['status'])
                ); ?>

            </span>


        </div>



        <!-- =================================================
             BORROWER INFORMATION
             ================================================= -->

        <div class="bd-section">


            <h3>
                Contact Information
            </h3>


            <!-- NAME -->

            <div class="bd-info-row">


                <div class="bd-info-icon">
                    👤
                </div>


                <div class="bd-info-content">

                    <span class="bd-info-label">
                        Full Name
                    </span>

                    <span class="bd-info-value">
                        <?= htmlspecialchars(
                            $borrower['borrower_name']
                        ); ?>
                    </span>

                </div>


            </div>



            <!-- EMAIL -->

            <div class="bd-info-row">


                <div class="bd-info-icon">
                    ✉
                </div>


                <div class="bd-info-content">

                    <span class="bd-info-label">
                        Email
                    </span>

                    <span class="bd-info-value">

                        <?php if (!empty($borrower['borrower_email'])): ?>

                            <?= htmlspecialchars(
                                $borrower['borrower_email']
                            ); ?>

                        <?php else: ?>

                            Not available

                        <?php endif; ?>

                    </span>

                </div>


            </div>



            <!-- PHONE -->

            <div class="bd-info-row">


                <div class="bd-info-icon">
                    ☎
                </div>


                <div class="bd-info-content">

                    <span class="bd-info-label">
                        Phone
                    </span>

                    <span class="bd-info-value">

                        <?php if (!empty($borrower['borrower_phone'])): ?>

                            <?= htmlspecialchars(
                                $borrower['borrower_phone']
                            ); ?>

                        <?php else: ?>

                            Not available

                        <?php endif; ?>

                    </span>

                </div>


            </div>


        </div>



        <!-- =================================================
             BORROWING INFORMATION
             ================================================= -->

        <div class="bd-section">


            <h3>
                Borrowing Information
            </h3>


            <!-- ITEM -->

            <div class="bd-info-row">


                <div class="bd-info-icon">
                    ▣
                </div>


                <div class="bd-info-content">

                    <span class="bd-info-label">
                        Item
                    </span>

                    <span class="bd-info-value">
                        <?= htmlspecialchars(
                            $borrower['item_name']
                        ); ?>
                    </span>

                </div>


            </div>



            <!-- BORROW DATE -->

            <div class="bd-info-row">


                <div class="bd-info-icon">
                    📅
                </div>


                <div class="bd-info-content">

                    <span class="bd-info-label">
                        Borrow Date
                    </span>

                    <span class="bd-info-value">

                        <?php

                        if (!empty($borrower['borrow_date'])) {

                            echo date(
                                "d M Y",
                                strtotime(
                                    $borrower['borrow_date']
                                )
                            );

                        } else {

                            echo "Not available";

                        }

                        ?>

                    </span>

                </div>


            </div>



            <!-- EXPECTED RETURN -->

            <div class="bd-info-row">


                <div class="bd-info-icon">
                    📅
                </div>


                <div class="bd-info-content">

                    <span class="bd-info-label">
                        Expected Return
                    </span>

                    <span class="bd-info-value">

                        <?php

                        if (
                            !empty(
                                $borrower[
                                    'expected_return_date'
                                ]
                            )
                        ) {

                            echo date(
                                "d M Y",
                                strtotime(
                                    $borrower[
                                        'expected_return_date'
                                    ]
                                )
                            );

                        } else {

                            echo "Not available";

                        }

                        ?>

                    </span>

                </div>


            </div>



            <!-- ACTUAL RETURN -->

            <?php if (!empty($borrower['actual_return_date'])): ?>

                <div class="bd-info-row">


                    <div class="bd-info-icon">
                        ✓
                    </div>


                    <div class="bd-info-content">

                        <span class="bd-info-label">
                            Actual Return
                        </span>

                        <span class="bd-info-value">

                            <?= date(
                                "d M Y",
                                strtotime(
                                    $borrower[
                                        'actual_return_date'
                                    ]
                                )
                            ); ?>

                        </span>

                    </div>


                </div>

            <?php endif; ?>


        </div>



        <!-- =================================================
             BOTTOM BUTTON
             ================================================= -->

        <div class="bd-bottom">

            <a
                href="manage_requests.php"
                class="bd-main-button"
            >

                ← Back to Manage Requests

            </a>

        </div>


    </div>


</main>


</body>

</html>