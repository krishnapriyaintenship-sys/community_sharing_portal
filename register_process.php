<?php

session_start();

require_once "includes/db.php";
require_once "includes/mail.php";


/*
|--------------------------------------------------------------------------
| Only allow POST request
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: register.php");
    exit();

}


/*
|--------------------------------------------------------------------------
| Get Form Data
|--------------------------------------------------------------------------
*/

$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$department = trim($_POST['department'] ?? '');
$year = trim($_POST['year'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$terms = $_POST['terms'] ?? '';


/*
|--------------------------------------------------------------------------
| Basic Validation
|--------------------------------------------------------------------------
*/

if (
    $full_name === '' ||
    $email === '' ||
    $phone === '' ||
    $department === '' ||
    $year === '' ||
    $password === '' ||
    $confirm_password === ''
) {

    echo "<script>
        alert('Please fill in all required fields.');
        window.location='register.php';
    </script>";

    exit();

}


/*
|--------------------------------------------------------------------------
| Terms Validation
|--------------------------------------------------------------------------
*/

if ($terms !== '1') {

    echo "<script>
        alert('Please accept the Terms & Conditions.');
        window.location='register.php';
    </script>";

    exit();

}


/*
|--------------------------------------------------------------------------
| Name Validation
|--------------------------------------------------------------------------
*/

if (strlen($full_name) < 3 || strlen($full_name) > 100) {

    echo "<script>
        alert('Full name must contain between 3 and 100 characters.');
        window.location='register.php';
    </script>";

    exit();

}


/*
|--------------------------------------------------------------------------
| Email Validation
|--------------------------------------------------------------------------
*/

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    echo "<script>
        alert('Please enter a valid email address.');
        window.location='register.php';
    </script>";

    exit();

}


/*
|--------------------------------------------------------------------------
| Phone Validation
|--------------------------------------------------------------------------
*/

if (!preg_match('/^[0-9]{10}$/', $phone)) {

    echo "<script>
        alert('Phone number must contain exactly 10 digits.');
        window.location='register.php';
    </script>";

    exit();

}


/*
|--------------------------------------------------------------------------
| Password Validation
|--------------------------------------------------------------------------
*/

if (strlen($password) < 6) {

    echo "<script>
        alert('Password must contain at least 6 characters.');
        window.location='register.php';
    </script>";

    exit();

}


/*
|--------------------------------------------------------------------------
| Confirm Password
|--------------------------------------------------------------------------
*/

if ($password !== $confirm_password) {

    echo "<script>
        alert('Passwords do not match.');
        window.location='register.php';
    </script>";

    exit();

}


/*
|--------------------------------------------------------------------------
| Check Existing Email
|--------------------------------------------------------------------------
*/

$check_sql = "SELECT user_id FROM users WHERE email = ? LIMIT 1";

$check_stmt = mysqli_prepare($conn, $check_sql);

if (!$check_stmt) {

    die("Database error: " . mysqli_error($conn));

}

mysqli_stmt_bind_param(
    $check_stmt,
    "s",
    $email
);

mysqli_stmt_execute($check_stmt);

mysqli_stmt_store_result($check_stmt);

if (mysqli_stmt_num_rows($check_stmt) > 0) {

    mysqli_stmt_close($check_stmt);

    echo "<script>
        alert('This email is already registered.');
        window.location='register.php';
    </script>";

    exit();

}

mysqli_stmt_close($check_stmt);


/*
|--------------------------------------------------------------------------
| Password Hash
|--------------------------------------------------------------------------
*/

$hashed_password = password_hash(
    $password,
    PASSWORD_DEFAULT
);

if ($hashed_password === false) {

    echo "<script>
        alert('Unable to secure the password. Please try again.');
        window.location='register.php';
    </script>";

    exit();

}


/*
|--------------------------------------------------------------------------
| Profile Image
|--------------------------------------------------------------------------
*/

$image = "default.png";

if (
    isset($_FILES['profile_image']) &&
    $_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE
) {

    if ($_FILES['profile_image']['error'] !== UPLOAD_ERR_OK) {

        echo "<script>
            alert('There was a problem uploading the profile image.');
            window.location='register.php';
        </script>";

        exit();

    }


    /*
    | Maximum size = 2 MB
    */

    if ($_FILES['profile_image']['size'] > 2 * 1024 * 1024) {

        echo "<script>
            alert('Profile image must be less than 2 MB.');
            window.location='register.php';
        </script>";

        exit();

    }


    /*
    | Check actual image
    */

    $image_info = getimagesize(
        $_FILES['profile_image']['tmp_name']
    );

    if ($image_info === false) {

        echo "<script>
            alert('Please upload a valid image.');
            window.location='register.php';
        </script>";

        exit();

    }


    /*
    | Allowed image types
    */

    $allowed_types = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png'
    ];

    $mime_type = $image_info['mime'];

    if (!isset($allowed_types[$mime_type])) {

        echo "<script>
            alert('Only JPG, JPEG and PNG images are allowed.');
            window.location='register.php';
        </script>";

        exit();

    }


    /*
    | Create uploads directory if it doesn't exist
    */

    $upload_directory = __DIR__ . "/uploads/";

    if (!is_dir($upload_directory)) {

        mkdir(
            $upload_directory,
            0755,
            true
        );

    }


    /*
    | Generate unique filename
    */

    $extension = $allowed_types[$mime_type];

    $image = "profile_" .
             time() .
             "_" .
             bin2hex(random_bytes(5)) .
             "." .
             $extension;


    /*
    | Move uploaded image
    */

    if (
        !move_uploaded_file(
            $_FILES['profile_image']['tmp_name'],
            $upload_directory . $image
        )
    ) {

        echo "<script>
            alert('Unable to save profile image.');
            window.location='register.php';
        </script>";

        exit();

    }

}


/*
|--------------------------------------------------------------------------
| Insert User
|--------------------------------------------------------------------------
*/

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
VALUES (?, ?, ?, ?, ?, ?, ?)";


$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {

    echo "<script>
        alert('Database error. Registration failed.');
        window.location='register.php';
    </script>";

    exit();

}


mysqli_stmt_bind_param(
    $stmt,
    "sssssss",
    $full_name,
    $email,
    $phone,
    $department,
    $year,
    $hashed_password,
    $image
);


/*
|--------------------------------------------------------------------------
| Execute Insert
|--------------------------------------------------------------------------
*/

if (mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);


    /*
    |--------------------------------------------------------------------------
    | Send Registration Email
    |--------------------------------------------------------------------------
    */

    $email_subject = "Welcome to CampusShare";


    $email_body = "
    <!DOCTYPE html>

    <html>

    <head>
        <meta charset='UTF-8'>
        <title>Welcome to CampusShare</title>
    </head>

    <body style='font-family: Arial, sans-serif; background-color: #f5f5f5; padding: 30px;'>

        <div style='max-width: 600px; margin: auto; background: white; padding: 30px; border-radius: 10px;'>

            <h2 style='color: #333;'>Welcome to CampusShare!</h2>

            <p>Hello <strong>" . htmlspecialchars($full_name) . "</strong>,</p>

            <p>
                Your account has been successfully created in the
                <strong>Community Item Sharing Portal</strong>.
            </p>

            <p>
                You can now log in to your account and use the portal
                to share and borrow community items.
            </p>

            <p>
                <strong>Registered Email:</strong>
                " . htmlspecialchars($email) . "
            </p>

            <p style='margin-top: 30px;'>
                Thank you for joining CampusShare!
            </p>

            <p>
                <strong>CampusShare Team</strong>
            </p>

        </div>

    </body>

    </html>
    ";


    /*
    |--------------------------------------------------------------------------
    | Send Email
    |--------------------------------------------------------------------------
    */

    $email_sent = sendEmail(
        $email,
        $full_name,
        $email_subject,
        $email_body
    );


    /*
    |--------------------------------------------------------------------------
    | Registration Successful
    |--------------------------------------------------------------------------
    */

    if ($email_sent) {

        echo "<script>

            alert('Registration Successful! A confirmation email has been sent to your email address.');

            window.location='login.php';

        </script>";

    } else {

        echo "<script>

            alert('Registration Successful, but the confirmation email could not be sent. You can still login.');

            window.location='login.php';

        </script>";

    }

    exit();

}


/*
|--------------------------------------------------------------------------
| Registration Failed
|--------------------------------------------------------------------------
*/

mysqli_stmt_close($stmt);

echo "<script>

    alert('Registration Failed. Please try again.');

    window.location='register.php';

</script>";

exit();

?>