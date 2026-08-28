<?php
session_start();
include("includes/db.php");

if(isset($_POST['full_name'])){

    $full_name = mysqli_real_escape_string($conn,$_POST['full_name']);
    $email = mysqli_real_escape_string($conn,$_POST['email']);
    $phone = mysqli_real_escape_string($conn,$_POST['phone']);
    $department = mysqli_real_escape_string($conn,$_POST['department']);
    $year = mysqli_real_escape_string($conn,$_POST['year']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Password Match

    if($password != $confirm_password){

        echo "<script>
        alert('Passwords do not match');
        window.location='register.php';
        </script>";
        exit();

    }

    // Check Email

    $check = mysqli_query($conn,"SELECT * FROM users WHERE email='$email'");

    if(mysqli_num_rows($check)>0){

        echo "<script>
        alert('Email already registered');
        window.location='register.php';
        </script>";
        exit();

    }

    // Password Hash

    $hashed_password = password_hash($password,PASSWORD_DEFAULT);

    // Image Upload

    $image = "default.png";

    if($_FILES['profile_image']['name']!=""){

        $image = time()."_".$_FILES['profile_image']['name'];

        move_uploaded_file(
            $_FILES['profile_image']['tmp_name'],
            "uploads/".$image
        );

    }

    // Insert User

    $sql = "INSERT INTO users
    (
        full_name,
        email,
        phone,
        department,
        year_of_study,
        password,
        profile_image
    )

    VALUES

    (
        '$full_name',
        '$email',
        '$phone',
        '$department',
        '$year',
        '$hashed_password',
        '$image'
    )";

    if(mysqli_query($conn,$sql)){

        echo "<script>

        alert('Registration Successful');

        window.location='login.php';

        </script>";

    }else{

        echo "<script>

        alert('Registration Failed');

        window.location='register.php';

        </script>";

    }

}
?>