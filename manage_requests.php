<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include("includes/db.php");

$user_id = $_SESSION['user_id'];


/* =====================================================
   GET LOGGED-IN USER
===================================================== */

$user_query = mysqli_query($conn, "
    SELECT *
    FROM users
    WHERE user_id = '$user_id'
");

if (!$user_query) {
    die("User query failed: " . mysqli_error($conn));
}

$user = mysqli_fetch_assoc($user_query);


/* =====================================================
   GET BORROW REQUESTS
   Show only latest request for same item + borrower
===================================================== */

$request_query = mysqli_query($conn, "

    SELECT
        br.request_id,
        br.item_id,
        br.borrower_id,
        br.borrow_date,
        br.expected_return_date,
        br.actual_return_date,
        br.status,
        br.request_date,

        i.item_name,

        u.full_name AS borrower_name,
        u.email AS borrower_email,
        u.phone AS borrower_phone

    FROM borrow_requests br

    INNER JOIN items i
        ON br.item_id = i.item_id

    INNER JOIN users u
        ON br.borrower_id = u.user_id

    WHERE i.user_id = '$user_id'

    AND br.request_id = (

        SELECT MAX(br2.request_id)

        FROM borrow_requests br2

        WHERE br2.item_id = br.item_id

        AND br2.borrower_id = br.borrower_id
    )

    ORDER BY br.request_id DESC

");

if (!$request_query) {
    die("Request query failed: " . mysqli_error($conn));
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

    <title>Manage Requests | CampusShare</title>


    <!-- CSS -->

    <link
        rel="stylesheet"
        href="css/manage_requests.css"
    >


    <!-- FONT AWESOME -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

</head>


<body>


<!-- =====================================================
     SIDEBAR
===================================================== -->

<div class="sidebar">


    <!-- LOGO -->

    <div class="logo">

        <img
            src="images/logo.png"
            alt="CampusShare Logo"
        >

        <h2>CampusShare</h2>

    </div>


    <!-- MENU -->

    <ul>


        <li>

            <a href="dashboard.php">

                <i class="fa-solid fa-house"></i>

                <span>Dashboard</span>

            </a>

        </li>


        <li>

            <a href="add_item.php">

                <i class="fa-solid fa-plus"></i>

                <span>Add Item</span>

            </a>

        </li>


        <li>

            <a href="my_items.php">

                <i class="fa-solid fa-box"></i>

                <span>My Items</span>

            </a>

        </li>


        <li>

            <a href="browse_items.php">

                <i class="fa-solid fa-magnifying-glass"></i>

                <span>Browse Items</span>

            </a>

        </li>


        <li class="active">

            <a href="manage_requests.php">

                <i class="fa-solid fa-handshake"></i>

                <span>Manage Requests</span>

            </a>

        </li>


        <li>

            <a href="notifications.php">

                <i class="fa-solid fa-bell"></i>

                <span>Notifications</span>

            </a>

        </li>


        <li>

            <a href="profile.php">

                <i class="fa-solid fa-user"></i>

                <span>Profile</span>

            </a>

        </li>


        <li>

            <a href="logout.php">

                <i class="fa-solid fa-right-from-bracket"></i>

                <span>Logout</span>

            </a>

        </li>


    </ul>

</div>



<!-- =====================================================
     MAIN CONTENT
===================================================== -->

<div class="main">


    <!-- =================================================
         HEADER
    ================================================== -->

    <header>


        <div class="heading">

            <p class="small-title">
                REQUEST MANAGEMENT
            </p>


            <h1>
                Manage Borrow Requests
            </h1>


            <p class="subtitle">
                Review and manage requests for your shared items.
            </p>

        </div>



        <!-- PROFILE -->

        <div class="profile">

            <img
                src="images/default.png"
                alt="Profile"
            >


            <div>

                <span class="profile-name">

                    <?php

                    echo htmlspecialchars(
                        $user['full_name']
                    );

                    ?>

                </span>


                <small>
                    Item Owner
                </small>

            </div>

        </div>

    </header>



    <!-- =================================================
         REQUEST CONTAINER
    ================================================== -->

    <div class="request-container">


        <?php

        if (mysqli_num_rows($request_query) > 0) {


            while (
                $request =
                mysqli_fetch_assoc($request_query)
            ) {


                $status = strtolower(
                    trim($request['status'])
                );

        ?>


        <!-- =================================================
             REQUEST CARD
        ================================================== -->

        <div class="request-card">


            <!-- CARD TOP -->

            <div class="card-top">


                <!-- ITEM ICON -->

                <div class="item-icon">

                    <i class="fa-solid fa-box-open"></i>

                </div>


                <!-- ITEM NAME -->

                <div class="item-title">

                    <h2>

                        <?php

                        echo htmlspecialchars(
                            $request['item_name']
                        );

                        ?>

                    </h2>


                    <span>
                        Borrow Request
                    </span>

                </div>


                <!-- STATUS -->

                <div class="status <?php echo htmlspecialchars($status); ?>">

                    <span class="status-dot"></span>


                    <?php

                    echo htmlspecialchars(
                        $request['status']
                    );

                    ?>

                </div>

            </div>



            <!-- =================================================
                 CARD BODY
            ================================================== -->

            <div class="card-body">


                <!-- BORROWER -->

                <div class="info-row">

                    <div class="info-label">

                        <i class="fa-solid fa-user"></i>

                        <span>
                            Borrower
                        </span>

                    </div>


                    <strong>

                        <?php

                        echo htmlspecialchars(
                            $request['borrower_name']
                        );

                        ?>

                    </strong>

                </div>



                <!-- EMAIL -->

                <div class="info-row">

                    <div class="info-label">

                        <i class="fa-solid fa-envelope"></i>

                        <span>
                            Email
                        </span>

                    </div>


                    <span class="value">

                        <?php

                        echo htmlspecialchars(
                            $request['borrower_email']
                        );

                        ?>

                    </span>

                </div>



                <!-- PHONE -->

                <div class="info-row">

                    <div class="info-label">

                        <i class="fa-solid fa-phone"></i>

                        <span>
                            Phone
                        </span>

                    </div>


                    <span class="value">

                        <?php

                        if (!empty(
                            $request['borrower_phone']
                        )) {

                            echo htmlspecialchars(
                                $request['borrower_phone']
                            );

                        } else {

                            echo "Not provided";

                        }

                        ?>

                    </span>

                </div>



                <!-- BORROW DATE -->

                <div class="info-row">

                    <div class="info-label">

                        <i class="fa-regular fa-calendar"></i>

                        <span>
                            Borrow Date
                        </span>

                    </div>


                    <span class="value">

                        <?php

                        if (!empty(
                            $request['borrow_date']
                        )) {

                            echo date(
                                "d M Y",
                                strtotime(
                                    $request['borrow_date']
                                )
                            );

                        } else {

                            echo "Not available";

                        }

                        ?>

                    </span>

                </div>



                <!-- EXPECTED RETURN -->

                <div class="info-row">

                    <div class="info-label">

                        <i class="fa-solid fa-calendar-check"></i>

                        <span>
                            Expected Return
                        </span>

                    </div>


                    <span class="value">

                        <?php

                        if (!empty(
                            $request['expected_return_date']
                        )) {

                            echo date(
                                "d M Y",
                                strtotime(
                                    $request[
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



                <!-- ACTUAL RETURN -->

                <?php

                if (!empty(
                    $request['actual_return_date']
                )) {

                ?>

                <div class="info-row">

                    <div class="info-label">

                        <i class="fa-solid fa-rotate-left"></i>

                        <span>
                            Actual Return
                        </span>

                    </div>


                    <span class="value">

                        <?php

                        echo date(
                            "d M Y",
                            strtotime(
                                $request[
                                    'actual_return_date'
                                ]
                            )
                        );

                        ?>

                    </span>

                </div>

                <?php

                }

                ?>


            </div>



            <!-- =================================================
                 CARD FOOTER
            ================================================== -->

            <div class="card-footer">


                <!-- VIEW BORROWER -->

                <a
                    href="borrower_details.php?request_id=<?php echo $request['request_id']; ?>"
                    class="view-btn"
                >

                    <i class="fa-solid fa-user"></i>

                    View Borrower Details

                </a>



                <?php


                /* ================================================
                   PENDING
                ================================================= */

                if ($status == "pending") {

                ?>


                    <!-- APPROVE -->

                    <a
                        href="approve_request.php?request_id=<?php echo $request['request_id']; ?>"
                        class="approve-btn"

                        onclick="
                            return confirm(
                                'Are you sure you want to approve this request?'
                            );
                        "
                    >

                        <i class="fa-solid fa-check"></i>

                        Approve

                    </a>



                    <!-- REJECT -->

                    <a
                        href="reject_request.php?request_id=<?php echo $request['request_id']; ?>"
                        class="reject-btn"

                        onclick="
                            return confirm(
                                'Are you sure you want to reject this request?'
                            );
                        "
                    >

                        <i class="fa-solid fa-xmark"></i>

                        Reject

                    </a>


                <?php


                }


                /* ================================================
                   APPROVED
                ================================================= */

                elseif ($status == "approved") {

                ?>


                    <!-- MARK AS RETURNED -->

                    <a
                        href="return_item.php?request_id=<?php echo $request['request_id']; ?>"
                        class="return-btn"

                        onclick="
                            return confirm(
                                'Has the borrower returned this item?'
                            );
                        "
                    >

                        <i class="fa-solid fa-rotate-left"></i>

                        Mark as Returned

                    </a>


                <?php


                }


                /* ================================================
                   RETURNED
                ================================================= */

                elseif ($status == "returned") {

                ?>


                    <!-- RETURNED MESSAGE -->

                    <span class="returned-message">

                        <i class="fa-solid fa-circle-check"></i>

                        Item Returned

                    </span>


                <?php

                }

                ?>


            </div>


        </div>


        <?php

            }

        }

        else {

        ?>


        <!-- =================================================
             NO REQUESTS
        ================================================== -->

        <div class="no-requests">


            <div class="empty-icon">

                <i class="fa-solid fa-handshake"></i>

            </div>


            <h2>
                No Borrow Requests
            </h2>


            <p>
                You don't have any borrow requests
                for your items yet.
            </p>


            <a
                href="browse_items.php"
                class="browse-btn"
            >

                <i class="fa-solid fa-magnifying-glass"></i>

                Browse Items

            </a>


        </div>


        <?php

        }

        ?>


    </div>


</div>


</body>

</html>           