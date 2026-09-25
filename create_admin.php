<?php

$password = "Admin@12345";

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

echo "Hashed Password:<br><br>";
echo $hashed_password;

?>