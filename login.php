<?php
session_start();

if(isset($_SESSION['user_id'])){
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Login | CampusShare</title>

<link rel="stylesheet" href="css/login.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
rel="stylesheet">

</head>

<body>

<div class="login-container">

    <!-- Left Side -->
    <div class="left-panel">

        <img src="images/login-banner.png" alt="CampusShare Login">

        <div class="overlay">

            <h1>Welcome Back!</h1>

            <p>
                Borrow books, calculators, laptops and project kits from your college community.
            </p>

        </div>

    </div>

    <!-- Right Side -->
    <div class="right-panel">

        <div class="login-box">

            <div class="logo">

                <img src="images/logo.png" alt="CampusShare Logo">

                <h2>CampusShare</h2>

            </div>

            <h3>Login to Your Account</h3>

            <form action="login_process.php" method="POST">

                <div class="input-box">

                    <label>Email Address</label>

                    <div class="input-field">

                        <i class="fa-solid fa-envelope"></i>

                        <input
                        type="email"
                        name="email"
                        placeholder="Enter your email"
                        required>

                    </div>

                </div>

                <div class="input-box">

                    <label>Password</label>

                    <div class="input-field">

                        <i class="fa-solid fa-lock"></i>

                        <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        required>

                        <i class="fa-solid fa-eye"
                        id="togglePassword"></i>

                    </div>

                </div>

                <div class="options">

                    <label>

                        <input type="checkbox" name="remember">

                        Remember Me

                    </label>

                    <a href="forgot_password.php">

                        Forgot Password?

                    </a>

                </div>

                <button type="submit">

                    <i class="fa-solid fa-right-to-bracket"></i>

                    Login

                </button>

            </form>

            <div class="register-link">

                Don't have an account?

                <a href="register.php">

                    Register Here

                </a>

            </div>

        </div>

    </div>

</div>

<script src="js/login.js"></script>

</body>

</html>