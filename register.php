<?php
session_start();

if (isset($_SESSION['user_id'])) {
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

                <img src="images/logo.png" alt="CampusShare Logo">

                <h2>CampusShare</h2>

            </div>


            <h3>Create Your Account</h3>


            <form
                action="register_process.php"
                method="POST"
                enctype="multipart/form-data"
                id="registerForm"
            >

                <!-- Full Name -->

                <div class="input-group">

                    <label for="full_name">Full Name</label>

                    <div class="input-field">

                        <i class="fa-solid fa-user"></i>

                        <input
                            type="text"
                            id="full_name"
                            name="full_name"
                            placeholder="Enter Full Name"
                            maxlength="100"
                            required
                        >

                    </div>

                </div>


                <!-- Email -->

                <div class="input-group">

                    <label for="email">College Email</label>

                    <div class="input-field">

                        <i class="fa-solid fa-envelope"></i>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="Enter College Email"
                            maxlength="100"
                            autocomplete="email"
                            required
                        >

                    </div>

                </div>


                <!-- Phone -->

                <div class="input-group">

                    <label for="phone">Phone Number</label>

                    <div class="input-field">

                        <i class="fa-solid fa-phone"></i>

                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            placeholder="Enter 10-digit Phone Number"
                            maxlength="10"
                            pattern="[0-9]{10}"
                            inputmode="numeric"
                            required
                        >

                    </div>

                </div>


                <!-- Department -->

                <div class="input-group">

                    <label for="department">Department</label>

                    <div class="input-field">

                        <i class="fa-solid fa-building-columns"></i>

                        <select
                            id="department"
                            name="department"
                            required
                        >

                            <option value="">Select Department</option>

                            <option value="BCA">BCA</option>

                            <option value="BSc Computer Science">
                                BSc Computer Science
                            </option>

                            <option value="BCom">BCom</option>

                            <option value="BA English">
                                BA English
                            </option>

                            <option value="BBA">BBA</option>

                            <option value="MCA">MCA</option>

                        </select>

                    </div>

                </div>


                <!-- Year -->

                <div class="input-group">

                    <label for="year">Year of Study</label>

                    <div class="input-field">

                        <i class="fa-solid fa-calendar"></i>

                        <select
                            id="year"
                            name="year"
                            required
                        >

                            <option value="">Select Year</option>

                            <option value="First Year">
                                First Year
                            </option>

                            <option value="Second Year">
                                Second Year
                            </option>

                            <option value="Third Year">
                                Third Year
                            </option>

                            <option value="Final Year">
                                Final Year
                            </option>

                        </select>

                    </div>

                </div>


                <!-- Profile Photo -->

                <div class="input-group">

                    <label for="profile_image">
                        Profile Photo
                    </label>

                    <input
                        type="file"
                        id="profile_image"
                        name="profile_image"
                        accept=".jpg,.jpeg,.png"
                    >

                    <small class="file-note">
                        JPG, JPEG or PNG. Maximum 2 MB.
                    </small>

                </div>


                <!-- Password -->

                <div class="input-group">

                    <label for="password">Password</label>

                    <div class="input-field">

                        <i class="fa-solid fa-lock"></i>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Create Password"
                            minlength="6"
                            autocomplete="new-password"
                            required
                        >

                    </div>

                </div>


                <!-- Confirm Password -->

                <div class="input-group">

                    <label for="confirmPassword">
                        Confirm Password
                    </label>

                    <div class="input-field">

                        <i class="fa-solid fa-lock"></i>

                        <input
                            type="password"
                            id="confirmPassword"
                            name="confirm_password"
                            placeholder="Confirm Password"
                            minlength="6"
                            autocomplete="new-password"
                            required
                        >

                    </div>

                </div>


                <!-- Terms -->

                <div class="terms">

                    <label>

                        <input
                            type="checkbox"
                            name="terms"
                            value="1"
                            required
                        >

                        I agree to the Terms & Conditions

                    </label>

                </div>


                <!-- Register Button -->

                <button type="submit" id="registerButton">

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