<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$mail = new PHPMailer(true);

try {
    // ✅ SMTP config — adjust to yours
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'azunadum20@gmail.com';
    $mail->Password = 'yvgyjtlkntmacfjp';
    $mail->SMTPSecure = 'tls'; // or 'ssl'
    $mail->Port = 587; // or 465

    // ✅ Email settings
    $mail->CharSet = 'UTF-8';   // Character set    

    $mail->setFrom('azunadum20@gmail.com', 'Training Impact System'); // Sender
    $mail->addAddress('dumindin.jireh2102@gmail.com', 'Jireh Dumindin');    // Recipient

    $mail->isHTML(true);
    $mail->Subject = 'Test Mail from Training Impact System';
    $mail->Body    = 'Hello, this is a test mail sent using PHPMailer & Gmail SMTP.';

    $mail->send();
    echo 'Message has been sent successfully!';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>
