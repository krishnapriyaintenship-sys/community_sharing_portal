<?php
session_start();

require_once "includes/db.php";

/* =====================================================
   LOGIN CHECK
===================================================== */

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$owner_id = (int)$_SESSION['user_id'];

$message = "";
$message_type = "";


/* =====================================================
   ACCEPT REQUEST
===================================================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['accept_request'])) {

    $request_id = (int)($_POST['request_id'] ?? 0);

    $sql = "UPDATE borrow_requests
            SET status = 'Approved',
                approved_date = NOW()
            WHERE request_id = ?
              AND owner_id = ?
              AND status = 'Pending'";

    $stmt = $conn->prepare($sql);

    if ($stmt) {

        $stmt->bind_param(
            "ii",
            $request_id,
            $owner_id
        );

        if ($stmt->execute() && $stmt->affected_rows > 0) {

            $message = "Request accepted successfully.";
            $message_type = "success";

        } else {

            $message = "Unable to accept this request.";
            $message_type = "error";
        }

        $stmt->close();

    } else {

        $message = "Database error.";
        $message_type = "error";
    }
}


/* =====================================================
   REJECT REQUEST
===================================================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['reject_request'])) {

    $request_id = (int)($_POST['request_id'] ?? 0);

    $owner_message = trim(
        $_POST['owner_message'] ?? ''
    );

    if ($owner_message === '') {
        $owner_message = "Request rejected by item owner.";
    }

    $sql = "UPDATE borrow_requests
            SET status = 'Rejected',
                owner_message = ?
            WHERE request_id = ?
              AND owner_id = ?
              AND status = 'Pending'";

    $stmt = $conn->prepare($sql);

    if ($stmt) {

        $stmt->bind_param(
            "sii",
            $owner_message,
            $request_id,
            $owner_id
        );

        if ($stmt->execute() && $stmt->affected_rows > 0) {

            $message = "Request rejected successfully.";
            $message_type = "success";

        } else {

            $message = "Unable to reject this request.";
            $message_type = "error";
        }

        $stmt->close();

    } else {

        $message = "Database error.";
        $message_type = "error";
    }
}


/* =====================================================
   MARK AS RETURNED
===================================================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['return_request'])) {

    $request_id = (int)($_POST['request_id'] ?? 0);

    $sql = "UPDATE borrow_requests
            SET status = 'Returned',
                returned_date = NOW()
            WHERE request_id = ?
              AND owner_id = ?
              AND status = 'Approved'";

    $stmt = $conn->prepare($sql);

    if ($stmt) {

        $stmt->bind_param(
            "ii",
            $request_id,
            $owner_id
        );

        if ($stmt->execute() && $stmt->affected_rows > 0) {

            $message = "Item marked as returned.";
            $message_type = "success";

        } else {

            $message = "Unable to mark item as returned.";
            $message_type = "error";
        }

        $stmt->close();

    } else {

        $message = "Database error.";
        $message_type = "error";
    }
}


/* =====================================================
   GET REQUESTS
===================================================== */

$sql = "SELECT
            br.*,
            u.full_name AS borrower_name,
            u.email AS borrower_email,
            u.phone AS borrower_phone
        FROM borrow_requests br
        LEFT JOIN users u
            ON br.borrower_id = u.user_id
        WHERE br.owner_id = ?
        ORDER BY br.request_date DESC";

$stmt = $conn->prepare($sql);

$requests = [];

if ($stmt) {

    $stmt->bind_param(
        "i",
        $owner_id
    );

    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }

    $stmt->close();
}


/* =====================================================
   COUNTS
===================================================== */

$total = count($requests);

$pending = 0;
$approved = 0;
$rejected = 0;
$returned = 0;

foreach ($requests as $request) {

    if ($request['status'] === 'Pending') {
        $pending++;
    }

    if ($request['status'] === 'Approved') {
        $approved++;
    }

    if ($request['status'] === 'Rejected') {
        $rejected++;
    }

    if ($request['status'] === 'Returned') {
        $returned++;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Manage Requests - CampusShare</title>


<style>

/* =====================================================
   BASIC
===================================================== */

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, Helvetica, sans-serif;
    background: #f4f6f9;
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
}

.logo {
    text-align: center;
    margin-bottom: 35px;
}

.logo-box {
    width: 55px;
    height: 55px;

    margin: auto;

    display: flex;
    align-items: center;
    justify-content: center;

    background: #2563eb;

    border-radius: 12px;

    font-size: 20px;
    font-weight: bold;
}

.logo h2 {
    margin: 12px 0 4px;
    font-size: 20px;
}

.logo p {
    margin: 0;
    font-size: 12px;
    color: #9ca3af;
}

.menu-title {
    margin: 25px 10px 10px;

    font-size: 11px;

    color: #9ca3af;

    font-weight: bold;
}

.sidebar a {
    display: block;

    padding: 13px 14px;

    margin-bottom: 5px;

    color: #d1d5db;

    text-decoration: none;

    border-radius: 8px;

    font-size: 14px;
}

.sidebar a:hover {
    background: #1f2937;
    color: white;
}

.sidebar a.active {
    background: #2563eb;
    color: white;
}


/* =====================================================
   MAIN
===================================================== */

.main {
    margin-left: 240px;

    padding: 35px;
}


/* =====================================================
   HEADER
===================================================== */

.page-title h1 {
    margin: 0 0 8px;

    font-size: 30px;
}

.page-title p {
    margin: 0 0 25px;

    color: #6b7280;
}


/* =====================================================
   ALERT
===================================================== */

.alert {
    padding: 14px 18px;

    border-radius: 8px;

    margin-bottom: 25px;

    font-weight: bold;
}

.alert.success {
    background: #dcfce7;
    color: #166534;
}

.alert.error {
    background: #fee2e2;
    color: #991b1b;
}


/* =====================================================
   STAT CARDS
===================================================== */

.stats {
    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 18px;

    margin-bottom: 30px;
}

.stat {
    background: white;

    padding: 20px;

    border-radius: 12px;

    box-shadow:
        0 2px 8px rgba(0,0,0,.06);
}

.stat-label {
    display: block;

    color: #6b7280;

    font-size: 13px;

    margin-bottom: 8px;
}

.stat-number {
    font-size: 28px;

    font-weight: bold;
}


/* =====================================================
   REQUEST CONTAINER
===================================================== */

.request-container {
    background: white;

    padding: 25px;

    border-radius: 14px;

    box-shadow:
        0 2px 10px rgba(0,0,0,.06);
}

.request-container h2 {
    margin-top: 0;
}


/* =====================================================
   REQUEST CARD
===================================================== */

.request-card {
    border: 1px solid #e5e7eb;

    border-radius: 12px;

    padding: 22px;

    margin-top: 20px;

    background: white;
}

.request-card:hover {
    box-shadow:
        0 5px 18px rgba(0,0,0,.08);
}


/* =====================================================
   CARD TOP
===================================================== */

.request-top {
    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 18px;
}

.request-id {
    font-weight: bold;

    font-size: 16px;
}


/* =====================================================
   STATUS
===================================================== */

.status {
    display: inline-block;

    padding: 7px 14px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: bold;
}

.pending {
    background: #fff7ed;
    color: #c2410c;
}

.approved {
    background: #dcfce7;
    color: #166534;
}

.rejected {
    background: #fee2e2;
    color: #991b1b;
}

.returned {
    background: #dbeafe;
    color: #1d4ed8;
}


/* =====================================================
   DETAILS
===================================================== */

.request-details {
    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 15px;

    margin-bottom: 20px;
}

.detail {
    background: #f8fafc;

    padding: 14px;

    border-radius: 8px;
}

.detail-label {
    display: block;

    font-size: 11px;

    color: #6b7280;

    text-transform: uppercase;

    margin-bottom: 5px;
}

.detail-value {
    font-size: 14px;

    font-weight: 600;
}


/* =====================================================
   IMPORTANT BUTTON AREA
===================================================== */

.request-actions {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 15px;

    padding-top: 18px;

    border-top: 1px solid #e5e7eb;

}


/* =====================================================
   VIEW BORROWER BUTTON
===================================================== */

.view-btn {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    padding: 12px 18px;

    min-height: 44px;

    background: #eff6ff;

    border: 1px solid #93c5fd;

    color: #1d4ed8;

    border-radius: 8px;

    text-decoration: none;

    font-size: 13px;

    font-weight: bold;

    white-space: nowrap;
}

.view-btn:hover {
    background: #dbeafe;
}


/* =====================================================
   BUTTON GROUP
===================================================== */

.button-group {

    display: flex;

    gap: 10px;

    align-items: center;
}


/* =====================================================
   ACTION BUTTON
===================================================== */

.action-button {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    min-height: 44px;

    padding: 12px 20px;

    border: none;

    border-radius: 8px;

    color: white !important;

    font-size: 13px;

    font-weight: bold;

    cursor: pointer;

    white-space: nowrap;

    text-decoration: none;
}


/* =====================================================
   ACCEPT
===================================================== */

.accept-button {

    background: #16a34a !important;

    color: white !important;
}

.accept-button:hover {

    background: #15803d !important;
}


/* =====================================================
   REJECT
===================================================== */

.reject-button {

    background: #dc2626 !important;

    color: white !important;
}

.reject-button:hover {

    background: #b91c1c !important;
}


/* =====================================================
   RETURN
===================================================== */

.return-button {

    background: #2563eb !important;

    color: white !important;
}

.return-button:hover {

    background: #1d4ed8 !important;
}


/* =====================================================
   FINISHED STATUS
===================================================== */

.finished {

    padding: 12px 16px;

    border-radius: 8px;

    font-size: 13px;

    font-weight: bold;
}

.finished-rejected {

    background: #fee2e2;

    color: #991b1b;
}

.finished-returned {

    background: #dbeafe;

    color: #1d4ed8;
}


/* =====================================================
   EMPTY
===================================================== */

.empty {

    text-align: center;

    padding: 60px 20px;

    color: #6b7280;
}


/* =====================================================
   MODAL
===================================================== */

.modal {

    display: none;

    position: fixed;

    inset: 0;

    background: rgba(0,0,0,.55);

    z-index: 9999;

    align-items: center;

    justify-content: center;
}

.modal-box {

    width: 90%;

    max-width: 450px;

    background: white;

    border-radius: 12px;

    padding: 25px;
}

.modal-box h3 {
    margin-top: 0;
}

.modal-box textarea {

    width: 100%;

    min-height: 110px;

    padding: 12px;

    border: 1px solid #d1d5db;

    border-radius: 8px;

    resize: vertical;

    outline: none;

    margin: 10px 0 18px;
}

.modal-buttons {

    display: flex;

    justify-content: flex-end;

    gap: 10px;
}

.cancel-button {

    padding: 11px 18px;

    border: 1px solid #d1d5db;

    background: white;

    border-radius: 8px;

    cursor: pointer;
}

.confirm-reject {

    padding: 11px 18px;

    border: none;

    background: #dc2626;

    color: white;

    border-radius: 8px;

    font-weight: bold;

    cursor: pointer;
}


/* =====================================================
   MOBILE
===================================================== */

@media (max-width: 850px) {

    .sidebar {
        position: relative;

        width: 100%;

        height: auto;
    }

    .main {
        margin-left: 0;

        padding: 20px;
    }

    .stats {
        grid-template-columns:
            repeat(2, 1fr);
    }

    .request-actions {
        flex-direction: column;

        align-items: stretch;
    }

    .view-btn {
        width: 100%;
    }

    .button-group {
        width: 100%;
    }

    .action-button {
        flex: 1;
    }
}


@media (max-width: 500px) {

    .stats {
        grid-template-columns: 1fr;
    }

    .request-details {
        grid-template-columns: 1fr;
    }

    .button-group {
        flex-direction: column;
    }

    .action-button {
        width: 100%;
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

        <div class="logo-box">
            CS
        </div>

        <h2>
            CampusShare
        </h2>

        <p>
            Community Sharing
        </p>

    </div>


    <div class="menu-title">
        MAIN MENU
    </div>


    <a href="dashboard.php">
        Dashboard
    </a>

    <a href="add_item.php">
        Add Item
    </a>

    <a href="my_items.php">
        My Items
    </a>

    <a href="browse_items.php">
        Browse Items
    </a>

    <a href="manage_requests.php"
       class="active">

        Manage Requests

    </a>


    <div class="menu-title">
        ACCOUNT
    </div>


    <a href="notifications.php">
        Notifications
    </a>

    <a href="profile.php">
        Profile
    </a>

    <a href="logout.php">
        Logout
    </a>

</div>


<!-- =====================================================
     MAIN CONTENT
===================================================== -->

<div class="main">


    <div class="page-title">

        <h1>
            Manage Requests
        </h1>

        <p>
            Accept or reject borrowing requests from other users.
        </p>

    </div>


    <!-- MESSAGE -->

    <?php if ($message !== ""): ?>

        <div class="alert <?php echo htmlspecialchars($message_type); ?>">

            <?php echo htmlspecialchars($message); ?>

        </div>

    <?php endif; ?>


    <!-- =================================================
         STATISTICS
    ================================================== -->

    <div class="stats">

        <div class="stat">

            <span class="stat-label">
                Total Requests
            </span>

            <span class="stat-number">
                <?php echo $total; ?>
            </span>

        </div>


        <div class="stat">

            <span class="stat-label">
                Pending
            </span>

            <span class="stat-number">
                <?php echo $pending; ?>
            </span>

        </div>


        <div class="stat">

            <span class="stat-label">
                Approved
            </span>

            <span class="stat-number">
                <?php echo $approved; ?>
            </span>

        </div>


        <div class="stat">

            <span class="stat-label">
                Returned
            </span>

            <span class="stat-number">
                <?php echo $returned; ?>
            </span>

        </div>

    </div>


    <!-- =================================================
         REQUESTS
    ================================================== -->

    <div class="request-container">

        <h2>
            Borrow Requests
        </h2>


        <?php if (empty($requests)): ?>

            <div class="empty">

                <h3>
                    No Requests Found
                </h3>

                <p>
                    No one has requested your items yet.
                </p>

            </div>

        <?php else: ?>


            <?php foreach ($requests as $request): ?>


                <?php

                $status =
                    $request['status'];

                $status_class =
                    strtolower($status);

                ?>


                <div class="request-card">


                    <!-- =================================
                         TOP
                    ================================== -->

                    <div class="request-top">

                        <div class="request-id">

                            Request #
                            <?php
                            echo (int)$request['request_id'];
                            ?>

                        </div>


                        <span class="status <?php echo $status_class; ?>">

                            <?php
                            echo htmlspecialchars($status);
                            ?>

                        </span>

                    </div>


                    <!-- =================================
                         DETAILS
                    ================================== -->

                    <div class="request-details">


                        <div class="detail">

                            <span class="detail-label">
                                Borrower
                            </span>

                            <span class="detail-value">

                                <?php
                                echo htmlspecialchars(
                                    $request['borrower_name']
                                    ?? 'Unknown'
                                );
                                ?>

                            </span>

                        </div>


                        <div class="detail">

                            <span class="detail-label">
                                Email
                            </span>

                            <span class="detail-value">

                                <?php
                                echo htmlspecialchars(
                                    $request['borrower_email']
                                    ?? 'Not available'
                                );
                                ?>

                            </span>

                        </div>


                        <div class="detail">

                            <span class="detail-label">
                                Item ID
                            </span>

                            <span class="detail-value">

                                #<?php
                                echo (int)$request['item_id'];
                                ?>

                            </span>

                        </div>


                        <div class="detail">

                            <span class="detail-label">
                                Request Date
                            </span>

                            <span class="detail-value">

                                <?php

                                if (!empty(
                                    $request['request_date']
                                )) {

                                    echo date(
                                        "d M Y",
                                        strtotime(
                                            $request['request_date']
                                        )
                                    );

                                } else {

                                    echo "—";

                                }

                                ?>

                            </span>

                        </div>


                    </div>


                    <!-- =================================
                         BUTTON AREA
                    ================================== -->

                    <div class="request-actions">


                        <!-- VIEW BORROWER -->

                        <a
                            href="borrower_details.php?request_id=<?php echo (int)$request['request_id']; ?>"
                            class="view-btn"
                        >

                            👤 View Borrower

                        </a>


                        <!-- =================================
                             PENDING BUTTONS
                        ================================== -->

                        <?php if ($status === 'Pending'): ?>


                            <div class="button-group">


                                <!-- ACCEPT -->

                                <form
                                    method="POST"
                                    style="margin:0;"
                                >

                                    <input
                                        type="hidden"
                                        name="request_id"
                                        value="<?php
                                        echo (int)$request['request_id'];
                                        ?>"
                                    >


                                    <button
                                        type="submit"
                                        name="accept_request"
                                        class="action-button accept-button"
                                        onclick="return confirm('Do you want to ACCEPT this borrow request?');"
                                    >

                                        ✓ Accept

                                    </button>

                                </form>


                                <!-- REJECT -->

                                <button
                                    type="button"
                                    class="action-button reject-button"
                                    onclick="openRejectModal(<?php echo (int)$request['request_id']; ?>)"
                                >

                                    ✕ Reject

                                </button>


                            </div>


                        <!-- =================================
                             APPROVED
                        ================================== -->

                        <?php elseif ($status === 'Approved'): ?>


                            <div class="button-group">


                                <form
                                    method="POST"
                                    style="margin:0;"
                                >

                                    <input
                                        type="hidden"
                                        name="request_id"
                                        value="<?php
                                        echo (int)$request['request_id'];
                                        ?>"
                                    >


                                    <button
                                        type="submit"
                                        name="return_request"
                                        class="action-button return-button"
                                        onclick="return confirm('Has the borrower returned this item?');"
                                    >

                                        ↩ Mark as Returned

                                    </button>

                                </form>


                            </div>


                        <!-- =================================
                             REJECTED
                        ================================== -->

                        <?php elseif ($status === 'Rejected'): ?>


                            <div class="finished finished-rejected">

                                ✕ Request Rejected

                            </div>


                        <!-- =================================
                             RETURNED
                        ================================== -->

                        <?php elseif ($status === 'Returned'): ?>


                            <div class="finished finished-returned">

                                ✓ Item Returned

                            </div>


                        <?php endif; ?>


                    </div>


                </div>


            <?php endforeach; ?>


        <?php endif; ?>


    </div>


</div>


<!-- =====================================================
     REJECT MODAL
===================================================== -->

<div
    class="modal"
    id="rejectModal"
>


    <div class="modal-box">


        <h3>
            Reject Request
        </h3>


        <p>
            Enter a reason for rejecting this request.
        </p>


        <form method="POST">


            <input
                type="hidden"
                name="request_id"
                id="rejectRequestId"
            >


            <textarea
                name="owner_message"
                placeholder="Enter rejection reason..."
                required
            ></textarea>


            <div class="modal-buttons">


                <button
                    type="button"
                    class="cancel-button"
                    onclick="closeRejectModal()"
                >

                    Cancel

                </button>


                <button
                    type="submit"
                    name="reject_request"
                    class="confirm-reject"
                >

                    ✕ Reject Request

                </button>


            </div>


        </form>


    </div>

</div>


<script>

/* =====================================================
   REJECT MODAL
===================================================== */

function openRejectModal(requestId) {

    document.getElementById(
        "rejectRequestId"
    ).value = requestId;

    document.getElementById(
        "rejectModal"
    ).style.display = "flex";
}


function closeRejectModal() {

    document.getElementById(
        "rejectModal"
    ).style.display = "none";
}


window.addEventListener(
    "click",
    function(event) {

        const modal =
            document.getElementById("rejectModal");

        if (event.target === modal) {

            closeRejectModal();

        }

    }
);

</script>


</body>

</html>