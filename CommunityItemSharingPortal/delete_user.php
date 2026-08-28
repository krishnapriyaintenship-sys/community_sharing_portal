<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

include("includes/db.php");

if (isset($_GET['user_id'])) {

    $user_id = (int)$_GET['user_id'];

    // Prevent deleting a user that doesn't exist
    $check = mysqli_query($conn, "SELECT * FROM users WHERE user_id='$user_id'");

    if (mysqli_num_rows($check) > 0) {

        // Delete user
        $delete = mysqli_query($conn, "DELETE FROM users WHERE user_id='$user_id'");

        if ($delete) {

            echo "<script>
            alert('User deleted successfully.');
            window.location='manage_users.php';
            </script>";

        } else {

            echo "<script>
            alert('Unable to delete user.');
            window.location='manage_users.php';
            </script>";

        }

    } else {

        echo "<script>
        alert('User not found.');
        window.location='manage_users.php';
        </script>";

    }

} else {

    header("Location: manage_users.php");
    exit();
}
?>
