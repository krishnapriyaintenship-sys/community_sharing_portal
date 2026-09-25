<?php

require_once 'includes/mail.php';

$to = 'krishnapriyaintenship@gmail.com';

$subject = 'CampusShare Email Test';

$body = '
<h2>CampusShare</h2>

<p>Hello!</p>

<p>This is a test email from the Community Item Sharing Portal.</p>

<p>If you received this email, PHPMailer is working correctly.</p>

<p>Thank you.</p>
';

if (sendEmail($to, 'Test User', $subject, $body)) {

    echo "Email sent successfully!";

} else {

    echo "Email sending failed.";
}