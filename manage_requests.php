<?php
session_start();

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/notification_mail.php';


/* =========================================================
   LOGIN CHECK
========================================================= */

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=Please login first.");
    exit;
}

$owner_id = (int) $_SESSION['user_id'];

$message = '';
$message_type = '';


/* =========================================================
   SAFE EMAIL FUNCTION
========================================================= */

function safeEmailCall($functionName, array $arguments)
{
    try {

        if (function_exists($functionName)) {
            call_user_func_array($functionName, $arguments);
        }

        return true;

    } catch (Throwable $e) {

        /*
         * Email error should not stop the database action.
         */
        error_log(
            "Email error in {$functionName}: " .
            $e->getMessage()
        );

        return false;
    }
}


/* =========================================================
   HANDLE POST ACTIONS
========================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $request_id = (int) ($_POST['request_id'] ?? 0);
    $action = trim($_POST['action'] ?? '');
    $owner_message = trim($_POST['owner_message'] ?? '');

    if ($request_id <= 0) {

        $message = "Invalid request ID.";
        $message_type = "error";

    } else {

        /* =====================================================
           GET REQUEST
        ===================================================== */

        $stmt = $conn->prepare("
            SELECT
                br.request_id,
                br.item_id,
                br.borrower_id,
                br.owner_id,
                br.borrow_date,
                br.expected_return_date,
                br.actual_return_date,
                br.status,
                br.owner_message,
                br.request_date,
                br.approved_date,
                br.returned_date,

                i.item_name,
                i.description,
                i.item_condition,
                i.availability,
                i.location,
                i.image,

                borrower.full_name AS borrower_name,
                borrower.email AS borrower_email,
                borrower.phone AS borrower_phone,
                borrower.department AS borrower_department,
                borrower.year_of_study AS borrower_year,

                owner.full_name AS owner_name,
                owner.email AS owner_email

            FROM borrow_requests br

            INNER JOIN items i
                ON br.item_id = i.item_id

            INNER JOIN users borrower
                ON br.borrower_id = borrower.user_id

            INNER JOIN users owner
                ON br.owner_id = owner.user_id

            WHERE br.request_id = ?
              AND br.owner_id = ?

            LIMIT 1
        ");

        $stmt->bind_param(
            "ii",
            $request_id,
            $owner_id
        );

        $stmt->execute();

        $result = $stmt->get_result();

        $request = $result->fetch_assoc();

        $stmt->close();


        if (!$request) {

            $message =
                "Request not found or you are not authorized to manage it.";

            $message_type = "error";

        } else {


            /* =================================================
               APPROVE REQUEST
            ================================================= */

            if ($action === 'approve') {

                if ($request['status'] !== 'Pending') {

                    $message =
                        "Only pending requests can be approved.";

                    $message_type = "error";

                } else {

                    /*
                     * Check whether another active request
                     * already exists for this same item.
                     *
                     * We exclude the current request.
                     */
                    $stmt = $conn->prepare("
                        SELECT request_id, status
                        FROM borrow_requests
                        WHERE item_id = ?
                          AND request_id != ?
                          AND status IN (
                              'Approved',
                              'Item Received',
                              'Return Requested'
                          )
                        ORDER BY request_id DESC
                        LIMIT 1
                    ");

                    $stmt->bind_param(
                        "ii",
                        $request['item_id'],
                        $request_id
                    );

                    $stmt->execute();

                    $active_result = $stmt->get_result();

                    $active_request = $active_result->fetch_assoc();

                    $stmt->close();


                    if ($active_request) {

                        /*
                         * Another borrower is already using
                         * this item.
                         */
                        $message =
                            "This item already has an active borrow request. " .
                            "This pending request cannot be approved.";

                        $message_type = "error";

                    } else {

                        /*
                         * Approve current request.
                         *
                         * We do NOT check availability here because
                         * the screenshot shows that the availability
                         * value can remain Borrowed even when the
                         * current request is Pending.
                         *
                         * Instead, we checked for another ACTIVE
                         * borrow request above.
                         */

                        $stmt = $conn->prepare("
                            UPDATE borrow_requests
                            SET
                                status = 'Approved',
                                approved_date = NOW(),
                                owner_message = NULL
                            WHERE request_id = ?
                              AND owner_id = ?
                              AND status = 'Pending'
                        ");

                        $stmt->bind_param(
                            "ii",
                            $request_id,
                            $owner_id
                        );

                        $updated = $stmt->execute();

                        $stmt->close();


                        if ($updated) {

                            /*
                             * Mark item as Borrowed.
                             */
                            $stmt = $conn->prepare("
                                UPDATE items
                                SET availability = 'Borrowed'
                                WHERE item_id = ?
                            ");

                            $stmt->bind_param(
                                "i",
                                $request['item_id']
                            );

                            $stmt->execute();

                            $stmt->close();


                            /*
                             * SEND APPROVAL EMAIL
             *
                             * IMPORTANT:
                             * Your function requires 5 arguments.
                             */
                            safeEmailCall(
                                'sendBorrowApprovedEmail',
                                [
                                    $request['borrower_email'],
                                    $request['borrower_name'],
                                    $request['owner_name'],
                                    $request['item_name'],
                                    $request['expected_return_date']
                                ]
                            );


                            $message =
                                "Borrow request approved successfully.";

                            $message_type = "success";

                        } else {

                            $message =
                                "Unable to approve the request.";

                            $message_type = "error";
                        }
                    }
                }
            }


            /* =================================================
               REJECT REQUEST
            ================================================= */

            elseif ($action === 'reject') {

                if ($request['status'] !== 'Pending') {

                    $message =
                        "Only pending requests can be rejected.";

                    $message_type = "error";

                } else {

                    $stmt = $conn->prepare("
                        UPDATE borrow_requests
                        SET
                            status = 'Rejected',
                            owner_message = ?
                        WHERE request_id = ?
                          AND owner_id = ?
                          AND status = 'Pending'
                    ");

                    $stmt->bind_param(
                        "sii",
                        $owner_message,
                        $request_id,
                        $owner_id
                    );

                    $updated = $stmt->execute();

                    $stmt->close();


                    if ($updated) {

                        safeEmailCall(
                            'sendBorrowRejectedEmail',
                            [
                                $request['borrower_email'],
                                $request['borrower_name'],
                                $request['item_name'],
                                $owner_message
                            ]
                        );

                        $message =
                            "Borrow request rejected successfully.";

                        $message_type = "success";

                    } else {

                        $message =
                            "Unable to reject the request.";

                        $message_type = "error";
                    }
                }
            }


            /* =================================================
               ACCEPT RETURN
            ================================================= */

            elseif ($action === 'accept_return') {

                if ($request['status'] !== 'Return Requested') {

                    $message =
                        "This request does not have a pending return.";

                    $message_type = "error";

                } else {

                    $stmt = $conn->prepare("
                        UPDATE borrow_requests
                        SET
                            status = 'Returned',
                            actual_return_date = CURDATE(),
                            returned_date = NOW()
                        WHERE request_id = ?
                          AND owner_id = ?
                          AND status = 'Return Requested'
                    ");

                    $stmt->bind_param(
                        "ii",
                        $request_id,
                        $owner_id
                    );

                    $updated = $stmt->execute();

                    $stmt->close();


                    if ($updated) {

                        /*
                         * Make item available.
                         */
                        $stmt = $conn->prepare("
                            UPDATE items
                            SET availability = 'Available'
                            WHERE item_id = ?
                        ");

                        $stmt->bind_param(
                            "i",
                            $request['item_id']
                        );

                        $stmt->execute();

                        $stmt->close();


                        safeEmailCall(
                            'sendReturnAcceptedEmail',
                            [
                                $request['borrower_email'],
                                $request['borrower_name'],
                                $request['item_name']
                            ]
                        );


                        $message =
                            "Return accepted successfully. " .
                            "The item is now available.";

                        $message_type = "success";

                    } else {

                        $message =
                            "Unable to accept the return.";

                        $message_type = "error";
                    }
                }
            }


            /* =================================================
               REJECT RETURN
            ================================================= */

            elseif ($action === 'reject_return') {

                if ($request['status'] !== 'Return Requested') {

                    $message =
                        "This request does not have a pending return.";

                    $message_type = "error";

                } else {

                    /*
                     * Return request rejected.
                     *
                     * Item remains borrowed.
                     */
                    $stmt = $conn->prepare("
                        UPDATE borrow_requests
                        SET status = 'Item Received'
                        WHERE request_id = ?
                          AND owner_id = ?
                          AND status = 'Return Requested'
                    ");

                    $stmt->bind_param(
                        "ii",
                        $request_id,
                        $owner_id
                    );

                    $updated = $stmt->execute();

                    $stmt->close();


                    if ($updated) {

                        $stmt = $conn->prepare("
                            UPDATE items
                            SET availability = 'Borrowed'
                            WHERE item_id = ?
                        ");

                        $stmt->bind_param(
                            "i",
                            $request['item_id']
                        );

                        $stmt->execute();

                        $stmt->close();


                        safeEmailCall(
                            'sendReturnRejectedEmail',
                            [
                                $request['borrower_email'],
                                $request['borrower_name'],
                                $request['item_name']
                            ]
                        );


                        $message =
                            "Return request rejected successfully.";

                        $message_type = "success";

                    } else {

                        $message =
                            "Unable to reject the return.";

                        $message_type = "error";
                    }
                }
            }


            else {

                $message = "Invalid action.";
                $message_type = "error";
            }
        }
    }
}


/* =========================================================
   FETCH REQUESTS
========================================================= */

$stmt = $conn->prepare("
    SELECT
        br.request_id,
        br.item_id,
        br.borrower_id,
        br.borrow_date,
        br.expected_return_date,
        br.actual_return_date,
        br.status,
        br.owner_message,
        br.request_date,
        br.approved_date,
        br.returned_date,

        i.item_name,
        i.description,
        i.item_condition,
        i.availability,
        i.location,
        i.image,

        u.full_name AS borrower_name,
        u.email AS borrower_email,
        u.phone AS borrower_phone,
        u.department AS borrower_department,
        u.year_of_study AS borrower_year

    FROM borrow_requests br

    INNER JOIN items i
        ON br.item_id = i.item_id

    INNER JOIN users u
        ON br.borrower_id = u.user_id

    WHERE br.owner_id = ?

    ORDER BY
        CASE br.status
            WHEN 'Pending' THEN 1
            WHEN 'Return Requested' THEN 2
            WHEN 'Approved' THEN 3
            WHEN 'Item Received' THEN 4
            WHEN 'Rejected' THEN 5
            WHEN 'Returned' THEN 6
            ELSE 7
        END,
        br.request_date DESC,
        br.request_id DESC
");

$stmt->bind_param("i", $owner_id);

$stmt->execute();

$result = $stmt->get_result();

$requests = [];

while ($row = $result->fetch_assoc()) {
    $requests[] = $row;
}

$stmt->close();


/* =========================================================
   COUNTS
========================================================= */

$pending_count = 0;
$return_count = 0;
$approved_count = 0;
$received_count = 0;

foreach ($requests as $row) {

    if ($row['status'] === 'Pending') {
        $pending_count++;
    }

    if ($row['status'] === 'Return Requested') {
        $return_count++;
    }

    if ($row['status'] === 'Approved') {
        $approved_count++;
    }

    if ($row['status'] === 'Item Received') {
        $received_count++;
    }
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

    <title>Manage Requests - Community Share</title>

    <link
        rel="stylesheet"
        href="css/manage_requests.css"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

</head>

<body>


<div class="page-wrapper">


    <!-- =====================================================
         SIDEBAR
    ====================================================== -->

    <aside class="sidebar">

        <div class="sidebar-logo">

            <div class="logo-icon">
                <i class="fa-solid fa-share-nodes"></i>
            </div>

            <div>
                <h2>Community Share</h2>
                <span>Item Sharing Portal</span>
            </div>

        </div>


        <nav class="sidebar-nav">

            <a href="dashboard.php">
                <i class="fa-solid fa-house"></i>
                <span>Dashboard</span>
            </a>

            <a href="browse_items.php">
                <i class="fa-solid fa-box-open"></i>
                <span>Browse Items</span>
            </a>

            <a href="my_items.php">
                <i class="fa-solid fa-box"></i>
                <span>My Items</span>
            </a>

            <a href="add_item.php">
                <i class="fa-solid fa-plus"></i>
                <span>Add Item</span>
            </a>

            <a href="my_borrow_requests.php">
                <i class="fa-solid fa-hand-holding"></i>
                <span>My Borrow Requests</span>
            </a>

            <a
                href="manage_requests.php"
                class="active"
            >
                <i class="fa-solid fa-list-check"></i>
                <span>Manage Requests</span>
            </a>

            <a href="contact.php">
                <i class="fa-solid fa-envelope"></i>
                <span>Contact</span>
            </a>

        </nav>


        <div class="sidebar-bottom">

            <a href="logout.php" class="logout-link">

                <i class="fa-solid fa-right-from-bracket"></i>

                <span>Logout</span>

            </a>

        </div>

    </aside>


    <!-- =====================================================
         MAIN CONTENT
    ====================================================== -->

    <main class="main-content">


        <!-- HEADER -->

        <header class="top-header">

            <div>

                <h1>
                    <i class="fa-solid fa-list-check"></i>
                    Manage Requests
                </h1>

                <p>
                    View and manage requests for your items.
                </p>

            </div>


            <div class="user-info">

                <div class="user-avatar">

                    <?php
                    echo strtoupper(
                        substr(
                            $_SESSION['full_name'] ?? 'U',
                            0,
                            1
                        )
                    );
                    ?>

                </div>

                <div>

                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $_SESSION['full_name'] ?? 'User'
                        );
                        ?>
                    </strong>

                    <small>Item Owner</small>

                </div>

            </div>

        </header>


        <!-- =====================================================
             ALERT
        ====================================================== -->

        <?php if (!empty($message)): ?>

            <div
                class="alert
                <?php
                echo $message_type === 'success'
                    ? 'alert-success'
                    : 'alert-error';
                ?>"
            >

                <i
                    class="fa-solid
                    <?php
                    echo $message_type === 'success'
                        ? 'fa-circle-check'
                        : 'fa-circle-exclamation';
                    ?>"
                ></i>

                <div>
                    <?php
                    echo nl2br(
                        htmlspecialchars($message)
                    );
                    ?>
                </div>

                <button
                    type="button"
                    class="close-alert"
                    onclick="this.parentElement.remove();"
                >
                    &times;
                </button>

            </div>

        <?php endif; ?>


        <!-- =====================================================
             STATISTICS
        ====================================================== -->

        <section class="stats-grid">

            <div class="stat-card">

                <div class="stat-icon pending-icon">
                    <i class="fa-solid fa-clock"></i>
                </div>

                <div>
                    <span>Pending</span>
                    <strong>
                        <?php echo $pending_count; ?>
                    </strong>
                </div>

            </div>


            <div class="stat-card">

                <div class="stat-icon return-icon">
                    <i class="fa-solid fa-rotate-left"></i>
                </div>

                <div>
                    <span>Return Requests</span>
                    <strong>
                        <?php echo $return_count; ?>
                    </strong>
                </div>

            </div>


            <div class="stat-card">

                <div class="stat-icon approved-icon">
                    <i class="fa-solid fa-check"></i>
                </div>

                <div>
                    <span>Approved</span>
                    <strong>
                        <?php echo $approved_count; ?>
                    </strong>
                </div>

            </div>


            <div class="stat-card">

                <div class="stat-icon received-icon">
                    <i class="fa-solid fa-box-open"></i>
                </div>

                <div>
                    <span>Item Received</span>
                    <strong>
                        <?php echo $received_count; ?>
                    </strong>
                </div>

            </div>

        </section>


        <!-- =====================================================
             REQUEST SECTION
        ====================================================== -->

        <section class="requests-section">


            <div class="section-header">

                <div>

                    <h2>Borrow & Return Requests</h2>

                    <p>
                        Click "View Details" to see complete request
                        information and available actions.
                    </p>

                </div>


                <div class="total-count">

                    <i class="fa-solid fa-list"></i>

                    <?php echo count($requests); ?>

                    Total Requests

                </div>

            </div>


            <?php if (empty($requests)): ?>


                <!-- EMPTY -->

                <div class="empty-state">

                    <div class="empty-icon">

                        <i class="fa-solid fa-inbox"></i>

                    </div>

                    <h3>No Requests Yet</h3>

                    <p>
                        You don't have any borrow or return requests
                        for your items.
                    </p>

                </div>


            <?php else: ?>


                <div class="requests-list">


                    <?php foreach ($requests as $row): ?>


                        <?php

                        $status_class = 'status-default';
                        $status_icon = 'fa-circle-info';

                        switch ($row['status']) {

                            case 'Pending':
                                $status_class = 'status-pending';
                                $status_icon = 'fa-clock';
                                break;

                            case 'Approved':
                                $status_class = 'status-approved';
                                $status_icon = 'fa-check';
                                break;

                            case 'Item Received':
                                $status_class = 'status-received';
                                $status_icon = 'fa-box-open';
                                break;

                            case 'Return Requested':
                                $status_class = 'status-return';
                                $status_icon = 'fa-rotate-left';
                                break;

                            case 'Returned':
                                $status_class = 'status-returned';
                                $status_icon = 'fa-circle-check';
                                break;

                            case 'Rejected':
                                $status_class = 'status-rejected';
                                $status_icon = 'fa-xmark';
                                break;
                        }

                        ?>


                        <!-- =================================================
                             REQUEST CARD
                        ================================================== -->

                        <article class="request-card">


                            <div class="card-top">


                                <div class="item-mini">

                                    <div class="item-mini-image">

                                        <?php if (!empty($row['image'])): ?>

                                            <img
                                                src="uploads/items/<?php
                                                echo htmlspecialchars(
                                                    $row['image']
                                                );
                                                ?>"
                                                alt=""
                                            >

                                        <?php else: ?>

                                            <div class="no-image">
                                                <i class="fa-solid fa-image"></i>
                                            </div>

                                        <?php endif; ?>

                                    </div>


                                    <div>

                                        <h3>
                                            <?php
                                            echo htmlspecialchars(
                                                $row['item_name']
                                            );
                                            ?>
                                        </h3>

                                        <p>
                                            Request #<?php
                                            echo (int) $row['request_id'];
                                            ?>
                                        </p>

                                    </div>

                                </div>


                                <span
                                    class="status-badge <?php
                                    echo $status_class;
                                    ?>"
                                >

                                    <i
                                        class="fa-solid <?php
                                        echo $status_icon;
                                        ?>"
                                    ></i>

                                    <?php
                                    echo htmlspecialchars(
                                        $row['status']
                                    );
                                    ?>

                                </span>

                            </div>


                            <!-- =================================================
                                 SHORT DETAILS
                            ================================================== -->

                            <div class="short-details">


                                <div class="short-detail">

                                    <span>
                                        <i class="fa-solid fa-user"></i>
                                        Borrower
                                    </span>

                                    <strong>
                                        <?php
                                        echo htmlspecialchars(
                                            $row['borrower_name']
                                        );
                                        ?>
                                    </strong>

                                </div>


                                <div class="short-detail">

                                    <span>
                                        <i class="fa-solid fa-calendar"></i>
                                        Borrow Date
                                    </span>

                                    <strong>
                                        <?php
                                        echo !empty(
                                            $row['borrow_date']
                                        )
                                            ? date(
                                                'd M Y',
                                                strtotime(
                                                    $row['borrow_date']
                                                )
                                            )
                                            : '-';
                                        ?>
                                    </strong>

                                </div>


                                <div class="short-detail">

                                    <span>
                                        <i class="fa-solid fa-calendar-check"></i>
                                        Expected Return
                                    </span>

                                    <strong>
                                        <?php
                                        echo !empty(
                                            $row['expected_return_date']
                                        )
                                            ? date(
                                                'd M Y',
                                                strtotime(
                                                    $row['expected_return_date']
                                                )
                                            )
                                            : '-';
                                        ?>
                                    </strong>

                                </div>


                                <div class="short-detail">

                                    <span>
                                        <i class="fa-solid fa-location-dot"></i>
                                        Location
                                    </span>

                                    <strong>
                                        <?php
                                        echo htmlspecialchars(
                                            $row['location'] ?: 'Not specified'
                                        );
                                        ?>
                                    </strong>

                                </div>

                            </div>


                            <!-- =================================================
                                 VIEW DETAILS BUTTON
                            ================================================== -->

                            <div class="card-footer">

                                <button
                                    type="button"
                                    class="view-details-btn"
                                    onclick="openDetailsModal(
                                        <?php
                                        echo (int) $row['request_id'];
                                        ?>
                                    )"
                                >

                                    <i class="fa-solid fa-eye"></i>

                                    View Details

                                </button>

                            </div>


                        </article>


                        <!-- =================================================
                             HIDDEN DETAILS DATA
                        ================================================== -->

                        <div
                            id="request-data-<?php
                            echo (int) $row['request_id'];
                            ?>"
                            class="request-data"
                            data-request-id="<?php
                            echo (int) $row['request_id'];
                            ?>"
                            data-status="<?php
                            echo htmlspecialchars(
                                $row['status'],
                                ENT_QUOTES
                            );
                            ?>"
                            data-item="<?php
                            echo htmlspecialchars(
                                $row['item_name'],
                                ENT_QUOTES
                            );
                            ?>"
                            data-description="<?php
                            echo htmlspecialchars(
                                $row['description'] ?? '',
                                ENT_QUOTES
                            );
                            ?>"
                            data-condition="<?php
                            echo htmlspecialchars(
                                $row['item_condition'] ?? '',
                                ENT_QUOTES
                            );
                            ?>"
                            data-availability="<?php
                            echo htmlspecialchars(
                                $row['availability'] ?? '',
                                ENT_QUOTES
                            );
                            ?>"
                            data-location="<?php
                            echo htmlspecialchars(
                                $row['location'] ?? '',
                                ENT_QUOTES
                            );
                            ?>"
                            data-borrower="<?php
                            echo htmlspecialchars(
                                $row['borrower_name'],
                                ENT_QUOTES
                            );
                            ?>"
                            data-email="<?php
                            echo htmlspecialchars(
                                $row['borrower_email'],
                                ENT_QUOTES
                            );
                            ?>"
                            data-phone="<?php
                            echo htmlspecialchars(
                                $row['borrower_phone'] ?? '',
                                ENT_QUOTES
                            );
                            ?>"
                            data-department="<?php
                            echo htmlspecialchars(
                                $row['borrower_department'] ?? '',
                                ENT_QUOTES
                            );
                            ?>"
                            data-year="<?php
                            echo htmlspecialchars(
                                $row['borrower_year'] ?? '',
                                ENT_QUOTES
                            );
                            ?>"
                            data-borrow-date="<?php
                            echo htmlspecialchars(
                                $row['borrow_date'] ?? '',
                                ENT_QUOTES
                            );
                            ?>"
                            data-return-date="<?php
                            echo htmlspecialchars(
                                $row['expected_return_date'] ?? '',
                                ENT_QUOTES
                            );
                            ?>"
                            data-actual-return="<?php
                            echo htmlspecialchars(
                                $row['actual_return_date'] ?? '',
                                ENT_QUOTES
                            );
                            ?>"
                            data-owner-message="<?php
                            echo htmlspecialchars(
                                $row['owner_message'] ?? '',
                                ENT_QUOTES
                            );
                            ?>"
                            data-request-date="<?php
                            echo htmlspecialchars(
                                $row['request_date'] ?? '',
                                ENT_QUOTES
                            );
                            ?>"
                        ></div>


                    <?php endforeach; ?>


                </div>


            <?php endif; ?>


        </section>


    </main>

</div>


<!-- =========================================================
     DETAILS MODAL
========================================================= -->

<div
    id="detailsModal"
    class="modal"
>


    <div class="modal-content details-modal">


        <!-- MODAL HEADER -->

        <div class="modal-header">

            <div>

                <span class="modal-small-title">
                    Borrow Request
                </span>

                <h2 id="modalItemName">
                    Item Name
                </h2>

            </div>


            <button
                type="button"
                class="modal-close"
                onclick="closeDetailsModal()"
            >
                &times;
            </button>

        </div>


        <!-- MODAL BODY -->

        <div class="modal-body">


            <!-- STATUS -->

            <div class="modal-status-row">

                <span
                    id="modalStatus"
                    class="status-badge"
                >
                    Status
                </span>

            </div>


            <!-- ITEM DETAILS -->

            <div class="detail-block">

                <h3>
                    <i class="fa-solid fa-box-open"></i>
                    Item Details
                </h3>


                <div class="detail-grid">

                    <div>
                        <span>Condition</span>
                        <strong id="modalCondition">-</strong>
                    </div>

                    <div>
                        <span>Availability</span>
                        <strong id="modalAvailability">-</strong>
                    </div>

                    <div>
                        <span>Location</span>
                        <strong id="modalLocation">-</strong>
                    </div>

                    <div class="full-detail">
                        <span>Description</span>
                        <strong id="modalDescription">
                            -
                        </strong>
                    </div>

                </div>

            </div>


            <!-- BORROWER -->

            <div class="detail-block">

                <h3>
                    <i class="fa-solid fa-user"></i>
                    Borrower Details
                </h3>


                <div class="detail-grid">

                    <div>
                        <span>Name</span>
                        <strong id="modalBorrower">-</strong>
                    </div>

                    <div>
                        <span>Email</span>
                        <strong id="modalEmail">-</strong>
                    </div>

                    <div>
                        <span>Phone</span>
                        <strong id="modalPhone">-</strong>
                    </div>

                    <div>
                        <span>Department</span>
                        <strong id="modalDepartment">-</strong>
                    </div>

                    <div>
                        <span>Year of Study</span>
                        <strong id="modalYear">-</strong>
                    </div>

                </div>

            </div>


            <!-- BORROW DATES -->

            <div class="detail-block">

                <h3>
                    <i class="fa-solid fa-calendar-days"></i>
                    Borrow Details
                </h3>


                <div class="detail-grid">

                    <div>
                        <span>Borrow Date</span>
                        <strong id="modalBorrowDate">-</strong>
                    </div>

                    <div>
                        <span>Expected Return</span>
                        <strong id="modalReturnDate">-</strong>
                    </div>

                    <div>
                        <span>Actual Return</span>
                        <strong id="modalActualReturn">-</strong>
                    </div>

                    <div>
                        <span>Request Date</span>
                        <strong id="modalRequestDate">-</strong>
                    </div>

                </div>

            </div>


            <!-- OWNER MESSAGE -->

            <div
                id="ownerMessageBlock"
                class="detail-block message-block"
                style="display:none;"
            >

                <h3>
                    <i class="fa-solid fa-message"></i>
                    Owner Message
                </h3>

                <p id="modalOwnerMessage"></p>

            </div>


            <!-- ACTION AREA -->

            <div
                id="modalActions"
                class="modal-actions"
            ></div>


        </div>

    </div>

</div>


<script>

/* =========================================================
   DETAILS MODAL
========================================================= */

function openDetailsModal(requestId)
{
    const data =
        document.getElementById(
            'request-data-' + requestId
        );

    if (!data) {
        return;
    }


    const status =
        data.dataset.status || '';

    const item =
        data.dataset.item || '';

    const description =
        data.dataset.description || '';

    const condition =
        data.dataset.condition || '';

    const availability =
        data.dataset.availability || '';

    const location =
        data.dataset.location || '';

    const borrower =
        data.dataset.borrower || '';

    const email =
        data.dataset.email || '';

    const phone =
        data.dataset.phone || '';

    const department =
        data.dataset.department || '';

    const year =
        data.dataset.year || '';

    const borrowDate =
        data.dataset.borrowDate || '';

    const returnDate =
        data.dataset.returnDate || '';

    const actualReturn =
        data.dataset.actualReturn || '';

    const ownerMessage =
        data.dataset.ownerMessage || '';

    const requestDate =
        data.dataset.requestDate || '';


    document.getElementById(
        'modalItemName'
    ).textContent = item || 'Item';


    document.getElementById(
        'modalCondition'
    ).textContent =
        condition || 'Not specified';


    document.getElementById(
        'modalAvailability'
    ).textContent =
        availability || 'Not specified';


    document.getElementById(
        'modalLocation'
    ).textContent =
        location || 'Not specified';


    document.getElementById(
        'modalDescription'
    ).textContent =
        description || 'No description available.';


    document.getElementById(
        'modalBorrower'
    ).textContent =
        borrower || '-';


    document.getElementById(
        'modalEmail'
    ).textContent =
        email || '-';


    document.getElementById(
        'modalPhone'
    ).textContent =
        phone || '-';


    document.getElementById(
        'modalDepartment'
    ).textContent =
        department || '-';


    document.getElementById(
        'modalYear'
    ).textContent =
        year || '-';


    document.getElementById(
        'modalBorrowDate'
    ).textContent =
        formatDate(borrowDate);


    document.getElementById(
        'modalReturnDate'
    ).textContent =
        formatDate(returnDate);


    document.getElementById(
        'modalActualReturn'
    ).textContent =
        formatDate(actualReturn);


    document.getElementById(
        'modalRequestDate'
    ).textContent =
        formatDateTime(requestDate);


    /* =====================================================
       STATUS
    ===================================================== */

    const statusElement =
        document.getElementById('modalStatus');

    statusElement.textContent = status;

    statusElement.className =
        'status-badge ' + getStatusClass(status);


    /* =====================================================
       OWNER MESSAGE
    ===================================================== */

    const messageBlock =
        document.getElementById(
            'ownerMessageBlock'
        );

    const messageText =
        document.getElementById(
            'modalOwnerMessage'
        );


    if (ownerMessage) {

        messageText.textContent =
            ownerMessage;

        messageBlock.style.display =
            'block';

    } else {

        messageBlock.style.display =
            'none';
    }


    /* =====================================================
       ACTION BUTTONS
    ===================================================== */

    const actions =
        document.getElementById(
            'modalActions'
        );

    actions.innerHTML = '';


    /* =====================================================
       PENDING
    ===================================================== */

    if (status === 'Pending') {

        actions.innerHTML = `

            <form
                method="POST"
                action="manage_requests.php"
            >

                <input
                    type="hidden"
                    name="request_id"
                    value="${requestId}"
                >

                <input
                    type="hidden"
                    name="action"
                    value="approve"
                >

                <button
                    type="submit"
                    class="modal-action approve-btn"
                >
                    <i class="fa-solid fa-check"></i>
                    Approve Request
                </button>

            </form>


            <button
                type="button"
                class="modal-action reject-btn"
                onclick="openRejectModal(${requestId})"
            >
                <i class="fa-solid fa-xmark"></i>
                Reject Request
            </button>

        `;
    }


    /* =====================================================
       RETURN REQUESTED
    ===================================================== */

    else if (status === 'Return Requested') {

        actions.innerHTML = `

            <form
                method="POST"
                action="manage_requests.php"
            >

                <input
                    type="hidden"
                    name="request_id"
                    value="${requestId}"
                >

                <input
                    type="hidden"
                    name="action"
                    value="accept_return"
                >

                <button
                    type="submit"
                    class="modal-action accept-return-btn"
                >
                    <i class="fa-solid fa-check"></i>
                    Accept Return
                </button>

            </form>


            <form
                method="POST"
                action="manage_requests.php"
            >

                <input
                    type="hidden"
                    name="request_id"
                    value="${requestId}"
                >

                <input
                    type="hidden"
                    name="action"
                    value="reject_return"
                >

                <button
                    type="submit"
                    class="modal-action reject-return-btn"
                >
                    <i class="fa-solid fa-xmark"></i>
                    Reject Return
                </button>

            </form>

        `;
    }


    /* =====================================================
       APPROVED
    ===================================================== */

    else if (status === 'Approved') {

        actions.innerHTML = `

            <div class="info-action approved-info">

                <i class="fa-solid fa-check-circle"></i>

                <span>
                    Request approved. Waiting for the borrower
                    to confirm item receipt.
                </span>

            </div>

        `;
    }


    /* =====================================================
       ITEM RECEIVED
    ===================================================== */

    else if (status === 'Item Received') {

        actions.innerHTML = `

            <div class="info-action received-info">

                <i class="fa-solid fa-box-open"></i>

                <span>
                    Borrower has received the item.
                    Waiting for return request.
                </span>

            </div>

        `;
    }


    /* =====================================================
       REJECTED
    ===================================================== */

    else if (status === 'Rejected') {

        actions.innerHTML = `

            <div class="info-action rejected-info">

                <i class="fa-solid fa-circle-xmark"></i>

                <span>
                    This borrow request was rejected.
                </span>

            </div>

        `;
    }


    /* =====================================================
       RETURNED
    ===================================================== */

    else if (status === 'Returned') {

        actions.innerHTML = `

            <div class="info-action returned-info">

                <i class="fa-solid fa-circle-check"></i>

                <span>
                    Item has been returned successfully.
                    It is available for borrowing again.
                </span>

            </div>

        `;
    }


    document
        .getElementById('detailsModal')
        .classList.add('show');

    document.body.classList.add('modal-open');
}


/* =========================================================
   CLOSE DETAILS MODAL
========================================================= */

function closeDetailsModal()
{
    document
        .getElementById('detailsModal')
        .classList.remove('show');

    document.body.classList.remove('modal-open');
}


/* =========================================================
   REJECT MODAL
========================================================= */

function openRejectModal(requestId)
{
    closeDetailsModal();

    const modal =
        document.getElementById(
            'rejectModal'
        );

    document.getElementById(
        'reject_request_id'
    ).value = requestId;

    document.getElementById(
        'owner_message'
    ).value = '';

    modal.classList.add('show');

    document.body.classList.add(
        'modal-open'
    );
}


function closeRejectModal()
{
    const modal =
        document.getElementById(
            'rejectModal'
        );

    if (modal) {
        modal.classList.remove('show');
    }

    document.body.classList.remove(
        'modal-open'
    );
}


/* =========================================================
   STATUS CLASS
========================================================= */

function getStatusClass(status)
{
    switch (status) {

        case 'Pending':
            return 'status-pending';

        case 'Approved':
            return 'status-approved';

        case 'Item Received':
            return 'status-received';

        case 'Return Requested':
            return 'status-return';

        case 'Returned':
            return 'status-returned';

        case 'Rejected':
            return 'status-rejected';

        default:
            return 'status-default';
    }
}


/* =========================================================
   DATE FORMAT
========================================================= */

function formatDate(dateString)
{
    if (!dateString) {
        return '-';
    }

    const date =
        new Date(dateString + 'T00:00:00');

    if (isNaN(date.getTime())) {
        return dateString;
    }

    return date.toLocaleDateString(
        'en-GB',
        {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        }
    );
}


/* =========================================================
   DATE TIME FORMAT
========================================================= */

function formatDateTime(dateString)
{
    if (!dateString) {
        return '-';
    }

    const date =
        new Date(
            dateString.replace(' ', 'T')
        );

    if (isNaN(date.getTime())) {
        return dateString;
    }

    return date.toLocaleString(
        'en-GB',
        {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        }
    );
}


/* =========================================================
   CLICK OUTSIDE MODAL
========================================================= */

document
    .getElementById('detailsModal')
    .addEventListener(
        'click',
        function(event) {

            if (event.target === this) {
                closeDetailsModal();
            }

        }
    );


const rejectModal =
    document.getElementById(
        'rejectModal'
    );

if (rejectModal) {

    rejectModal.addEventListener(
        'click',
        function(event) {

            if (event.target === this) {
                closeRejectModal();
            }

        }
    );

}


/* =========================================================
   ESCAPE KEY
========================================================= */

document.addEventListener(
    'keydown',
    function(event) {

        if (event.key === 'Escape') {

            closeDetailsModal();

            closeRejectModal();

        }

    }
);


/* =========================================================
   AUTO HIDE ALERT
========================================================= */

setTimeout(
    function() {

        const alertBox =
            document.querySelector('.alert');

        if (alertBox) {

            alertBox.style.opacity = '0';

            setTimeout(
                function() {

                    if (alertBox) {
                        alertBox.remove();
                    }

                },
                400
            );
        }

    },
    6000
);

</script>


<!-- =========================================================
     REJECT MODAL
========================================================= -->

<div
    id="rejectModal"
    class="modal"
>

    <div class="modal-content reject-modal">


        <div class="modal-header reject-header">

            <div>

                <span class="modal-small-title">
                    Request Action
                </span>

                <h2>
                    Reject Borrow Request
                </h2>

            </div>


            <button
                type="button"
                class="modal-close"
                onclick="closeRejectModal()"
            >
                &times;
            </button>

        </div>


        <form
            method="POST"
            action="manage_requests.php"
        >

            <input
                type="hidden"
                name="request_id"
                id="reject_request_id"
                value=""
            >

            <input
                type="hidden"
                name="action"
                value="reject"
            >


            <div class="modal-body">

                <label for="owner_message">
                    Reason for rejection
                </label>

                <textarea
                    name="owner_message"
                    id="owner_message"
                    rows="5"
                    placeholder="Enter a message for the borrower..."
                ></textarea>

                <p class="modal-note">
                    This message will be sent to the borrower
                    by email.
                </p>

            </div>


            <div class="modal-footer">

                <button
                    type="button"
                    class="cancel-modal-btn"
                    onclick="closeRejectModal()"
                >
                    Cancel
                </button>


                <button
                    type="submit"
                    class="confirm-reject-btn"
                >

                    <i class="fa-solid fa-xmark"></i>

                    Reject Request

                </button>

            </div>

        </form>

    </div>

</div>


</body>
</html>