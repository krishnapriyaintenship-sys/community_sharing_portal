<?php

session_start();

/* =========================================================
   CHECK USER LOGIN
   ========================================================= */

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_name = $_SESSION['full_name'] ?? 'User';

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

    <title>Give Feedback | CampusShare</title>


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


    <!-- Feedback CSS -->

    <link
        rel="stylesheet"
        href="css/feedback.css"
    >

</head>


<body>


<div class="feedback-page">


    <!-- =====================================================
         BACK TO DASHBOARD
         ===================================================== -->

    <a
        href="dashboard.php"
        class="back-button"
    >

        <i class="fa-solid fa-arrow-left"></i>

        Back to Dashboard

    </a>


    <!-- =====================================================
         FEEDBACK CARD
         ===================================================== -->

    <div class="feedback-card">


        <!-- ICON -->

        <div class="feedback-icon">

            <i class="fa-solid fa-comment-dots"></i>

        </div>


        <!-- TITLE -->

        <h1>
            Give Your Feedback
        </h1>


        <p class="subtitle">

            Hello
            <strong>
                <?php echo htmlspecialchars($user_name); ?>
            </strong>!

            <br>

            We would love to hear your thoughts about CampusShare.

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
             FEEDBACK FORM
             ================================================= -->

        <form
            action="feedback_process.php"
            method="POST"
            id="feedbackForm"
        >


            <!-- =================================================
                 RATING
                 ================================================= -->

            <div class="form-group">


                <label>
                    How would you rate CampusShare?
                </label>


                <div class="rating-container">


                    <input
                        type="radio"
                        name="rating"
                        id="star5"
                        value="5"
                    >

                    <label
                        for="star5"
                        title="Excellent"
                    >
                        ★
                    </label>


                    <input
                        type="radio"
                        name="rating"
                        id="star4"
                        value="4"
                    >

                    <label
                        for="star4"
                        title="Very Good"
                    >
                        ★
                    </label>


                    <input
                        type="radio"
                        name="rating"
                        id="star3"
                        value="3"
                    >

                    <label
                        for="star3"
                        title="Good"
                    >
                        ★
                    </label>


                    <input
                        type="radio"
                        name="rating"
                        id="star2"
                        value="2"
                    >

                    <label
                        for="star2"
                        title="Fair"
                    >
                        ★
                    </label>


                    <input
                        type="radio"
                        name="rating"
                        id="star1"
                        value="1"
                    >

                    <label
                        for="star1"
                        title="Poor"
                    >
                        ★
                    </label>


                </div>


                <small>
                    Select a rating from 1 to 5 stars.
                </small>


            </div>



            <!-- =================================================
                 COMMENTS
                 ================================================= -->

            <div class="form-group">


                <label for="comments">

                    Your Feedback

                </label>


                <textarea
                    name="comments"
                    id="comments"
                    rows="6"
                    maxlength="1000"
                    placeholder="Write your feedback here..."
                    required
                ></textarea>


                <div class="character-count">

                    <span id="characterCount">
                        0
                    </span>
                    / 1000

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

                Submit Feedback

            </button>


        </form>


    </div>


</div>


<script>

/* =========================================================
   CHARACTER COUNT
   ========================================================= */

const comments =
    document.getElementById("comments");

const characterCount =
    document.getElementById("characterCount");


comments.addEventListener("input", function () {

    characterCount.textContent =
        comments.value.length;

});


/* =========================================================
   FORM VALIDATION
   ========================================================= */

document
    .getElementById("feedbackForm")
    .addEventListener("submit", function(event) {

        const rating =
            document.querySelector(
                'input[name="rating"]:checked'
            );

        if (!rating) {

            alert(
                "Please select a rating before submitting."
            );

            event.preventDefault();

            return;
        }

    });

</script>


</body>

</html>