<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location:login.php");
    exit();
}

include("includes/db.php");

$user_id = $_SESSION['user_id'];

$full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
$phone = mysqli_real_escape_string($conn, $_POST['phone']);
$department = mysqli_real_escape_string($conn, $_POST['department']);
$year_of_study = mysqli_real_escape_string($conn, $_POST['year_of_study']);

/* Get current profile image */

$user_query = mysqli_query($conn, "SELECT profile_image FROM users WHERE user_id='$user_id'");
$user = mysqli_fetch_assoc($user_query);

$profile_image = $user['profile_image'];

/* Upload new image */

if(isset($_FILES['profile_image']) && $_FILES['profile_image']['error']==0)
{
    if(!is_dir("uploads/profile"))
    {
        mkdir("uploads/profile",0777,true);
    }

    $filename = time()."_".basename($_FILES['profile_image']['name']);
    $target = "uploads/profile/".$filename;

    if(move_uploaded_file($_FILES['profile_image']['tmp_name'],$target))
    {
        if(!empty($profile_image) && file_exists("uploads/profile/".$profile_image))
        {
            unlink("uploads/profile/".$profile_image);
        }

        $profile_image = $filename;
    }
}

/* Update user */

$sql = "UPDATE users SET
full_name='$full_name',
phone='$phone',
department='$department',
year_of_study='$year_of_study',
profile_image='$profile_image'
WHERE user_id='$user_id'";

if(mysqli_query($conn,$sql))
{
    echo "<script>
    alert('Profile updated successfully.');
    window.location='profile.php';
    </script>";
}
else
{
    echo "<script>
    alert('Failed to update profile.');
    window.history.back();
    </script>";
}
?>