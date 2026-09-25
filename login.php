<?php
session_start();

/*
|--------------------------------------------------------------------------
| If already logged in, redirect to the correct dashboard
|--------------------------------------------------------------------------
*/

// Admin already logged in
if (
    isset($_SESSION['admin_id']) &&
    isset($_SESSION['admin_role']) &&
    $_SESSION['admin_role'] === 'admin'
) {
    header("Location: admin/dashboard.php");
    exit();
}

// Normal user already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}


// Get login error message
$error = $_GET['error'] ?? '';

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | CampusShare</title>


    <!-- Login CSS -->
    <link rel="stylesheet" href="css/login.css">


    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >


    <!-- Google Font -->
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet"
    >

</head>


<body>


<div class="login-container">


    <!-- =====================================================
         LEFT SIDE
         ===================================================== -->

    <div class="left-panel">


        <img
            src="images/login-banner.png"
            alt="CampusShare Login"
        >


        <div class="overlay">


            <h1>Welcome Back!</h1>


            <p>
                Borrow books, calculators, laptops and project kits
                from your college community.
            </p>


        </div>

    </div>



    <!-- =====================================================
         RIGHT SIDE
         ===================================================== -->

    <div class="right-panel">


        <div class="login-box">


            <!-- =================================================
                 LOGO
                 ================================================= -->

            <div class="logo">


                <img
                    src="images/logo.png"
                    alt="CampusShare Logo"
                >


                <h2>CampusShare</h2>


            </div>



            <!-- =================================================
                 HEADING
                 ================================================= -->

            <h3>Login to Your Account</h3>



            <!-- =================================================
                 ERROR MESSAGE
                 ================================================= -->

            <?php if (!empty($error)): ?>

                <div class="error-message">

                    <i class="fa-solid fa-circle-exclamation"></i>

                    <?php echo htmlspecialchars($error); ?>

                </div>

            <?php endif; ?>



            <!-- =================================================
                 LOGIN FORM
                 ================================================= -->

            <form
                action="login_process.php"
                method="POST"
                autocomplete="on"
            >


                <!-- =================================================
                     EMAIL
                     ================================================= -->

                <div class="input-box">


                    <label for="email">
                        Email Address
                    </label>


                    <div class="input-field">


                        <i class="fa-solid fa-envelope"></i>


                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="Enter your email"
                            autocomplete="email"
                            required
                        >


                    </div>


                </div>



                <!-- =================================================
                     PASSWORD
                     ================================================= -->

                <div class="input-box">


                    <label for="password">
                        Password
                    </label>


                    <div class="input-field">


                        <i class="fa-solid fa-lock"></i>


                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            required
                        >


                        <!-- Show / Hide Password -->

                        <i
                            class="fa-solid fa-eye"
                            id="togglePassword"
                            title="Show Password"
                        ></i>


                    </div>


                </div>



                <!-- =================================================
                     REMEMBER ME + FORGOT PASSWORD
                     ================================================= -->

                <div class="options">


                    <label>


                        <input
                            type="checkbox"
                            name="remember"
                            value="1"
                        >


                        Remember Me


                    </label>



                    <a href="forgot_password.php">
                        Forgot Password?
                    </a>


                </div>



                <!-- =================================================
                     LOGIN BUTTON
                     ================================================= -->

                <button type="submit">


                    <i class="fa-solid fa-right-to-bracket"></i>


                    Login


                </button>


            </form>



            <!-- =================================================
                 REGISTER LINK
                 ================================================= -->

            <div class="register-link">


                Don't have an account?


                <a href="register.php">
                    Register Here
                </a>


            </div>


        </div>


    </div>


</div>



<!-- =========================================================
     LOGIN JAVASCRIPT
     ========================================================= -->

<script src="js/login.js"></script>


</body>

</html>