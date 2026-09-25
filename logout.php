<?php

session_start();

require_once "includes/db.php";
require_once "includes/mail.php";

/*
|--------------------------------------------------------------------------
| Get logged-in user's details BEFORE destroying session
|--------------------------------------------------------------------------
*/

$user_id = $_SESSION['user_id'] ?? 0;
$user_name = $_SESSION['full_name'] ?? '';
$user_email = $_SESSION['email'] ?? '';


/*
|--------------------------------------------------------------------------
| Send Logout Email
|--------------------------------------------------------------------------
*/

if (!empty($user_email)) {

    $subject = "Logout Successful - Community Item Sharing Portal";

    $body = "
        <div style='font-family: Arial, sans-serif;'>
            
            <h2 style='color:#2563eb;'>
                Logout Successful
            </h2>

            <p>
                Hello <strong>" . htmlspecialchars($user_name) . "</strong>,
            </p>

            <p>
                You have successfully logged out of the
                <strong>Community Item Sharing Portal</strong>.
            </p>

            <p>
                Your session has been securely ended.
            </p>

            <br>

            <p>
                Thank you for using our Community Item Sharing Portal.
            </p>

            <p>
                Regards,<br>
                <strong>Community Item Sharing Portal</strong>
            </p>

        </div>
    ";

    sendEmail(
        $user_email,
        $user_name,
        $subject,
        $body
    );
}


/*
|--------------------------------------------------------------------------
| Remove all session variables
|--------------------------------------------------------------------------
*/

$_SESSION = array();


/*
|--------------------------------------------------------------------------
| Destroy session
|--------------------------------------------------------------------------
*/

session_destroy();


/*
|--------------------------------------------------------------------------
| Prevent browser caching
|--------------------------------------------------------------------------
*/

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");


/*
|--------------------------------------------------------------------------
| Redirect to Login Page
|--------------------------------------------------------------------------
*/

header(
    "Location: login.php?success=" .
    urlencode("Logout successful.")
);

exit();

?>