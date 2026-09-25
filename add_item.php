<?php
session_start();

/* =========================================================
   CHECK LOGIN
   ========================================================= */

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


/* =========================================================
   DATABASE CONNECTION
   ========================================================= */

include("includes/db.php");


$user_id = (int) $_SESSION['user_id'];


/* =========================================================
   GET LOGGED-IN USER
   ========================================================= */

$user_query = mysqli_query(
    $conn,
    "SELECT * FROM users WHERE user_id = '$user_id'"
);


if (!$user_query) {
    die("Unable to get user details.");
}


$user = mysqli_fetch_assoc($user_query);


/* =========================================================
   GET CATEGORIES
   ========================================================= */

$category_query = mysqli_query(
    $conn,
    "SELECT * FROM categories ORDER BY category_name ASC"
);


if (!$category_query) {
    die("Unable to load categories.");
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Add Item | CampusShare</title>


    <!-- =====================================================
         CSS
         ===================================================== -->

    <link
        rel="stylesheet"
        href="css/add_item.css"
    >


    <!-- =====================================================
         FONT AWESOME
         ===================================================== -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >


    <!-- =====================================================
         GOOGLE FONT
         ===================================================== -->

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet"
    >

</head>


<body>


<!-- =========================================================
     SIDEBAR
     ========================================================= -->

<div class="sidebar">


    <!-- LOGO -->

    <div class="logo">

        <img
            src="images/logo.png"
            alt="CampusShare Logo"
        >

        <h2>
            CampusShare
        </h2>

    </div>


    <!-- MENU -->

    <ul>


        <!-- DASHBOARD -->

        <li>

            <a href="dashboard.php">

                <i class="fa fa-house"></i>

                <span>
                    Dashboard
                </span>

            </a>

        </li>


        <!-- ADD ITEM -->

        <li class="active">

            <a href="add_item.php">

                <i class="fa fa-plus"></i>

                <span>
                    Add Item
                </span>

            </a>

        </li>


        <!-- BROWSE ITEMS -->

        <li>

            <a href="browse_items.php">

                <i class="fa fa-box"></i>

                <span>
                    Browse Items
                </span>

            </a>

        </li>


        <!-- MY ITEMS -->

        <li>

            <a href="my_items.php">

                <i class="fa fa-book"></i>

                <span>
                    My Items
                </span>

            </a>

        </li>


        <!-- PROFILE -->

        <li>

            <a href="profile.php">

                <i class="fa fa-user"></i>

                <span>
                    Profile
                </span>

            </a>

        </li>


        <!-- LOGOUT -->

        <li>

            <a href="logout.php">

                <i class="fa fa-right-from-bracket"></i>

                <span>
                    Logout
                </span>

            </a>

        </li>


    </ul>

</div>



<!-- =========================================================
     MAIN CONTENT
     ========================================================= -->

<div class="main">


    <!-- =====================================================
         HEADER
         ===================================================== -->

    <header>


        <div>

            <h1>
                Add New Item
            </h1>

            <p class="page-subtitle">
                Share an item with your campus community
            </p>

        </div>


        <!-- USER PROFILE -->

        <div class="profile">


            <?php

            /*
             * IMPORTANT:
             *
             * Never use a Windows path such as:
             *
             * C:\xampp\htdocs\...
             *
             * in the src attribute.
             *
             * Use a web-relative path instead.
             */


            if (
                !empty($user['profile_image']) &&
                file_exists(
                    "uploads/" . $user['profile_image']
                )
            ) {

            ?>

                <img
                    src="uploads/<?php
                    echo htmlspecialchars(
                        $user['profile_image']
                    );
                    ?>"
                    alt="Profile"
                >

            <?php

            } else {

            ?>

                <img
                    src="images/default.jpg"
                    alt="Default Profile"
                >

            <?php

            }

            ?>


            <span>

                <?php

                echo htmlspecialchars(
                    $user['full_name']
                );

                ?>

            </span>


        </div>


    </header>



    <!-- =====================================================
         FORM CONTAINER
         ===================================================== -->

    <div class="form-container">


        <form
            action="add_item_process.php"
            method="POST"
            enctype="multipart/form-data"
        >


            <!-- =================================================
                 ITEM NAME
                 ================================================= -->

            <div class="input-box">


                <label for="item_name">

                    Item Name

                </label>


                <input
                    type="text"
                    id="item_name"
                    name="item_name"
                    placeholder="Enter item name"
                    required
                >


            </div>



            <!-- =================================================
                 CATEGORY
                 ================================================= -->

            <div class="input-box">


                <label for="category">

                    Category

                </label>


                <select
                    id="category"
                    name="category"
                    required
                >

                    <option value="">

                        Select Category

                    </option>


                    <?php

                    while (
                        $cat =
                        mysqli_fetch_assoc(
                            $category_query
                        )
                    ) {

                    ?>

                        <option
                            value="<?php
                            echo (int)
                                $cat['category_id'];
                            ?>"
                        >

                            <?php

                            echo htmlspecialchars(
                                $cat['category_name']
                            );

                            ?>

                        </option>

                    <?php

                    }

                    ?>

                </select>


            </div>



            <!-- =================================================
                 DESCRIPTION
                 ================================================= -->

            <div class="input-box">


                <label for="description">

                    Description

                </label>


                <textarea
                    id="description"
                    name="description"
                    rows="5"
                    placeholder="Describe your item"
                    required
                ></textarea>


            </div>



            <!-- =================================================
                 CONDITION + LOCATION
                 ================================================= -->

            <div class="row">


                <!-- CONDITION -->

                <div class="input-box">


                    <label for="item_condition">

                        Condition

                    </label>


                    <select
                        id="item_condition"
                        name="item_condition"
                        required
                    >

                        <option value="New">
                            New
                        </option>

                        <option value="Like New">
                            Like New
                        </option>

                        <option value="Good">
                            Good
                        </option>

                        <option value="Fair">
                            Fair
                        </option>

                    </select>


                </div>



                <!-- LOCATION -->

                <div class="input-box">


                    <label for="location">

                        Location

                    </label>


                    <input
                        type="text"
                        id="location"
                        name="location"
                        placeholder="Library / Hostel / Department"
                        required
                    >


                </div>


            </div>



            <!-- =================================================
                 IMAGE UPLOAD
                 ================================================= -->

            <div class="input-box">


                <label for="item_image">

                    Upload Item Image

                </label>


                <div class="image-upload-box">


                    <input
                        type="file"
                        id="item_image"
                        name="item_image"
                        accept="image/jpeg,image/jpg,image/png"
                        required
                    >


                    <p class="upload-help">

                        <i class="fa fa-image"></i>

                        Select JPG, JPEG or PNG image

                    </p>


                </div>


            </div>



            <!-- =================================================
                 IMAGE PREVIEW
                 ================================================= -->

            <div
                id="image-preview-container"
                class="image-preview-container"
                style="display: none;"
            >


                <p class="preview-title">

                    Image Preview

                </p>


                <img
                    id="image-preview"
                    src=""
                    alt="Selected Item Image"
                >


            </div>



            <!-- =================================================
                 SUBMIT BUTTON
                 ================================================= -->

            <button
                type="submit"
                class="add-item-button"
            >

                <i class="fa fa-upload"></i>

                Add Item

            </button>


        </form>


    </div>


</div>



<!-- =========================================================
     IMAGE PREVIEW JAVASCRIPT
     ========================================================= -->

<script>

const imageInput =
    document.getElementById("item_image");

const imagePreview =
    document.getElementById("image-preview");

const previewContainer =
    document.getElementById(
        "image-preview-container"
    );


imageInput.addEventListener(
    "change",
    function () {

        const file = this.files[0];


        if (!file) {

            previewContainer.style.display =
                "none";

            imagePreview.src = "";

            return;

        }


        /* Check image type */

        const allowedTypes = [
            "image/jpeg",
            "image/jpg",
            "image/png"
        ];


        if (
            !allowedTypes.includes(
                file.type
            )
        ) {

            alert(
                "Please select a JPG, JPEG or PNG image."
            );

            this.value = "";

            previewContainer.style.display =
                "none";

            return;

        }


        /* Check file size */

        const maxSize =
            5 * 1024 * 1024;


        if (file.size > maxSize) {

            alert(
                "Image size must be less than 5 MB."
            );

            this.value = "";

            previewContainer.style.display =
                "none";

            return;

        }


        /* Show preview */

        const reader =
            new FileReader();


        reader.onload =
            function (event) {

                imagePreview.src =
                    event.target.result;

                previewContainer.style.display =
                    "block";

            };


        reader.readAsDataURL(file);

    }
);

</script>


</body>

</html>