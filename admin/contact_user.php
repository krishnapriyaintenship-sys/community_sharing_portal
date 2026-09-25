<?php

session_start();

/* =========================================================
   ADMIN LOGIN CHECK
   ========================================================= */

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}


/* =========================================================
   DATABASE CONNECTION
   ========================================================= */

require_once "../includes/db.php";
require_once "../includes/mail.php";


/* =========================================================
   GET REQUEST ID
   ========================================================= */

$request_id = intval($_GET['id'] ?? 0);

if ($request_id <= 0) {
    header("Location: overdue_items.php?error=Invalid request.");
    exit();
}


/* =========================================================
   GET BORROWER DETAILS
   ========================================================= */

$sql = "
    SELECT
        br.request_id,
        br.borrow_date,
        br.expected_return_date,
        br.status,

        u.user_id,
        u.full_name,
        u.email,

        i.item_name

    FROM borrow_requests br

    INNER JOIN users u
        ON br.borrower_id = u.user_id

    INNER JOIN items i
        ON br.item_id = i.item_id

    WHERE br.request_id = ?
";


$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $request_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/* =========================================================
   CHECK RECORD
   ========================================================= */

if (!$user) {
    header("Location: overdue_items.php?error=Overdue record not found.");
    exit();
}


/* =========================================================
   CHECK ACTIVE BORROWING
   =========================================================
   Contact User is opened from the admin overdue list. We only need to
   make sure the request is still an active borrowing. We do NOT reject
   the contact page merely because the due date is today or has changed,
   because the admin may still want to contact the borrower.
   ========================================================= */

$active_statuses = [
    'Approved',
    'Item Received',
    'Return Requested'
];

if (!in_array($user['status'], $active_statuses, true)) {
    header(
        "Location: overdue_items.php?error=" .
        urlencode("This borrowing is no longer active.")
    );
    exit();
}


/* =========================================================
   CSRF TOKEN
   ========================================================= */

if (empty($_SESSION['contact_user_token'])) {
    $_SESSION['contact_user_token'] = bin2hex(random_bytes(32));
}

$contact_user_token = $_SESSION['contact_user_token'];


/* =========================================================
   SEND MESSAGE
   ========================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (
        !isset($_POST['contact_user_token']) ||
        !hash_equals(
            $_SESSION['contact_user_token'],
            $_POST['contact_user_token']
        )
    ) {
        $error = "Invalid request. Please try again.";
    } else {

    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');


    /* =====================================================
       VALIDATION
       ===================================================== */

    if ($subject === '' || $message === '') {

        $error = "Please enter both subject and message.";

    } elseif (strlen($subject) > 150) {

        $error = "Subject cannot exceed 150 characters.";

    } elseif (strlen($message) > 3000) {

        $error = "Message cannot exceed 3000 characters.";

    } else {


        /* =================================================
           CREATE EMAIL
           ================================================= */

        $email_body = "

        <div style='font-family:Arial,sans-serif;max-width:650px;margin:auto;'>

            <h2 style='color:#dc2626;'>
                Overdue Item Notification
            </h2>

            <p>Hello <strong>"
                . htmlspecialchars($user['full_name']) .
            "</strong>,</p>

            <p>
                This is a message from the administrator of the
                Community Item Sharing Portal.
            </p>

            <div style='background:#f3f4f6;padding:15px;border-radius:8px;'>

                <p>
                    <strong>Item:</strong>
                    "
                    . htmlspecialchars($user['item_name']) .
                    "
                </p>

                <p>
                    <strong>Expected Return Date:</strong>
                    "
                    . htmlspecialchars($user['expected_return_date']) .
                    "
                </p>

            </div>

            <h3>Admin Message</h3>

            <p>
                "
                . nl2br(htmlspecialchars($message)) .
                "
            </p>

            <p>
                Please return the borrowed item as soon as possible.
            </p>

            <br>

            <p>
                Regards,<br>
                <strong>Community Item Sharing Portal Admin</strong>
            </p>

        </div>
        ";


        /* =================================================
           SEND EMAIL
           ================================================= */

        $sent = sendEmail(
            $user['email'],
            $user['full_name'],
            $subject,
            $email_body
        );


        /* =================================================
           RESULT
           ================================================= */

        if ($sent) {

            header(
                "Location: overdue_items.php?success=" .
                urlencode(
                    "Message sent successfully to " .
                    $user['full_name'] . "."
                )
            );

            exit();

        } else {

            $error =
                "Unable to send the email. Please check the mail configuration.";

        }
    }
}

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Contact User</title>

    <link rel="stylesheet"
          href="admin.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>

        .contact-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
        }

        .contact-card {
            background: #ffffff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        .contact-card h2 {
            margin-bottom: 25px;
        }

        .user-info {
            background: #f5f7fa;
            padding: 18px;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .user-info p {
            margin: 8px 0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 15px;
            box-sizing: border-box;
        }

        .form-group textarea {
            min-height: 180px;
            resize: vertical;
        }

        .button-row {
            display: flex;
            gap: 12px;
            margin-top: 20px;
        }

        .btn-send {
            background: #2563eb;
            color: white;
            border: none;
            padding: 12px 22px;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-back {
            background: #6b7280;
            color: white;
            padding: 12px 22px;
            border-radius: 8px;
            text-decoration: none;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

    </style>

</head>

<body>

<div class="contact-container">

    <div class="contact-card">

        <h2>
            <i class="fa-solid fa-envelope"></i>
            Contact User
        </h2>


        <!-- USER INFORMATION -->

        <div class="user-info">

            <p>
                <strong>Name:</strong>
                <?= htmlspecialchars($user['full_name']); ?>
            </p>

            <p>
                <strong>Email:</strong>
                <?= htmlspecialchars($user['email']); ?>
            </p>

            <p>
                <strong>Item:</strong>
                <?= htmlspecialchars($user['item_name']); ?>
            </p>

            <p>
                <strong>Expected Return Date:</strong>
                <?= htmlspecialchars($user['expected_return_date']); ?>
            </p>

            <p>
                <strong>Status:</strong>
                <?= htmlspecialchars($user['status']); ?>
            </p>

        </div>


        <!-- ERROR -->

        <?php if (!empty($error)): ?>

            <div class="alert-error">

                <?= htmlspecialchars($error); ?>

            </div>

        <?php endif; ?>


        <!-- CONTACT FORM -->

        <form method="POST">

            <input
                type="hidden"
                name="contact_user_token"
                value="<?= htmlspecialchars($contact_user_token); ?>"
            >


            <div class="form-group">

                <label for="subject">
                    Subject
                </label>

                <input
                    type="text"
                    id="subject"
                    name="subject"
                    value="<?= htmlspecialchars(
                        $_POST['subject']
                        ?? 'Overdue Item - Return Required'
                    ); ?>"
                    maxlength="150"
                    required
                >

            </div>


            <div class="form-group">

                <label for="message">
                    Message
                </label>

                <textarea
                    id="message"
                    name="message"
                    maxlength="3000"
                    required
                ><?= htmlspecialchars(
                    $_POST['message']
                    ?? "Your borrowed item is overdue. Please return the item as soon as possible."
                ); ?></textarea>

            </div>


            <div class="button-row">

                <button
                    type="submit"
                    class="btn-send">

                    <i class="fa-solid fa-paper-plane"></i>

                    Send Message

                </button>


                <a
                    href="overdue_items.php"
                    class="btn-back">

                    <i class="fa-solid fa-arrow-left"></i>

                    Back

                </a>

            </div>

        </form>

    </div>

</div>

</body>

</html>