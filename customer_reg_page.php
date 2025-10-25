<?php
@include 'config.php';
require 'send_email.php'; // Ensure PHPMailer is set up

session_start();

if (isset($_POST['submit'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Secure hashing
    $cpassword = $_POST['cpassword'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error[] = 'Invalid email address.';
    } elseif ($_POST['password'] !== $cpassword) {
        $error[] = 'Passwords do not match!';
    } else {
        $check_user_query = "SELECT * FROM customer_users WHERE username='$username' OR email='$email'";
        $result = mysqli_query($conn, $check_user_query);

        if (mysqli_num_rows($result) > 0) {
            $error[] = 'Username or email already exists!';
        } else {
            // Email verification setup
            $verification_code = rand(100000, 999999);
            $expiry_time = date("Y-m-d H:i:s", strtotime('+1 hour'));

            // Store data temporarily in session
            $_SESSION['temp_user'] = [
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'verification_code' => $verification_code,
                'verification_expiry' => $expiry_time,
            ];

            // Send email with verification code
            $subject = "Email Verification Code";
            $body = "Hi $username,<br><br>
                     Use the following verification code to complete your registration:<br>
                     <h3>$verification_code</h3><br>
                     This code is valid for 1 hour.<br><br>
                     Thank you!";
            $email_sent = sendEmail($email, $subject, $body);

            if ($email_sent === true) {
                header('Location: customer_email_verify.php'); // Redirect to verification page
                exit();
            } else {
                $error[] = "Failed to send verification email: $email_sent";
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Form</title>
    <style>
        <?php include('style.css'); ?>
        .password-container {
            position: relative;
        }
        .password-container input {
            padding-right: 40px;
        }
        .toggle-eye {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
        }
        .toggle-eye img {
            width: 24px;
            height: 24px;
        }
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 20px;
            border-radius: 10px;
            width: 70%;
            max-height: 70vh;
            overflow-y: auto;
            text-align: left;
        }
        .close {
            float: right;
            font-size: 20px;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="form-container">
    <form action="" method="post" onsubmit="return validateForm()">
        <h3>Register Now</h3>
        <?php if (isset($error)) foreach ($error as $msg) echo '<span class="error-msg">' . htmlspecialchars($msg) . '</span>'; ?>
        <input type="text" name="username" required placeholder="Username" minlength="3">
        <input type="email" name="email" required placeholder="Email Address">
        <div class="password-container">
            <input type="password" id="password" name="password" required placeholder="Enter Password" minlength="8">
            <span class="toggle-eye" onclick="togglePassword('password', 'eye-icon1')">
                <img id="eye-icon1" src="./image/show.png" alt="Show Password">
            </span>
        </div>
        <div class="password-container">
            <input type="password" id="cpassword" name="cpassword" required placeholder="Confirm Password" minlength="8">
            <span class="toggle-eye" onclick="togglePassword('cpassword', 'eye-icon2')">
                <img id="eye-icon2" src="./image/show.png" alt="Show Password">
            </span>
        </div>

        <label>
            <input type="checkbox" id="agreeTerms" onclick="toggleSubmitButton()"> I agree to the 
            <a href="#" onclick="openModal()">Terms and Conditions</a>
        </label>

        <input type="submit" name="submit" value="Register Now" class="form-btn" id="submitBtn" disabled>
        <p>Already have an account? <a href="customer_login_page.php">Login Now</a></p>
    </form>
</div>

<!-- Terms and Conditions Modal -->
<div id="termsModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3>Terms and Conditions</h3>
        <p>Welcome to ArMaTech Pawnshop Management System. By registering for an account, you agree to comply with the following terms and conditions. Please read them carefully before proceeding.</p>

        <h4>1. Account Registration</h4>
        <p>1.1. You must provide accurate, complete, and up-to-date information during the registration process.</p>
        <p>1.2. You are responsible for maintaining the confidentiality of your account credentials and agree not to share them with third parties.</p>
        <p>1.3. You must be at least 18 years old to register for an account.</p>
        <p>1.4. The pawnshop reserves the right to verify your identity before approving account registration.</p>

        <h4>2. Use of the System</h4>
        <p>2.1. Your account is for personal use or authorized business use only.</p>
        <p>2.2. Any unauthorized access, modification, or misuse of the system is strictly prohibited.</p>
        <p>2.3. You agree to use the system only for lawful purposes related to pawn transactions and management.</p>

        <h4>3. Data Privacy and Security</h4>
        <p>3.1. Your personal and transaction data will be stored securely and used only for pawnshop-related activities.</p>
        <p>3.2. The pawnshop may collect and process your data in accordance with our Privacy Policy.</p>
        <p>3.3. You agree not to attempt to access or extract data that does not belong to you.</p>

        <h4>4. Liability and Disclaimers</h4>
        <p>4.1. The pawnshop is not responsible for losses resulting from unauthorized access due to your negligence.</p>

        <h4>5. Amendments to Terms</h4>
        <p>5.1. The pawnshop reserves the right to modify these terms at any time.</p>
        <p>5.2. Any changes will be communicated through the system, and continued use of your account constitutes acceptance of the updated terms.</p>

        <p>By registering an account, you confirm that you have read, understood, and agreed to these Terms and Conditions.</p>
        <p>For inquiries, contact ArMaTech@gmail.com</p>

        <p><strong>ArMaTech Pawnshop Management System</strong></p>
    </div>
</div>

<script>
    function togglePassword(fieldId, eyeIconId) {
        const passwordField = document.getElementById(fieldId);
        const eyeIcon = document.getElementById(eyeIconId);
        if (passwordField.type === "password") {
            passwordField.type = "text";
            eyeIcon.src = "./image/hide.png";
        } else {
            passwordField.type = "password";
            eyeIcon.src = "./image/show.png";
        }
    }

    function validateForm() {
        const password = document.getElementById("password").value;
        const confirmPassword = document.getElementById("cpassword").value;
        if (password !== confirmPassword) {
            alert("Passwords do not match!");
            return false;
        }
        return true;
    }

    function openModal() {
        document.getElementById("termsModal").style.display = "block";
    }

    function closeModal() {
        document.getElementById("termsModal").style.display = "none";
    }

    function toggleSubmitButton() {
        document.getElementById("submitBtn").disabled = !document.getElementById("agreeTerms").checked;
    }

    // Close modal if clicked outside
    window.onclick = function(event) {
        let modal = document.getElementById("termsModal");
        if (event.target === modal) {
            modal.style.display = "none";
        }
    }
</script>

</body>
</html>
