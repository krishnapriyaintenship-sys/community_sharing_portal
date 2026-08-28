<?php
include("includes/db.php");

if(isset($_POST['send']))
{
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    // Set to 0 for visitors who are not logged in
    $user_id = 0;

    $sql = "INSERT INTO contact_messages
            (user_id, name, email, subject, message)
            VALUES
            ('$user_id', '$name', '$email', '$subject', '$message')";

    if(mysqli_query($conn, $sql))
    {
        echo "<script>
                alert('Message sent successfully!');
                window.location='index.php';
              </script>";
    }
    else
    {
        echo "<script>
                alert('Failed to send message!');
                window.history.back();
              </script>";
    }
}
?>