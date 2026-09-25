<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer
require_once __DIR__ . '/../vendor/autoload.php';

// Load email configuration
require_once __DIR__ . '/mail_config.php';


function sendEmail($toEmail, $toName, $subject, $body)
{
    $mail = new PHPMailer(true);

    try {

        // Gmail SMTP settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;

        // Encryption
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Sender
        $mail->setFrom(
            MAIL_FROM_EMAIL,
            MAIL_FROM_NAME
        );

        // Receiver
        $mail->addAddress($toEmail, $toName);

        // Email format
        $mail->isHTML(true);

        // Subject
        $mail->Subject = $subject;

        // Email content
        $mail->Body = $body;

        // Plain text version
        $mail->AltBody = strip_tags($body);

        // Send
        $mail->send();

        return true;

    } catch (Exception $e) {

        return false;
    }
}