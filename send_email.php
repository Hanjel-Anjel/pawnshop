<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Include Composer's autoloader

function sendEmail($to, $subject, $body) {
    $mail = new PHPMailer(true);
    // znfp vxaa igra wdax - the password in less secure app
    try {
        // Server settings
        $mail->isSMTP();                                     // Set mailer to use SMTP
        $mail->Host = 'smtp.gmail.com';                      // Specify main SMTP server
        $mail->SMTPAuth = true;                              // Enable SMTP authentication
        $mail->Username = 'ralparmario101@gmail.com';            // Your Gmail address
        $mail->Password = 'znfpvxaaigrawdax';               // Your Gmail app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  // Enable TLS encryption
        $mail->Port = 587;                                   // TCP port to connect to

        // Recipients
        $mail->setFrom('your-email@gmail.com', 'ArMaTech Pa-wnshop'); // Sender's email and name
        $mail->addAddress($to);                              // Add recipient's email

        // Content
        $mail->isHTML(true);  // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true; // Email sent successfully
    } catch (Exception $e) {
        return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>
