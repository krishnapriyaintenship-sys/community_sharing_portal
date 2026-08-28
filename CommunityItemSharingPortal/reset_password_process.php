<?php
session_start();
include("includes/db.php");

if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_SESSION['reset_email'];

    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Check if passwords match
    if ($new_password != $confirm_password) {
        echo "<script>
                alert('Passwords do not match!');
                window.location='reset_password.php';
              </script>";
        exit();
    }

    // Password length check
    if (strlen($new_password) < 6) {
        echo "<script>
                alert('Password must be at least 6 characters.');
                window.location='reset_password.php';
              </script>";
        exit();
    }

    // Encrypt password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update password
    $update = "UPDATE users SET password='$hashed_password' WHERE email='$email'";

    if (mysqli_query($conn, $update)) {

        unset($_SESSION['reset_email']);

        echo "<script>
                alert('Password changed successfully!');
                window.location='login.php';
              </script>";

    } else {

        echo "<script>
                alert('Something went wrong!');
                window.location='forgot_password.php';
              </script>";

    }

} else {
    header("Location: forgot_password.php");
    exit();
}
?>