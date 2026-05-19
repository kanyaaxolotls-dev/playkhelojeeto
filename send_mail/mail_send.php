<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer classes manually
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com'; // SMTP server
    $mail->SMTPAuth   = true;
    $mail->Username   = 'newpunecarpets@gmail.com'; // Your Gmail
    $mail->Password   = 'ruvwqovyqesyjjoi';   // App Password (not your Gmail password)
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    // Sender and recipient
    $mail->setFrom('newpunecarpets@gmail.com', 'Your Name');
    $mail->addAddress('snehal.axolotls@gmail.com', 'Recipient Name');

    // Email content
    $mail->isHTML(true);
    $mail->Subject = 'Manual PHPMailer Test';
    $mail->Body    = '<b>Hello!</b> This is a test email using PHPMailer manually.';

    $mail->send();
    echo 'Message has been sent.';
} catch (Exception $e) {
    echo "Mailer Error: {$mail->ErrorInfo}";
}
?>
