<?php

session_start();

if (!isset($_SESSION['user_id'])) {

    header("Location: login.php");

    exit();
}

include("includes/db.php");

$user_id = $_SESSION['user_id'];


/* Check request ID */

if (!isset($_GET['request_id']) || empty($_GET['request_id'])) {

    echo "<script>
            alert('Invalid Request ID.');
            window.location='manage_requests.php';
          </script>";

    exit();
}


$request_id = intval($_GET['request_id']);


/*
   Get borrower details.

   Important:
   i.user_id = logged-in user
   This ensures that only the owner of the item
   can see the borrower's details.
*/

$query = mysqli_query($conn, "

    SELECT

        br.request_id,
        br.borrow_date,
        br.expected_return_date,
        br.actual_return_date,
        br.status,
        br.request_date,

        i.item_id,
        i.item_name,

        u.user_id AS borrower_id,
        u.full_name,
        u.email,
        u.phone,
        u.profile_image

    FROM borrow_requests br

    INNER JOIN items i
        ON br.item_id = i.item_id

    INNER JOIN users u
        ON br.borrower_id = u.user_id

    WHERE br.request_id='$request_id'

    AND i.user_id='$user_id'

");


/* Request not found */

if (!$query || mysqli_num_rows($query) == 0) {

    echo "<script>
            alert('Borrow request not found.');
            window.location='manage_requests.php';
          </script>";

    exit();
}


$request = mysqli_fetch_assoc($query);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Borrower Details | CampusShare</title>

<link rel="stylesheet"
href="css/borrower_details.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>


<body>


<div class="container">


    <!-- PAGE TITLE -->

    <h1>

        <i class="fa-solid fa-user"></i>

        Borrower Details

    </h1>


    <!-- BORROWER CARD -->

    <div class="card">


        <div class="profile-section">

            <?php

            if (
                !empty($request['profile_image']) &&
                file_exists(
                    "uploads/" . $request['profile_image']
                )
            ) {

            ?>

                <img
                    src="uploads/<?php
                    echo htmlspecialchars(
                        $request['profile_image']
                    );
                    ?>"
                    class="profile-image"
                    alt="Profile">

            <?php

            } else {

            ?>

                <img
                    src="images/default.png"
                    class="profile-image"
                    alt="Profile">

            <?php

            }

            ?>

        </div>


        <div class="details">


            <h2>

                <?php
                echo htmlspecialchars(
                    $request['full_name']
                );
                ?>

            </h2>


            <p>

                <strong>

                    <i class="fa-solid fa-envelope"></i>

                    Email:

                </strong>

                <?php
                echo htmlspecialchars(
                    $request['email']
                );
                ?>

            </p>


            <p>

                <strong>

                    <i class="fa-solid fa-phone"></i>

                    Phone:

                </strong>

                <?php

                if (!empty($request['phone'])) {

                    echo htmlspecialchars(
                        $request['phone']
                    );

                } else {

                    echo "Not provided";

                }

                ?>

            </p>


        </div>

    </div>



    <!-- BORROW REQUEST DETAILS -->

    <div class="card request-card">


        <h2>

            <i class="fa-solid fa-handshake"></i>

            Borrow Request Details

        </h2>


        <p>

            <strong>Item:</strong>

            <?php
            echo htmlspecialchars(
                $request['item_name']
            );
            ?>

        </p>


        <p>

            <strong>Borrow Date:</strong>

            <?php
            echo date(
                "d M Y",
                strtotime(
                    $request['borrow_date']
                )
            );
            ?>

        </p>


        <p>

            <strong>Expected Return Date:</strong>

            <?php
            echo date(
                "d M Y",
                strtotime(
                    $request['expected_return_date']
                )
            );
            ?>

        </p>


        <?php

        if (!empty($request['actual_return_date'])) {

        ?>

        <p>

            <strong>Actual Return Date:</strong>

            <?php

            echo date(
                "d M Y",
                strtotime(
                    $request['actual_return_date']
                )
            );

            ?>

        </p>

        <?php

        }

        ?>


        <p>

            <strong>Status:</strong>

            <span class="status">

                <?php
                echo htmlspecialchars(
                    $request['status']
                );
                ?>

            </span>

        </p>


        <p>

            <strong>Request Date:</strong>

            <?php

            if (!empty($request['request_date'])) {

                echo date(
                    "d M Y h:i A",
                    strtotime(
                        $request['request_date']
                    )
                );

            } else {

                echo "Not available";

            }

            ?>

        </p>


    </div>


    <!-- BACK BUTTON -->

    <div class="back">

        <a
            href="manage_requests.php"
            class="back-btn">

            <i class="fa-solid fa-arrow-left"></i>

            Back to Manage Requests

        </a>

    </div>


</div>


</body>

</html>