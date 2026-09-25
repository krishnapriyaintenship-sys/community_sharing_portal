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
   MAIL FUNCTION
   ========================================================= */

require_once "../includes/mail.php";


/* =========================================================
   CHECK REQUEST METHOD
   ========================================================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: overdue_items.php");
    exit();

}


/* =========================================================
   GET REQUEST ID
   ========================================================= */

$request_id = intval($_POST['request_id'] ?? 0);


if ($request_id <= 0) {

    header(
        "Location: overdue_items.php?error=" .
        urlencode("Invalid request.")
    );

    exit();

}


/* =========================================================
   GET OVERDUE BORROWING DETAILS
   ========================================================= */

$sql = "

    SELECT

        br.request_id,
        br.expected_return_date,
        br.status,

        i.item_name,

        borrower.full_name AS borrower_name,
        borrower.email AS borrower_email,

        owner.full_name AS owner_name

    FROM borrow_requests br

    INNER JOIN items i
        ON br.item_id = i.item_id

    LEFT JOIN users borrower
        ON br.borrower_id = borrower.user_id

    LEFT JOIN users owner
        ON br.owner_id = owner.user_id

    WHERE

        br.request_id = ?

        AND br.expected_return_date < CURDATE()

        AND br.actual_return_date IS NULL

        AND br.status IN (
            'Approved',
            'Item Received',
            'Return Requested'
        )

    LIMIT 1

";


$stmt = mysqli_prepare($conn, $sql);


if (!$stmt) {

    header(
        "Location: overdue_items.php?error=" .
        urlencode("Database error.")
    );

    exit();

}


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $request_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$data = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/* =========================================================
   CHECK RECORD
   ========================================================= */

if (!$data) {

    header(
        "Location: overdue_items.php?error=" .
        urlencode(
            "This borrowing is no longer overdue or was already returned."
        )
    );

    exit();

}


/* =========================================================
   CHECK BORROWER EMAIL
   ========================================================= */

if (empty($data['borrower_email'])) {

    header(
        "Location: overdue_items.php?error=" .
        urlencode(
            "The borrower does not have a valid email address."
        )
    );

    exit();

}


/* =========================================================
   CALCULATE DAYS OVERDUE
   ========================================================= */

$due_date = new DateTime(
    $data['expected_return_date']
);

$today = new DateTime();

$days_overdue = $due_date->diff($today)->days;


/* =========================================================
   PREPARE EMAIL
   ========================================================= */

$borrower_name =
    htmlspecialchars(
        $data['borrower_name'] ?? 'Borrower'
    );

$item_name =
    htmlspecialchars(
        $data['item_name'] ?? 'Borrowed Item'
    );

$expected_return_date =
    date(
        'd M Y',
        strtotime(
            $data['expected_return_date']
        )
    );


/* =========================================================
   EMAIL SUBJECT
   ========================================================= */

$subject =
    "Overdue Return Reminder - CampusShare";


/* =========================================================
   EMAIL BODY
   ========================================================= */

$body = "

<!DOCTYPE html>

<html>

<head>

    <meta charset='UTF-8'>

</head>

<body
    style='
        font-family: Arial, sans-serif;
        background:#f4f7fb;
        padding:20px;
    '
>


<div
    style='
        max-width:600px;
        margin:auto;
        background:white;
        padding:30px;
        border-radius:10px;
    '
>


    <h2
        style='
            color:#dc2626;
            margin-top:0;
        '
    >
        Overdue Return Reminder
    </h2>


    <p>
        Hello <strong>{$borrower_name}</strong>,
    </p>


    <p>
        This is a reminder from
        <strong>CampusShare</strong>
        regarding the item you borrowed.
    </p>


    <div
        style='
            background:#fff7ed;
            border-left:4px solid #f97316;
            padding:15px;
            margin:20px 0;
        '
    >

        <p>
            <strong>Item:</strong>
            {$item_name}
        </p>


        <p>
            <strong>Expected Return Date:</strong>
            {$expected_return_date}
        </p>


        <p>
            <strong>Days Overdue:</strong>
            {$days_overdue} day(s)
        </p>

    </div>


    <p>
        The expected return date has already passed.
        Please return the item to the owner as soon as possible.
    </p>


    <p>
        If you have already returned the item, please ignore
        this message or make sure the return has been completed
        through the CampusShare system.
    </p>


    <br>


    <p>
        Thank you,<br>
        <strong>CampusShare Team</strong>
    </p>


</div>


</body>

</html>

";


/* =========================================================
   SEND EMAIL
   ========================================================= */

$email_sent = sendEmail(
    $data['borrower_email'],
    $data['borrower_name'],
    $subject,
    $body
);


/* =========================================================
   RESULT
   ========================================================= */

if ($email_sent) {

    header(
        "Location: overdue_items.php?success=" .
        urlencode(
            "Overdue reminder sent successfully to " .
            $data['borrower_name'] . "."
        )
    );

    exit();

} else {

    header(
        "Location: overdue_items.php?error=" .
        urlencode(
            "Unable to send the reminder email."
        )
    );

    exit();

}

?>