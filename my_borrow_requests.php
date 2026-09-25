<?php

session_start();

/* =========================================================
   LOGIN CHECK
========================================================= */

if (!isset($_SESSION['user_id'])) {

    header("Location: login.php");
    exit();

}


/* =========================================================
   DATABASE + EMAIL
========================================================= */

require_once "includes/db.php";
require_once "includes/notification_mail.php";


/* =========================================================
   USER ID
========================================================= */

$user_id = (int) $_SESSION['user_id'];

$user_name = $_SESSION['full_name'] ?? 'User';


/* =========================================================
   MESSAGE
========================================================= */

$message = "";
$message_type = "";


/* =========================================================
   CANCEL PENDING REQUEST
========================================================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['cancel_request'])
) {

    $request_id =
        (int) ($_POST['request_id'] ?? 0);


    if ($request_id <= 0) {

        $message = "Invalid request.";
        $message_type = "error";

    } else {

        /*
         * Only the borrower who created the request
         * can cancel it.
         *
         * Only Pending request can be deleted.
         */

        $stmt = $conn->prepare("
            DELETE FROM borrow_requests

            WHERE request_id = ?
              AND borrower_id = ?
              AND status = 'Pending'
        ");


        if ($stmt) {

            $stmt->bind_param(
                "ii",
                $request_id,
                $user_id
            );


            if (
                $stmt->execute()
                && $stmt->affected_rows > 0
            ) {

                $message =
                    "Borrow request cancelled successfully.";

                $message_type = "success";

            } else {

                $message =
                    "This request cannot be cancelled.";

                $message_type = "error";

            }


            $stmt->close();

        } else {

            $message = "Database error.";
            $message_type = "error";

        }

    }

}


/* =========================================================
   ITEM RECEIVED
========================================================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['received'])
) {

    $request_id =
        (int) ($_POST['request_id'] ?? 0);


    /*
     * Get approved request details.
     */

    $stmt = $conn->prepare("
        SELECT

            br.request_id,
            br.item_id,
            br.owner_id,
            br.status,

            i.item_name,

            owner.full_name AS owner_name,
            owner.email AS owner_email

        FROM borrow_requests br

        INNER JOIN items i
            ON br.item_id = i.item_id

        INNER JOIN users owner
            ON br.owner_id = owner.user_id

        WHERE br.request_id = ?
          AND br.borrower_id = ?
          AND br.status = 'Approved'

        LIMIT 1
    ");


    if ($stmt) {

        $stmt->bind_param(
            "ii",
            $request_id,
            $user_id
        );

        $stmt->execute();

        $result =
            $stmt->get_result();

        $request =
            $result->fetch_assoc();

        $stmt->close();


        if ($request) {

            /*
             * Approved
             *     ↓
             * Item Received
             */

            $update = $conn->prepare("
                UPDATE borrow_requests

                SET status = 'Item Received'

                WHERE request_id = ?
                  AND borrower_id = ?
                  AND status = 'Approved'
            ");


            if ($update) {

                $update->bind_param(
                    "ii",
                    $request_id,
                    $user_id
                );


                if (
                    $update->execute()
                    && $update->affected_rows > 0
                ) {

                    /*
                     * Send email to owner.
                     */

                    sendItemReceivedEmail(
                        $request['owner_email'],
                        $request['owner_name'],
                        $user_name,
                        $request['item_name']
                    );


                    $message =
                        "Item received successfully. "
                        . "The owner has been notified.";

                    $message_type = "success";

                } else {

                    $message =
                        "Unable to update the request.";

                    $message_type = "error";

                }


                $update->close();

            }

        } else {

            $message =
                "This request is not ready for Item Received.";

            $message_type = "error";

        }

    }

}


/* =========================================================
   RETURN ITEM
========================================================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['return_item'])
) {

    $request_id =
        (int) ($_POST['request_id'] ?? 0);


    /*
     * Get Item Received request.
     */

    $stmt = $conn->prepare("
        SELECT

            br.request_id,
            br.item_id,
            br.owner_id,
            br.status,

            i.item_name,

            owner.full_name AS owner_name,
            owner.email AS owner_email

        FROM borrow_requests br

        INNER JOIN items i
            ON br.item_id = i.item_id

        INNER JOIN users owner
            ON br.owner_id = owner.user_id

        WHERE br.request_id = ?
          AND br.borrower_id = ?
          AND br.status = 'Item Received'

        LIMIT 1
    ");


    if ($stmt) {

        $stmt->bind_param(
            "ii",
            $request_id,
            $user_id
        );

        $stmt->execute();

        $result =
            $stmt->get_result();

        $request =
            $result->fetch_assoc();

        $stmt->close();


        if ($request) {

            /*
             * Item Received
             *       ↓
             * Return Requested
             */

            $update = $conn->prepare("
                UPDATE borrow_requests

                SET status = 'Return Requested'

                WHERE request_id = ?
                  AND borrower_id = ?
                  AND status = 'Item Received'
            ");


            if ($update) {

                $update->bind_param(
                    "ii",
                    $request_id,
                    $user_id
                );


                if (
                    $update->execute()
                    && $update->affected_rows > 0
                ) {

                    /*
                     * Notify owner by email.
                     */

                    sendReturnRequestEmail(
                        $request['owner_email'],
                        $request['owner_name'],
                        $user_name,
                        $request['item_name']
                    );


                    $message =
                        "Return request sent to the owner.";

                    $message_type = "success";

                } else {

                    $message =
                        "Unable to send return request.";

                    $message_type = "error";

                }


                $update->close();

            }

        } else {

            $message =
                "You can request a return only after receiving the item.";

            $message_type = "error";

        }

    }

}


/* =========================================================
   GET CURRENT BORROW REQUESTS
========================================================= */

$stmt = $conn->prepare("
    SELECT

        br.*,

        i.item_name,
        i.description,
        i.item_condition,
        i.availability,
        i.location,
        i.image,

        owner.full_name AS owner_name,
        owner.email AS owner_email

    FROM borrow_requests br

    INNER JOIN items i
        ON br.item_id = i.item_id

    INNER JOIN users owner
        ON br.owner_id = owner.user_id

    WHERE br.borrower_id = ?

      AND br.request_id = (

        SELECT br2.request_id

        FROM borrow_requests br2

        WHERE br2.item_id = br.item_id
          AND br2.borrower_id = br.borrower_id

        ORDER BY

            CASE br2.status

                WHEN 'Return Requested' THEN 1
                WHEN 'Item Received' THEN 2
                WHEN 'Approved' THEN 3
                WHEN 'Pending' THEN 4
                WHEN 'Rejected' THEN 5
                WHEN 'Returned' THEN 6

                ELSE 7

            END,

            br2.request_date DESC,
            br2.request_id DESC

        LIMIT 1

      )

    ORDER BY br.request_date DESC
");


$stmt->bind_param(
    "i",
    $user_id
);

$stmt->execute();

$result =
    $stmt->get_result();

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>My Borrow Requests | CampusShare</title>

    <link
        rel="stylesheet"
        href="css/my_borrow_requests.css?v=30"
    >

</head>


<body>


<!-- =====================================================
     SIDEBAR
===================================================== -->

<aside class="sidebar">

    <div class="logo">

        <div class="logo-icon">
            CS
        </div>

        <div class="logo-title">
            CampusShare
        </div>

        <div class="logo-subtitle">
            Community Sharing
        </div>

    </div>


    <div class="menu-title">
        MAIN MENU
    </div>


    <a href="dashboard.php">
        <span>🏠</span>
        Dashboard
    </a>


    <a href="add_item.php">
        <span>➕</span>
        Add Item
    </a>


    <a href="my_items.php">
        <span>📦</span>
        My Items
    </a>


    <a href="browse_items.php">
        <span>🔍</span>
        Browse Items
    </a>


    <a
        href="my_borrow_requests.php"
        class="active"
    >
        <span>📋</span>
        My Borrow Requests
    </a>


    <a href="manage_requests.php">
        <span>📝</span>
        Manage Requests
    </a>


    <div class="menu-title">
        ACCOUNT
    </div>


    <a href="notifications.php">
        <span>🔔</span>
        Notifications
    </a>


    <a href="profile.php">
        <span>👤</span>
        Profile
    </a>


    <a href="feedback.php">
        <span>⭐</span>
        Feedback
    </a>


    <a href="contact.php">
        <span>✉️</span>
        Contact Admin
    </a>


    <a href="logout.php">
        <span>🚪</span>
        Logout
    </a>

</aside>



<!-- =====================================================
     MAIN
===================================================== -->

<main class="main">


    <!-- HEADER -->

    <div class="page-header">

        <div>

            <h1>
                My Borrow Requests
            </h1>

            <p>
                Track your requests, received items and returns.
            </p>

        </div>


        <a
            href="browse_items.php"
            class="browse-btn"
        >
            🔍 Browse Items
        </a>

    </div>



    <!-- MESSAGE -->

    <?php if (!empty($message)): ?>

        <div
            class="message
            <?php echo htmlspecialchars($message_type); ?>"
        >

            <?php

            echo nl2br(
                htmlspecialchars($message)
            );

            ?>

        </div>

    <?php endif; ?>



    <!-- REQUESTS -->

    <section class="request-section">


        <?php if ($result->num_rows > 0): ?>


            <?php while ($row = $result->fetch_assoc()): ?>


                <?php

                /*
                 * Image
                 */

                $imageFile =
                    basename(
                        $row['image'] ?? ''
                    );


                $imagePath = "";


                if (
                    !empty($imageFile)
                    && file_exists(
                        __DIR__
                        . "/uploads/items/"
                        . $imageFile
                    )
                ) {

                    $imagePath =
                        "uploads/items/"
                        . rawurlencode($imageFile);

                }


                /*
                 * Status class
                 */

                $statusClass = "pending";


                if (
                    $row['status']
                    === 'Approved'
                ) {

                    $statusClass = "approved";

                }

                elseif (
                    $row['status']
                    === 'Item Received'
                ) {

                    $statusClass = "received";

                }

                elseif (
                    $row['status']
                    === 'Return Requested'
                ) {

                    $statusClass =
                        "return-requested";

                }

                elseif (
                    $row['status']
                    === 'Rejected'
                ) {

                    $statusClass = "rejected";

                }

                elseif (
                    $row['status']
                    === 'Returned'
                ) {

                    $statusClass = "returned";

                }

                ?>


                <!-- REQUEST CARD -->

                <article class="request-card">


                    <!-- IMAGE -->

                    <div class="item-image">


                        <?php if (!empty($imagePath)): ?>

                            <img
                                src="<?php
                                echo htmlspecialchars(
                                    $imagePath
                                );
                                ?>"
                                alt="<?php
                                echo htmlspecialchars(
                                    $row['item_name']
                                );
                                ?>"
                            >

                        <?php else: ?>

                            <div class="no-image">
                                📦
                            </div>

                        <?php endif; ?>


                    </div>



                    <!-- CONTENT -->

                    <div class="request-content">


                        <div class="request-heading">


                            <div>

                                <div class="request-number">
                                    Request #
                                    <?php
                                    echo (int)
                                        $row['request_id'];
                                    ?>
                                </div>


                                <h2>

                                    <?php
                                    echo htmlspecialchars(
                                        $row['item_name']
                                    );
                                    ?>

                                </h2>

                            </div>


                            <span
                                class="status
                                <?php
                                echo $statusClass;
                                ?>"
                            >

                                <?php
                                echo htmlspecialchars(
                                    $row['status']
                                );
                                ?>

                            </span>


                        </div>



                        <!-- DETAILS -->

                        <div class="details-grid">


                            <div class="detail">

                                <span class="detail-label">
                                    Owner
                                </span>

                                <span class="detail-value">

                                    <?php
                                    echo htmlspecialchars(
                                        $row['owner_name']
                                    );
                                    ?>

                                </span>

                            </div>


                            <div class="detail">

                                <span class="detail-label">
                                    Location
                                </span>

                                <span class="detail-value">

                                    <?php
                                    echo htmlspecialchars(
                                        $row['location']
                                        ?? 'Not specified'
                                    );
                                    ?>

                                </span>

                            </div>


                            <div class="detail">

                                <span class="detail-label">
                                    Condition
                                </span>

                                <span class="detail-value">

                                    <?php
                                    echo htmlspecialchars(
                                        $row['item_condition']
                                        ?? 'Not specified'
                                    );
                                    ?>

                                </span>

                            </div>


                            <div class="detail">

                                <span class="detail-label">
                                    Borrow Date
                                </span>

                                <span class="detail-value">

                                    <?php

                                    echo !empty(
                                        $row['borrow_date']
                                    )

                                        ? date(
                                            "d-m-Y",
                                            strtotime(
                                                $row['borrow_date']
                                            )
                                        )

                                        : "Not specified";

                                    ?>

                                </span>

                            </div>


                            <div class="detail">

                                <span class="detail-label">
                                    Expected Return
                                </span>

                                <span class="detail-value">

                                    <?php

                                    echo !empty(
                                        $row[
                                            'expected_return_date'
                                        ]
                                    )

                                        ? date(
                                            "d-m-Y",
                                            strtotime(
                                                $row[
                                                    'expected_return_date'
                                                ]
                                            )
                                        )

                                        : "Not specified";

                                    ?>

                                </span>

                            </div>


                            <div class="detail">

                                <span class="detail-label">
                                    Request Date
                                </span>

                                <span class="detail-value">

                                    <?php

                                    echo !empty(
                                        $row['request_date']
                                    )

                                        ? date(
                                            "d-m-Y h:i A",
                                            strtotime(
                                                $row['request_date']
                                            )
                                        )

                                        : "Not specified";

                                    ?>

                                </span>

                            </div>


                        </div>



                        <!-- =================================================
                             PENDING
                        ================================================== -->

                        <?php if (
                            $row['status']
                            === 'Pending'
                        ): ?>


                            <div class="action-box pending-box">

                                <div class="action-icon">
                                    ⏳
                                </div>


                                <div class="action-text">

                                    <h3>
                                        Request Pending
                                    </h3>

                                    <p>
                                        Your request has been sent to
                                        the owner. Please wait for the
                                        owner to accept or reject it.
                                    </p>


                                    <form
                                        method="POST"
                                        class="inline-form"
                                    >

                                        <input
                                            type="hidden"
                                            name="request_id"
                                            value="<?php
                                            echo (int)
                                                $row['request_id'];
                                            ?>"
                                        >


                                        <button
                                            type="submit"
                                            name="cancel_request"
                                            class="btn cancel-btn"
                                            onclick="
                                                return confirm(
                                                    'Are you sure you want to cancel this request?'
                                                );
                                            "
                                        >
                                            ✕ Cancel Request
                                        </button>

                                    </form>

                                </div>

                            </div>



                        <!-- =================================================
                             APPROVED
                        ================================================== -->

                        <?php elseif (
                            $row['status']
                            === 'Approved'
                        ): ?>


                            <div class="action-box approved-box">

                                <div class="action-icon">
                                    ✓
                                </div>


                                <div class="action-text">

                                    <h3>
                                        Request Accepted
                                    </h3>

                                    <p>
                                        The owner has accepted your
                                        borrow request.
                                    </p>

                                    <p>
                                        After you physically receive
                                        the item, click
                                        <strong>
                                            Item Received
                                        </strong>.
                                    </p>


                                    <form
                                        method="POST"
                                        class="inline-form"
                                    >

                                        <input
                                            type="hidden"
                                            name="request_id"
                                            value="<?php
                                            echo (int)
                                                $row['request_id'];
                                            ?>"
                                        >


                                        <button
                                            type="submit"
                                            name="received"
                                            class="btn received-btn"
                                            onclick="
                                                return confirm(
                                                    'Have you received this item from the owner?'
                                                );
                                            "
                                        >
                                            ✓ Item Received
                                        </button>

                                    </form>

                                </div>

                            </div>



                        <!-- =================================================
                             ITEM RECEIVED
                        ================================================== -->

                        <?php elseif (
                            $row['status']
                            === 'Item Received'
                        ): ?>


                            <div class="action-box received-box">

                                <div class="action-icon">
                                    📦
                                </div>


                                <div class="action-text">

                                    <h3>
                                        Item Received
                                    </h3>

                                    <p>
                                        You have confirmed that you
                                        received the item.
                                    </p>

                                    <p>
                                        When you return the item to the
                                        owner, click
                                        <strong>
                                            Return Item
                                        </strong>.
                                    </p>


                                    <form
                                        method="POST"
                                        class="inline-form"
                                    >

                                        <input
                                            type="hidden"
                                            name="request_id"
                                            value="<?php
                                            echo (int)
                                                $row['request_id'];
                                            ?>"
                                        >


                                        <button
                                            type="submit"
                                            name="return_item"
                                            class="btn return-btn"
                                            onclick="
                                                return confirm(
                                                    'Do you want to send a return request to the owner?'
                                                );
                                            "
                                        >
                                            ↩ Return Item
                                        </button>

                                    </form>

                                </div>

                            </div>



                        <!-- =================================================
                             RETURN REQUESTED
                        ================================================== -->

                        <?php elseif (
                            $row['status']
                            === 'Return Requested'
                        ): ?>


                            <div class="action-box return-request-box">

                                <div class="action-icon">
                                    ↩
                                </div>


                                <div class="action-text">

                                    <h3>
                                        Return Request Sent
                                    </h3>

                                    <p>
                                        Your return request has been
                                        sent to the owner.
                                    </p>

                                    <p>
                                        Please wait for the owner to
                                        accept the return.
                                    </p>


                                    <div class="waiting-label">
                                        ⏳ Waiting for Owner
                                    </div>

                                </div>

                            </div>



                        <!-- =================================================
                             REJECTED
                        ================================================== -->

                        <?php elseif (
                            $row['status']
                            === 'Rejected'
                        ): ?>


                            <div class="action-box rejected-box">

                                <div class="action-icon">
                                    ✕
                                </div>


                                <div class="action-text">

                                    <h3>
                                        Request Rejected
                                    </h3>


                                    <p>
                                        The owner rejected your
                                        borrow request.
                                    </p>


                                    <?php if (
                                        !empty(
                                            $row['owner_message']
                                        )
                                    ): ?>

                                        <div class="owner-message">

                                            <strong>
                                                Owner Message
                                            </strong>

                                            <p>
                                                <?php
                                                echo nl2br(
                                                    htmlspecialchars(
                                                        $row[
                                                            'owner_message'
                                                        ]
                                                    )
                                                );
                                                ?>
                                            </p>

                                        </div>

                                    <?php endif; ?>


                                    <a
                                        href="borrow_request.php?item_id=<?php
                                        echo (int)
                                            $row['item_id'];
                                        ?>"
                                        class="btn request-again-btn"
                                    >
                                        ↻ Request Again
                                    </a>

                                </div>

                            </div>



                        <!-- =================================================
                             RETURNED
                        ================================================== -->

                        <?php elseif (
                            $row['status']
                            === 'Returned'
                        ): ?>


                            <div class="action-box returned-box">

                                <div class="action-icon">
                                    ✓
                                </div>


                                <div class="action-text">

                                    <h3>
                                        Item Returned
                                    </h3>

                                    <p>
                                        The owner accepted the return.
                                    </p>

                                    <p>
                                        The item has been returned
                                        successfully and is available
                                        again.
                                    </p>

                                </div>

                            </div>


                        <?php endif; ?>


                    </div>


                </article>


            <?php endwhile; ?>


        <?php else: ?>


            <div class="empty-state">

                <div class="empty-icon">
                    📋
                </div>

                <h2>
                    No Borrow Requests
                </h2>

                <p>
                    You have not sent any borrow requests yet.
                </p>


                <a
                    href="browse_items.php"
                    class="btn browse-empty-btn"
                >
                    🔍 Browse Items
                </a>

            </div>


        <?php endif; ?>


    </section>


</main>


</body>

</html>