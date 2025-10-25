<?php
@include 'config.php';
require 'send_email.php'; // Include PHPMailer configuration
session_start();

// Set the correct time zone
date_default_timezone_set('Asia/Manila'); // Philippine Time Zone

if (isset($_POST['submit'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    // Check if the email exists in the database
    $user_check = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
    $admin_check = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");

    if (mysqli_num_rows($user_check) > 0 || mysqli_num_rows($admin_check) > 0) {
        $verification_code = rand(100000, 999999); // Generate a 6-digit code
        $expiry_time = date("Y-m-d H:i:s", strtotime('+1 hour')); // Code expires in 1 hour

        // Set MySQL session time zone
        mysqli_query($conn, "SET time_zone = '+08:00'");

        // Update the verification code and expiry in the database
        $update_query = "UPDATE users SET reset_token = '$verification_code', token_expiry = '$expiry_time' WHERE email = '$email'";
        mysqli_query($conn, $update_query);

        // Prepare the email content
        $subject = "Password Reset Verification Code";
        $body = "Hello,<br><br>
                 We received a request to reset your password. Use the verification code below to reset it:<br>
                 <h3>$verification_code</h3><br>
                 This code will expire in 1 hour.<br><br>
                 If you did not request a password reset, please ignore this email.";

        // Send the email using PHPMailer
        $result = sendEmail($email, $subject, $body);

        if ($result === true) {
            $_SESSION['email'] = $email; // Store email for the verify_code.php page
            header('Location: verify_code.php');
            exit();
        } else {
            $error_message = "Error sending email: " . $result;
        }
    } else {
        $error_message = "Email address not found.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="mt-auto">
        <a href="login_form.php" class="btn btn-primary">Back</a>
    </div> 
    <div class="container mt-5">
        <h2 class="mb-4">Forgot Password</h2>
        <?php if (isset($success_message)) { echo "<div class='alert alert-success'>$success_message</div>"; } ?>
        <?php if (isset($error_message)) { echo "<div class='alert alert-danger'>$error_message</div>"; } ?>
        <form action="" method="post" class="p-4 border rounded">
            <div class="mb-3">
                <label for="email" class="form-label">Enter your email:</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <button type="submit" name="submit" class="btn btn-primary">Send Verification Code</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
