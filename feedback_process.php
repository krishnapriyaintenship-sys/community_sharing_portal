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

    header("Location: feedback.php");

    exit();
}


/* =========================================================
   DATABASE CONNECTION
   ========================================================= */

require_once "includes/db.php";


/* =========================================================
   GET USER ID
   ========================================================= */

$user_id = intval($_SESSION['user_id']);


/* =========================================================
   GET FORM DATA
   ========================================================= */

$rating = intval($_POST['rating'] ?? 0);

$comments = trim(
    $_POST['comments'] ?? ''
);


/* =========================================================
   VALIDATE RATING
   ========================================================= */

if ($rating < 1 || $rating > 5) {

    header(
        "Location: feedback.php?error=" .
        urlencode(
            "Please select a rating between 1 and 5."
        )
    );

    exit();
}


/* =========================================================
   VALIDATE COMMENTS
   ========================================================= */

if (empty($comments)) {

    header(
        "Location: feedback.php?error=" .
        urlencode(
            "Please enter your feedback."
        )
    );

    exit();
}


/* =========================================================
   CHECK COMMENT LENGTH
   ========================================================= */

if (strlen($comments) > 1000) {

    header(
        "Location: feedback.php?error=" .
        urlencode(
            "Feedback cannot exceed 1000 characters."
        )
    );

    exit();
}


/* =========================================================
   INSERT FEEDBACK
   ========================================================= */

$sql = "
    INSERT INTO feedback
    (
        user_id,
        rating,
        comments
    )
    VALUES
    (
        ?,
        ?,
        ?
    )
";


$stmt = mysqli_prepare(
    $conn,
    $sql
);


if (!$stmt) {

    header(
        "Location: feedback.php?error=" .
        urlencode(
            "Unable to submit feedback. Please try again."
        )
    );

    exit();
}


mysqli_stmt_bind_param(
    $stmt,
    "iis",
    $user_id,
    $rating,
    $comments
);


/* =========================================================
   EXECUTE
   ========================================================= */

if (mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    header(
        "Location: feedback.php?success=" .
        urlencode(
            "Thank you! Your feedback has been submitted successfully."
        )
    );

    exit();

} else {

    mysqli_stmt_close($stmt);

    header(
        "Location: feedback.php?error=" .
        urlencode(
            "Unable to submit feedback. Please try again."
        )
    );

    exit();
}

?>