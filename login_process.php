<?php

session_start();

require_once "includes/db.php";
require_once "includes/mail.php";


/* =========================================================
   CHECK REQUEST METHOD
   ========================================================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: login.php");
    exit();

}


/* =========================================================
   GET LOGIN DATA
   ========================================================= */

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';


/* =========================================================
   VALIDATION
   ========================================================= */

if (empty($email) || empty($password)) {

    header(
        "Location: login.php?error=" .
        urlencode("Please enter email and password.")
    );

    exit();

}


/* =========================================================
   1. CHECK ADMIN LOGIN
   ========================================================= */

$admin_sql = "
    SELECT
        admin_id,
        email,
        password
    FROM admins
    WHERE email = ?
    LIMIT 1
";

$admin_stmt = mysqli_prepare($conn, $admin_sql);


if (!$admin_stmt) {

    header(
        "Location: login.php?error=" .
        urlencode("Database error. Please try again.")
    );

    exit();

}


mysqli_stmt_bind_param(
    $admin_stmt,
    "s",
    $email
);

mysqli_stmt_execute($admin_stmt);

$admin_result = mysqli_stmt_get_result($admin_stmt);

$admin = mysqli_fetch_assoc($admin_result);

mysqli_stmt_close($admin_stmt);


/* =========================================================
   IF ADMIN LOGIN IS CORRECT
   ========================================================= */

if ($admin && password_verify($password, $admin['password'])) {

    /*
       Create a new session ID for security
    */
    session_regenerate_id(true);


    /*
       Remove normal user session data
    */
    unset(
        $_SESSION['user_id'],
        $_SESSION['full_name'],
        $_SESSION['email'],
        $_SESSION['profile_image'],
        $_SESSION['user_role']
    );


    /*
       Store admin session data
    */
    $_SESSION['admin_id'] = $admin['admin_id'];

    $_SESSION['admin_email'] = $admin['email'];

    $_SESSION['admin_role'] = 'admin';


    /*
       Redirect Admin to Admin Dashboard
    */
    header("Location: admin/dashboard.php");

    exit();

}


/* =========================================================
   2. CHECK NORMAL USER LOGIN
   ========================================================= */

$user_sql = "
    SELECT
        user_id,
        full_name,
        email,
        password,
        profile_image
    FROM users
    WHERE email = ?
    LIMIT 1
";

$user_stmt = mysqli_prepare($conn, $user_sql);


if (!$user_stmt) {

    header(
        "Location: login.php?error=" .
        urlencode("Database error. Please try again.")
    );

    exit();

}


mysqli_stmt_bind_param(
    $user_stmt,
    "s",
    $email
);

mysqli_stmt_execute($user_stmt);

$user_result = mysqli_stmt_get_result($user_stmt);

$user = mysqli_fetch_assoc($user_result);

mysqli_stmt_close($user_stmt);


/* =========================================================
   INVALID USERNAME / PASSWORD
   ========================================================= */

if (!$user || !password_verify($password, $user['password'])) {

    header(
        "Location: login.php?error=" .
        urlencode("Invalid email or password.")
    );

    exit();

}


/* =========================================================
   USER LOGIN SUCCESS
   ========================================================= */

session_regenerate_id(true);


/*
   Remove admin session data
*/
unset(
    $_SESSION['admin_id'],
    $_SESSION['admin_email'],
    $_SESSION['admin_role']
);


/*
   Store normal user session data
*/

$_SESSION['user_id'] = $user['user_id'];

$_SESSION['full_name'] = $user['full_name'];

$_SESSION['email'] = $user['email'];

$_SESSION['profile_image'] = $user['profile_image'];

$_SESSION['user_role'] = 'user';


/* =========================================================
   SEND LOGIN SUCCESS EMAIL
   ========================================================= */

$login_email_body = "

<h2>Login Successful</h2>

<p>
    Hello <strong>" .
    htmlspecialchars($user['full_name']) .
    "</strong>,
</p>

<p>
    You have successfully logged in to the
    <strong>CampusShare</strong> portal.
</p>

<p>
    If this login was not performed by you,
    please contact the administrator.
</p>

<br>

<p>
    Regards,<br>
    <strong>CampusShare Team</strong>
</p>

";


/*
   Email failure should NOT stop login.
*/
sendEmail(
    $user['email'],
    $user['full_name'],
    "Login Successful - CampusShare",
    $login_email_body
);


/* =========================================================
   REDIRECT USER TO USER DASHBOARD
   ========================================================= */

header("Location: dashboard.php");

exit();

?>