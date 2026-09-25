<?php

session_start();

/*
|--------------------------------------------------------------------------
| Load Mail Function
|--------------------------------------------------------------------------
*/

require_once "../includes/mail.php";


/*
|--------------------------------------------------------------------------
| Get Admin Details BEFORE Removing Session
|--------------------------------------------------------------------------
*/

$admin_email = $_SESSION['admin_email'] ?? '';

$admin_name = "Administrator";


/*
|--------------------------------------------------------------------------
| Send Logout Email
|--------------------------------------------------------------------------
*/

if (!empty($admin_email)) {

    $subject = "Logout Successful - Community Item Sharing Portal";

    $body = "
        <div style='
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: auto;
            padding: 20px;
        '>

            <h2 style='color:#2563eb;'>
                Logout Successful
            </h2>

            <p>
                Hello <strong>Administrator</strong>,
            </p>

            <p>
                You have successfully logged out of the
                <strong>Community Item Sharing Portal</strong>.
            </p>

            <p>
                Your admin session has been securely ended.
            </p>

            <br>

            <p>
                Thank you for using the
                Community Item Sharing Portal.
            </p>

            <p>
                Regards,<br>
                <strong>Community Item Sharing Portal</strong>
            </p>

        </div>
    ";


    sendEmail(
        $admin_email,
        $admin_name,
        $subject,
        $body
    );
}


/*
|--------------------------------------------------------------------------
| Remove Admin Session
|--------------------------------------------------------------------------
*/

unset(
    $_SESSION['admin_id'],
    $_SESSION['admin_email'],
    $_SESSION['admin_role']
);


/*
|--------------------------------------------------------------------------
| Redirect to Main Login Page with Success Message
|--------------------------------------------------------------------------
*/

header(
    "Location: ../login.php?success=" .
    urlencode("Logout successful.")
);

exit();

?>