<?php

session_start();

/* =========================================================
   CHECK USER LOGIN
   ========================================================= */

if (!isset($_SESSION['user_id'])) {

    header("Location: login.php");
    exit();
}


/* =========================================================
   CHECK REQUEST METHOD
   ========================================================= */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: contact.php");
    exit();
}


/* =========================================================
   DATABASE CONNECTION
   ========================================================= */

require_once "includes/db.php";


/* =========================================================
   GET LOGGED-IN USER ID
   ========================================================= */

$user_id = intval($_SESSION['user_id']);


/* =========================================================
   GET FORM DATA
   ========================================================= */

$name = trim($_POST['name'] ?? '');

$email = trim($_POST['email'] ?? '');

$subject = trim($_POST['subject'] ?? '');

$message = trim($_POST['message'] ?? '');


/* =========================================================
   VALIDATE NAME
   ========================================================= */

if ($name === '') {

    header(
        "Location: contact.php?error=" .
        urlencode("Please enter your name.")
    );

    exit();
}


/* =========================================================
   VALIDATE EMAIL
   ========================================================= */

if (
    $email === '' ||
    !filter_var($email, FILTER_VALIDATE_EMAIL)
) {

    header(
        "Location: contact.php?error=" .
        urlencode("Please enter a valid email address.")
    );

    exit();
}


/* =========================================================
   VALIDATE SUBJECT
   ========================================================= */

if ($subject === '') {

    header(
        "Location: contact.php?error=" .
        urlencode("Please enter a subject.")
    );

    exit();
}


/* =========================================================
   VALIDATE MESSAGE
   ========================================================= */

if ($message === '') {

    header(
        "Location: contact.php?error=" .
        urlencode("Please enter your message.")
    );

    exit();
}


/* =========================================================
   LENGTH VALIDATION
   ========================================================= */

if (strlen($name) > 100) {

    header(
        "Location: contact.php?error=" .
        urlencode("Name cannot exceed 100 characters.")
    );

    exit();
}


if (strlen($email) > 100) {

    header(
        "Location: contact.php?error=" .
        urlencode("Email cannot exceed 100 characters.")
    );

    exit();
}


if (strlen($subject) > 150) {

    header(
        "Location: contact.php?error=" .
        urlencode("Subject cannot exceed 150 characters.")
    );

    exit();
}


if (strlen($message) > 2000) {

    header(
        "Location: contact.php?error=" .
        urlencode("Message cannot exceed 2000 characters.")
    );

    exit();
}


/* =========================================================
   INSERT CONTACT MESSAGE
   ========================================================= */

$sql = "
    INSERT INTO contact_messages
    (
        user_id,
        name,
        email,
        subject,
        message
    )
    VALUES
    (?, ?, ?, ?, ?)
";


$stmt = mysqli_prepare($conn, $sql);


/* =========================================================
   CHECK PREPARED STATEMENT
   ========================================================= */

if (!$stmt) {

    header(
        "Location: contact.php?error=" .
        urlencode(
            "Database error. Unable to send your message."
        )
    );

    exit();
}


/* =========================================================
   BIND PARAMETERS
   ========================================================= */

mysqli_stmt_bind_param(
    $stmt,
    "issss",
    $user_id,
    $name,
    $email,
    $subject,
    $message
);


/* =========================================================
   EXECUTE QUERY
   ========================================================= */

if (mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    header(
        "Location: contact.php?success=" .
        urlencode(
            "Your message has been sent to the administrator successfully."
        )
    );

    exit();

}


/* =========================================================
   INSERT FAILED
   ========================================================= */

mysqli_stmt_close($stmt);

header(
    "Location: contact.php?error=" .
    urlencode(
        "Unable to send your message. Please try again."
    )
);

exit();

?>