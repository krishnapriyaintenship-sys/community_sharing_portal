<?php
session_start();
include("includes/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = mysqli_real_escape_string($conn, trim($_POST['email']));

    $query = "SELECT * FROM users WHERE email='$email' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {

        $_SESSION['reset_email'] = $email;

        header("Location: reset_password.php");
        exit();

    } else {

        echo "<script>
            alert('No account found with this email.');
            window.location='forgot_password.php';
        </script>";

    }

} else {

    header("Location: forgot_password.php");
    exit();

}
?>