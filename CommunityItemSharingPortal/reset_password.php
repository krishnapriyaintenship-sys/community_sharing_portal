<?php
session_start();

if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password | CampusShare</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>

body{
    margin:0;
    padding:0;
    background:#f5f7fa;
    font-family:Arial,sans-serif;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
}

.container{
    width:420px;
    background:#fff;
    padding:30px;
    border-radius:10px;
    box-shadow:0 5px 15px rgba(0,0,0,.15);
}

.container img{
    display:block;
    margin:auto;
    width:80px;
}

h2{
    text-align:center;
    margin:15px 0;
}

input{
    width:100%;
    padding:12px;
    margin:12px 0;
    border:1px solid #ccc;
    border-radius:5px;
    box-sizing:border-box;
}

button{
    width:100%;
    padding:12px;
    border:none;
    background:#0d6efd;
    color:white;
    font-size:16px;
    border-radius:5px;
    cursor:pointer;
}

button:hover{
    background:#0b5ed7;
}

a{
    display:block;
    text-align:center;
    margin-top:15px;
    text-decoration:none;
    color:#0d6efd;
}

</style>

</head>
<body>

<div class="container">

<img src="images/logo.png">

<h2>Reset Password</h2>

<form action="reset_password_process.php" method="POST">

<input
type="password"
name="new_password"
placeholder="New Password"
required>

<input
type="password"
name="confirm_password"
placeholder="Confirm Password"
required>

<button type="submit">
<i class="fa fa-key"></i> Reset Password
</button>

</form>

<a href="login.php">
Back to Login
</a>

</div>

</body>
</html>