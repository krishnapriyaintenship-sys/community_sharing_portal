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
   USER DETAILS
   ========================================================= */

$user_id = intval($_SESSION['user_id']);

$user_name = $_SESSION['full_name'] ?? '';
$user_email = $_SESSION['email'] ?? '';

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Contact Admin | CampusShare</title>


    <!-- Google Font -->

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet"
    >


    <!-- Font Awesome -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >


    <!-- Contact CSS -->

    <link
        rel="stylesheet"
        href="css/contact.css"
    >

</head>


<body>


<div class="contact-page">


    <!-- =====================================================
         BACK BUTTON
         ===================================================== -->

    <a
        href="dashboard.php"
        class="back-button"
    >

        <i class="fa-solid fa-arrow-left"></i>

        Back to Dashboard

    </a>



    <!-- =====================================================
         CONTACT CARD
         ===================================================== -->

    <div class="contact-card">


        <!-- ICON -->

        <div class="contact-icon">

            <i class="fa-solid fa-envelope"></i>

        </div>


        <!-- TITLE -->

        <h1>
            Contact Admin
        </h1>


        <p class="subtitle">

            Have a question or problem?

            <br>

            Send a message to the CampusShare administrator.

        </p>



        <!-- =================================================
             SUCCESS MESSAGE
             ================================================= -->

        <?php if (!empty($success)): ?>

            <div class="message success-message">

                <i class="fa-solid fa-circle-check"></i>

                <?php
                echo htmlspecialchars($success);
                ?>

            </div>

        <?php endif; ?>



        <!-- =================================================
             ERROR MESSAGE
             ================================================= -->

        <?php if (!empty($error)): ?>

            <div class="message error-message">

                <i class="fa-solid fa-circle-exclamation"></i>

                <?php
                echo htmlspecialchars($error);
                ?>

            </div>

        <?php endif; ?>



        <!-- =================================================
             CONTACT FORM
             ================================================= -->

        <form
            action="contact_process.php"
            method="POST"
            id="contactForm"
        >


            <!-- =================================================
                 NAME
                 ================================================= -->

            <div class="form-group">

                <label for="name">

                    Full Name

                </label>


                <div class="input-box">

                    <i class="fa-solid fa-user"></i>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="<?php
                            echo htmlspecialchars($user_name);
                        ?>"
                        placeholder="Enter your name"
                        maxlength="100"
                        required
                    >

                </div>

            </div>



            <!-- =================================================
                 EMAIL
                 ================================================= -->

            <div class="form-group">

                <label for="email">

                    Email Address

                </label>


                <div class="input-box">

                    <i class="fa-solid fa-envelope"></i>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?php
                            echo htmlspecialchars($user_email);
                        ?>"
                        placeholder="Enter your email"
                        maxlength="100"
                        required
                    >

                </div>

            </div>



            <!-- =================================================
                 SUBJECT
                 ================================================= -->

            <div class="form-group">

                <label for="subject">

                    Subject

                </label>


                <div class="input-box">

                    <i class="fa-solid fa-heading"></i>

                    <input
                        type="text"
                        id="subject"
                        name="subject"
                        placeholder="Enter message subject"
                        maxlength="150"
                        required
                    >

                </div>

            </div>



            <!-- =================================================
                 MESSAGE
                 ================================================= -->

            <div class="form-group">

                <label for="message">

                    Message

                </label>


                <textarea
                    id="message"
                    name="message"
                    rows="6"
                    maxlength="2000"
                    placeholder="Write your message to the administrator..."
                    required
                ></textarea>


                <div class="character-count">

                    <span id="characterCount">
                        0
                    </span>

                    / 2000

                </div>

            </div>



            <!-- =================================================
                 SUBMIT BUTTON
                 ================================================= -->

            <button
                type="submit"
                class="submit-button"
            >

                <i class="fa-solid fa-paper-plane"></i>

                Send Message

            </button>


        </form>


    </div>


</div>



<!-- =========================================================
     JAVASCRIPT
     ========================================================= -->

<script>

const message =
    document.getElementById("message");

const characterCount =
    document.getElementById("characterCount");


message.addEventListener("input", function () {

    characterCount.textContent =
        message.value.length;

});

</script>


</body>

</html>