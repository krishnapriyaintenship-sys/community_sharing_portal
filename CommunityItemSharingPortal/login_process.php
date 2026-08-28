<?php
session_start();
include("includes/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = trim($_POST['password']);

    // Check if email exists
    $sql = "SELECT * FROM users WHERE email='$email' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) == 1){

        $user = mysqli_fetch_assoc($result);

        // Verify hashed password
        if(password_verify($password, $user['password'])){

            // Create session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['profile_image'] = $user['profile_image'];

            header("Location: dashboard.php");
            exit();

        }else{

            echo "<script>
                alert('Incorrect password.');
                window.location='login.php';
            </script>";

        }

    }else{

        echo "<script>
            alert('No account found with this email.');
            window.location='login.php';
        </script>";

    }

}else{

    header("Location: login.php");
    exit();

}
?>