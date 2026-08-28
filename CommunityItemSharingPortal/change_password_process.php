<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include("includes/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_id = $_SESSION['user_id'];

    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Check if new passwords match
    if ($new_password != $confirm_password) {

        echo "<script>
        alert('New Password and Confirm Password do not match.');
        window.location='change_password.php';
        </script>";
        exit();
    }

    // Minimum password length
    if (strlen($new_password) < 6) {

        echo "<script>
        alert('Password must be at least 6 characters long.');
        window.location='change_password.php';
        </script>";
        exit();
    }

    // Get current password from database
    $sql = "SELECT password FROM users WHERE user_id='$user_id'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {

        $user = mysqli_fetch_assoc($result);

        // Verify current password
        if (!password_verify($current_password, $user['password'])) {

            echo "<script>
            alert('Current Password is incorrect.');
            window.location='change_password.php';
            </script>";
            exit();
        }

        // Check if new password is same as current
        if (password_verify($new_password, $user['password'])) {

            echo "<script>
            alert('New Password cannot be the same as the current password.');
            window.location='change_password.php';
            </script>";
            exit();
        }

        // Hash new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Update password
        $update = "UPDATE users
                   SET password='$hashed_password'
                   WHERE user_id='$user_id'";

        if (mysqli_query($conn, $update)) {

            echo "<script>
            alert('Password changed successfully.');
            window.location='profile.php';
            </script>";

        } else {

            echo "<script>
            alert('Failed to update password.');
            window.location='change_password.php';
            </script>";

        }

    } else {

        echo "<script>
        alert('User not found.');
        window.location='login.php';
        </script>";

    }

} else {

    header("Location: change_password.php");
    exit();
}
?>