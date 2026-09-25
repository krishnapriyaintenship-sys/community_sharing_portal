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
   DATABASE + EMAIL
===================================================== */

require_once "includes/db.php";
require_once "includes/notification_mail.php";


/* =====================================================
   CURRENT USER
===================================================== */

$user_id = (int) $_SESSION['user_id'];

$error = "";


/* =====================================================
   GET ITEM ID
===================================================== */

$item_id = isset($_GET['item_id'])
    ? (int) $_GET['item_id']
    : 0;


/* =====================================================
   PROCESS FORM SUBMISSION
===================================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $item_id = isset($_POST['item_id'])
        ? (int) $_POST['item_id']
        : 0;

    $borrow_date = trim($_POST['borrow_date'] ?? '');
    $expected_return_date = trim($_POST['expected_return_date'] ?? '');


    /* =================================================
       BASIC VALIDATION
    ================================================= */

    if ($item_id <= 0) {

        $error = "Invalid item.";

    } elseif (
        empty($borrow_date) ||
        empty($expected_return_date)
    ) {

        $error = "Please select both borrow date and expected return date.";

    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $borrow_date)) {

        $error = "Invalid borrow date.";

    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $expected_return_date)) {

        $error = "Invalid expected return date.";

    } elseif ($borrow_date < date('Y-m-d')) {

        $error = "Borrow date cannot be in the past.";

    } elseif ($expected_return_date <= $borrow_date) {

        $error = "Expected return date must be after the borrow date.";

    } else {


        /* =================================================
           GET ITEM + OWNER DETAILS
        ================================================= */

        $stmt = $conn->prepare("
            SELECT
                i.item_id,
                i.item_name,
                i.availability,
                i.user_id AS owner_id,
                u.full_name AS owner_name,
                u.email AS owner_email
            FROM items i
            INNER JOIN users u
                ON i.user_id = u.user_id
            WHERE i.item_id = ?
            LIMIT 1
        ");


        if (!$stmt) {

            $error = "Database error. Please try again.";

        } else {

            $stmt->bind_param("i", $item_id);
            $stmt->execute();

            $result = $stmt->get_result();
            $item = $result->fetch_assoc();

            $stmt->close();


            /* =================================================
               ITEM NOT FOUND
            ================================================= */

            if (!$item) {

                $error = "Item not found.";

            }


            /* =================================================
               CANNOT BORROW OWN ITEM
            ================================================= */

            elseif ((int)$item['owner_id'] === $user_id) {

                $error = "You cannot borrow your own item.";

            }


            /* =================================================
               ITEM NOT AVAILABLE
            ================================================= */

            elseif ($item['availability'] !== 'Available') {

                $error = "This item is currently not available.";

            }


            /* =================================================
               CHECK ACTIVE REQUEST
            ================================================= */

            else {

                /*
                    These statuses prevent another request:

                    Pending
                    Approved
                    Item Received
                    Return Requested

                    Rejected and Returned allow a new request.
                */

                $stmt = $conn->prepare("
                    SELECT
                        request_id,
                        status
                    FROM borrow_requests
                    WHERE item_id = ?
                      AND borrower_id = ?
                      AND status IN (
                          'Pending',
                          'Approved',
                          'Item Received',
                          'Return Requested'
                      )
                    ORDER BY request_id DESC
                    LIMIT 1
                ");


                if (!$stmt) {

                    $error = "Database error. Please try again.";

                } else {

                    $stmt->bind_param(
                        "ii",
                        $item_id,
                        $user_id
                    );

                    $stmt->execute();

                    $existing_result = $stmt->get_result();

                    $existing_request =
                        $existing_result->fetch_assoc();

                    $stmt->close();


                    /* =================================================
                       ACTIVE REQUEST EXISTS
                    ================================================= */

                    if ($existing_request) {

                        $status = $existing_request['status'];


                        if ($status === 'Pending') {

                            $error =
                                "You already have a pending request for this item.";

                        } elseif ($status === 'Approved') {

                            $error =
                                "Your request for this item has already been approved.";

                        } elseif ($status === 'Item Received') {

                            $error =
                                "You have already received this item.";

                        } elseif ($status === 'Return Requested') {

                            $error =
                                "A return request is already active for this item.";

                        } else {

                            $error =
                                "You already have an active request for this item.";
                        }

                    }


                    /* =================================================
                       NO ACTIVE REQUEST
                       CREATE NEW REQUEST
                    ================================================= */

                    else {

                        /* =================================================
                           GET BORROWER DETAILS
                        ================================================= */

                        $stmt = $conn->prepare("
                            SELECT
                                full_name,
                                email
                            FROM users
                            WHERE user_id = ?
                            LIMIT 1
                        ");


                        if (!$stmt) {

                            $error =
                                "Unable to get your account details.";

                        } else {

                            $stmt->bind_param(
                                "i",
                                $user_id
                            );

                            $stmt->execute();

                            $borrower_result =
                                $stmt->get_result();

                            $borrower =
                                $borrower_result->fetch_assoc();

                            $stmt->close();


                            if (!$borrower) {

                                $error =
                                    "Borrower details not found.";

                            } else {


                                /* =================================================
                                   INSERT BORROW REQUEST
                                ================================================= */

                                $status = "Pending";

                                $owner_id =
                                    (int)$item['owner_id'];


                                $stmt = $conn->prepare("
                                    INSERT INTO borrow_requests
                                    (
                                        item_id,
                                        borrower_id,
                                        owner_id,
                                        borrow_date,
                                        expected_return_date,
                                        status,
                                        request_date
                                    )
                                    VALUES
                                    (
                                        ?,
                                        ?,
                                        ?,
                                        ?,
                                        ?,
                                        ?,
                                        NOW()
                                    )
                                ");


                                if (!$stmt) {

                                    $error =
                                        "Unable to create borrow request.";

                                } else {

                                    $stmt->bind_param(
                                        "iiisss",
                                        $item_id,
                                        $user_id,
                                        $owner_id,
                                        $borrow_date,
                                        $expected_return_date,
                                        $status
                                    );


                                    /* =================================================
                                       EXECUTE REQUEST
                                    ================================================= */

                                    if ($stmt->execute()) {

                                        $stmt->close();


                                        /* =================================================
                                           SEND EMAIL TO OWNER
                                        ================================================= */

                                        sendNewBorrowRequestEmail(
                                            $item['owner_email'],
                                            $item['owner_name'],
                                            $borrower['full_name'],
                                            $item['item_name'],
                                            $borrow_date,
                                            $expected_return_date
                                        );


                                        /* =================================================
                                           REDIRECT
                                        ================================================= */

                                        header(
                                            "Location: my_borrow_requests.php?success=" .
                                            urlencode(
                                                "Borrow request sent successfully. The owner has been notified."
                                            )
                                        );

                                        exit();

                                    } else {

                                        $error =
                                            "Failed to send borrow request.";

                                        $stmt->close();
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}


/* =====================================================
   GET ITEM DETAILS FOR DISPLAY
===================================================== */

$display_item = null;


if ($item_id > 0) {

    $stmt = $conn->prepare("
        SELECT
            i.*,
            u.full_name AS owner_name
        FROM items i
        INNER JOIN users u
            ON i.user_id = u.user_id
        WHERE i.item_id = ?
        LIMIT 1
    ");


    if ($stmt) {

        $stmt->bind_param(
            "i",
            $item_id
        );

        $stmt->execute();

        $result = $stmt->get_result();

        $display_item =
            $result->fetch_assoc();

        $stmt->close();
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

    <title>Borrow Request | CampusShare</title>


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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f7fb;
            color: #1f2937;
            min-height: 100vh;
        }


        .container {
            width: 90%;
            max-width: 800px;
            margin: 45px auto;
        }


        .card {
            background: white;
            padding: 30px;
            border-radius: 15px;

            box-shadow:
                0 5px 20px rgba(0, 0, 0, 0.08);
        }


        h1 {
            text-align: center;
            margin-bottom: 25px;
            color: #111827;
        }


        .item-box {
            background: #f8fafc;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;

            border: 1px solid #e5e7eb;
        }


        .item-box h2 {
            color: #111827;
            margin-bottom: 12px;
        }


        .item-box p {
            margin: 8px 0;
            color: #4b5563;
            line-height: 1.5;
        }


        .available {
            color: #15803d;
            font-weight: 600;
        }


        .borrowed {
            color: #dc2626;
            font-weight: 600;
        }


        .form-group {
            margin-bottom: 20px;
        }


        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
        }


        input[type="date"] {
            width: 100%;
            padding: 12px;

            border: 1px solid #d1d5db;
            border-radius: 8px;

            font-size: 15px;
            outline: none;
        }


        input[type="date"]:focus {
            border-color: #2563eb;

            box-shadow:
                0 0 0 3px rgba(37, 99, 235, 0.10);
        }


        .btn {
            width: 100%;
            padding: 13px;

            border: none;
            border-radius: 8px;

            background: #2563eb;
            color: white;

            font-size: 16px;
            font-weight: 600;

            cursor: pointer;
            transition: 0.2s;
        }


        .btn:hover {
            background: #1d4ed8;
        }


        .back-btn {
            display: block;

            text-align: center;

            margin-top: 15px;

            text-decoration: none;

            color: #4b5563;
            font-size: 14px;
        }


        .back-btn:hover {
            color: #2563eb;
        }


        .error {
            background: #fee2e2;
            color: #991b1b;

            border: 1px solid #fca5a5;

            padding: 13px 15px;

            border-radius: 8px;

            margin-bottom: 20px;

            line-height: 1.5;
        }


        .info {
            background: #eff6ff;
            color: #1e40af;

            border: 1px solid #bfdbfe;

            padding: 14px 16px;

            border-radius: 8px;

            margin-bottom: 20px;

            line-height: 1.6;

            font-size: 14px;
        }


        .date-note {
            display: block;
            margin-top: 6px;
            font-size: 12px;
            color: #6b7280;
        }


        @media (max-width: 600px) {

            .container {
                width: 94%;
                margin: 20px auto;
            }


            .card {
                padding: 20px;
            }


            h1 {
                font-size: 24px;
            }
        }

    </style>

</head>


<body>


<div class="container">

    <div class="card">


        <!-- PAGE TITLE -->

        <h1>
            <i class="fa-solid fa-hand-holding"></i>
            Borrow Request
        </h1>


        <!-- ERROR MESSAGE -->

        <?php if (!empty($error)): ?>

            <div class="error">

                <i class="fa-solid fa-circle-exclamation"></i>

                <?php
                echo htmlspecialchars($error);
                ?>

            </div>

        <?php endif; ?>


        <?php if ($display_item): ?>


            <!-- ITEM DETAILS -->

            <div class="item-box">

                <h2>
                    <?php
                    echo htmlspecialchars(
                        $display_item['item_name']
                    );
                    ?>
                </h2>


                <p>

                    <strong>Owner:</strong>

                    <?php
                    echo htmlspecialchars(
                        $display_item['owner_name']
                    );
                    ?>

                </p>


                <p>

                    <strong>Condition:</strong>

                    <?php
                    echo htmlspecialchars(
                        $display_item['item_condition']
                        ?? 'Not specified'
                    );
                    ?>

                </p>


                <p>

                    <strong>Location:</strong>

                    <?php
                    echo htmlspecialchars(
                        $display_item['location']
                        ?? 'Not specified'
                    );
                    ?>

                </p>


                <p>

                    <strong>Availability:</strong>

                    <?php if (
                        $display_item['availability'] === 'Available'
                    ): ?>

                        <span class="available">
                            Available
                        </span>

                    <?php else: ?>

                        <span class="borrowed">
                            Borrowed
                        </span>

                    <?php endif; ?>

                </p>

            </div>


            <?php if (
                $display_item['availability'] === 'Available'
                &&
                (int)$display_item['user_id'] !== $user_id
            ): ?>


                <!-- INFORMATION -->

                <div class="info">

                    <i class="fa-solid fa-circle-info"></i>

                    After submitting this request,
                    the item owner will receive an email notification.

                    <br><br>

                    The owner must accept the request before
                    you can receive the item.

                </div>


                <!-- BORROW FORM -->

                <form
                    method="POST"
                    action="borrow_request.php"
                >


                    <input
                        type="hidden"
                        name="item_id"
                        value="<?php echo (int)$item_id; ?>"
                    >


                    <!-- BORROW DATE -->

                    <div class="form-group">

                        <label for="borrow_date">
                            Borrow Date
                        </label>

                        <input
                            type="date"
                            id="borrow_date"
                            name="borrow_date"
                            required
                        >

                        <span class="date-note">
                            Borrow date must be today or a future date.
                        </span>

                    </div>


                    <!-- RETURN DATE -->

                    <div class="form-group">

                        <label for="expected_return_date">
                            Expected Return Date
                        </label>

                        <input
                            type="date"
                            id="expected_return_date"
                            name="expected_return_date"
                            required
                        >

                        <span class="date-note">
                            Return date must be after the borrow date.
                        </span>

                    </div>


                    <!-- SUBMIT -->

                    <button
                        type="submit"
                        name="send_request"
                        class="btn"
                    >

                        <i class="fa-solid fa-paper-plane"></i>

                        Send Borrow Request

                    </button>

                </form>


            <?php elseif (
                (int)$display_item['user_id'] === $user_id
            ): ?>


                <!-- OWN ITEM -->

                <div class="error">

                    <i class="fa-solid fa-user"></i>

                    You cannot borrow your own item.

                </div>


            <?php else: ?>


                <!-- BORROWED ITEM -->

                <div class="error">

                    <i class="fa-solid fa-ban"></i>

                    This item is currently borrowed
                    and cannot be requested.

                </div>

            <?php endif; ?>


        <?php else: ?>


            <!-- ITEM NOT FOUND -->

            <div class="error">

                <i class="fa-solid fa-circle-exclamation"></i>

                Item not found.

            </div>

        <?php endif; ?>


        <!-- BACK BUTTON -->

        <a
            href="browse_items.php"
            class="back-btn"
        >

            ← Back to Browse Items

        </a>


    </div>

</div>


<script>

/* =====================================================
   GET TODAY'S DATE
===================================================== */

const today =
    new Date()
    .toISOString()
    .split('T')[0];


/* =====================================================
   DATE INPUTS
===================================================== */

const borrowDate =
    document.getElementById("borrow_date");

const returnDate =
    document.getElementById("expected_return_date");


/* =====================================================
   BORROW DATE
===================================================== */

if (borrowDate) {

    borrowDate.min = today;

    borrowDate.value = today;
}


/* =====================================================
   RETURN DATE
===================================================== */

if (returnDate) {

    returnDate.min = today;
}


/* =====================================================
   KEEP RETURN DATE AFTER BORROW DATE
===================================================== */

if (borrowDate && returnDate) {

    borrowDate.addEventListener(
        "change",
        function () {

            /*
             * Return date must be AFTER
             * the selected borrow date.
             *
             * Therefore minimum return date
             * is borrow date + 1 day.
             */

            const selectedDate =
                new Date(this.value);

            selectedDate.setDate(
                selectedDate.getDate() + 1
            );


            const year =
                selectedDate.getFullYear();

            const month =
                String(
                    selectedDate.getMonth() + 1
                ).padStart(2, '0');

            const day =
                String(
                    selectedDate.getDate()
                ).padStart(2, '0');


            const minimumReturnDate =
                `${year}-${month}-${day}`;


            returnDate.min =
                minimumReturnDate;


            /*
             * Clear invalid return date.
             */

            if (
                returnDate.value &&
                returnDate.value < minimumReturnDate
            ) {

                returnDate.value = "";
            }

        }
    );


    /*
     * Initial setting
     */

    if (borrowDate.value) {

        const selectedDate =
            new Date(borrowDate.value);

        selectedDate.setDate(
            selectedDate.getDate() + 1
        );


        const year =
            selectedDate.getFullYear();

        const month =
            String(
                selectedDate.getMonth() + 1
            ).padStart(2, '0');

        const day =
            String(
                selectedDate.getDate()
            ).padStart(2, '0');


        returnDate.min =
            `${year}-${month}-${day}`;
    }

}

</script>


</body>
</html>