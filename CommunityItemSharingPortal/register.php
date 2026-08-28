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

<title>Register | CampusShare</title>

<link rel="stylesheet" href="css/register.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
rel="stylesheet">

</head>

<body>

<div class="container">

    <!-- Left Side -->

    <div class="left-panel">

        <img src="images/register-banner.jpg" alt="Register">

        <div class="overlay">

            <h1>Join CampusShare</h1>

            <p>

                Share books, calculators, laptops and project kits
                with your college community.

            </p>

        </div>

    </div>

    <!-- Right Side -->

    <div class="right-panel">

        <div class="form-box">

            <div class="logo">

                <img src="images/logo.png" alt="Logo">

                <h2>CampusShare</h2>

            </div>

            <h3>Create Your Account</h3>

            <form
            action="register_process.php"
            method="POST"
            enctype="multipart/form-data">

                <!-- Full Name -->

                <div class="input-group">

                    <label>Full Name</label>

                    <div class="input-field">

                        <i class="fa-solid fa-user"></i>

                        <input
                        type="text"
                        name="full_name"
                        placeholder="Enter Full Name"
                        required>

                    </div>

                </div>

                <!-- Email -->

                <div class="input-group">

                    <label>College Email</label>

                    <div class="input-field">

                        <i class="fa-solid fa-envelope"></i>

                        <input
                        type="email"
                        name="email"
                        placeholder="Enter College Email"
                        required>

                    </div>

                </div>

                <!-- Phone -->

                <div class="input-group">

                    <label>Phone Number</label>

                    <div class="input-field">

                        <i class="fa-solid fa-phone"></i>

                        <input
                        type="text"
                        name="phone"
                        placeholder="Enter Phone Number"
                        required>

                    </div>

                </div>

                <!-- Department -->

                <div class="input-group">

                    <label>Department</label>

                    <div class="input-field">

                        <i class="fa-solid fa-building-columns"></i>

                        <select name="department" required>

                            <option value="">Select Department</option>

                            <option>BCA</option>
                            <option>BSc Computer Science</option>
                            <option>BCom</option>
                            <option>BA English</option>
                            <option>BBA</option>
                            <option>MCA</option>

                        </select>

                    </div>

                </div>

                <!-- Year -->

                <div class="input-group">

                    <label>Year of Study</label>

                    <div class="input-field">

                        <i class="fa-solid fa-calendar"></i>

                        <select name="year">

                            <option value="">Select Year</option>

                            <option>First Year</option>
                            <option>Second Year</option>
                            <option>Third Year</option>
                            <option>Final Year</option>

                        </select>

                    </div>

                </div>

                <!-- Profile Photo -->

                <div class="input-group">

                    <label>Profile Photo</label>

                    <input
                    type="file"
                    name="profile_image"
                    accept=".jpg,.jpeg,.png">

                </div>

                <!-- Password -->

                <div class="input-group">

                    <label>Password</label>

                    <div class="input-field">

                        <i class="fa-solid fa-lock"></i>

                        <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Create Password"
                        required>

                    </div>

                </div>

                <!-- Confirm Password -->

                <div class="input-group">

                    <label>Confirm Password</label>

                    <div class="input-field">

                        <i class="fa-solid fa-lock"></i>

                        <input
                        type="password"
                        id="confirmPassword"
                        name="confirm_password"
                        placeholder="Confirm Password"
                        required>

                    </div>

                </div>

                <!-- Terms -->

                <div class="terms">

                    <label>

                        <input
                        type="checkbox"
                        required>

                        I agree to the Terms &
                        Conditions

                    </label>

                </div>

                <button type="submit">

                    <i class="fa-solid fa-user-plus"></i>

                    Register

                </button>

            </form>

            <div class="login-link">

                Already have an account?

                <a href="login.php">

                    Login Here

                </a>

            </div>

        </div>

    </div>

</div>

<script src="js/register.js"></script>

</body>
</html>