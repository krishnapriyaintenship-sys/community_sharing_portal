<?php
session_start();
include("includes/db.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Forgot Password | CampusShare</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>
body{
    margin:0;
    font-family:Arial,sans-serif;
    background:#f4f7fc;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
}

.box{
    width:400px;
    background:#fff;
    padding:30px;
    border-radius:10px;
    box-shadow:0 0 15px rgba(0,0,0,.15);
    text-align:center;
}

.box img{
    width:80px;
    margin-bottom:15px;
}

h2{
    margin-bottom:10px;
}

p{
    color:#666;
    font-size:14px;
    margin-bottom:20px;
}

input{
    width:100%;
    padding:12px;
    margin:10px 0;
    border:1px solid #ccc;
    border-radius:5px;
    box-sizing:border-box;
}

button{
    width:100%;
    padding:12px;
    border:none;
    background:#0d6efd;
    color:#fff;
    border-radius:5px;
    cursor:pointer;
    font-size:16px;
}

button:hover{
    background:#0b5ed7;
}

a{
    display:block;
    margin-top:15px;
    color:#0d6efd;
    text-decoration:none;
}
</style>

</head>
<body>

<div class="box">

<img src="images/logo.png">

<h2>Forgot Password</h2>

<p>Enter your registered email address. We will let you reset your password.</p>

<form action="forgot_password_process.php" method="POST">

<input
type="email"
name="email"
placeholder="Enter your email"
required>

<button type="submit">
<i class="fa fa-paper-plane"></i> Continue
</button>

</form>

<a href="login.php">
<i class="fa fa-arrow-left"></i> Back to Login
</a>

</div>

</body>
</html>