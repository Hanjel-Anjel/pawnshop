<?php
@include 'config.php';
require 'send_email.php'; // Include PHPMailer configuration
session_start();

if (isset($_POST['submit'])) {
   $firstname = mysqli_real_escape_string($conn, $_POST['firstname']);
   $middleinitial = mysqli_real_escape_string($conn, $_POST['middleinitial']);
   $lastname = mysqli_real_escape_string($conn, $_POST['lastname']);
   $email = mysqli_real_escape_string($conn, $_POST['email']);
   $password = md5($_POST['password']);
   $cpassword = md5($_POST['cpassword']);
   $user_type = $_POST['user_type'];

   if (!preg_match('/^[a-zA-Z0-9._%+-]+@gmail\.com$/', $email)) {
      $error[] = 'Only Gmail addresses are allowed.';
   } else {
      $select = "SELECT * FROM users WHERE email = '$email'";
      $result = mysqli_query($conn, $select);

      if (mysqli_num_rows($result) > 0) {
         $error[] = 'User already exists!';
      } else {
         if ($password != $cpassword) {
            $error[] = 'Passwords do not match!';
         } else {
            $verification_code = rand(100000, 999999); // Generate 6-digit code
            $expiry_time = date("Y-m-d H:i:s", strtotime('+1 hour')); // Code valid for 1 hour

            // Store details in session temporarily
            $_SESSION['temp_user'] = [
               'first_name' => $firstname,
               'middle_initial' => $middleinitial,
               'last_name' => $lastname,
               'email' => $email,
               'password' => $password,
               'user_type' => $user_type,
               'verification_code' => $verification_code,
               'verification_expiry' => $expiry_time,
            ];

            // Send verification email
            $subject = "Email Verification Code";
            $body = "Hello $firstname,<br><br>
                     Use the following verification code to complete your registration:<br>
                     <h3>$verification_code</h3><br>
                     This code will expire in 1 hour.<br><br>
                     Thank you.";
            $email_sent = sendEmail($email, $subject, $body);

            if ($email_sent === true) {
               header('Location: verify_email.php'); // Redirect to verification page
               exit();
            } else {
               $error[] = "Failed to send verification email: $email_sent";
            }
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

         <?php 
            include('style.css');
        ?>

      .password-container {
         position: relative;
      }
      .password-container input[type="password"],
      .password-container input[type="text"] {
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
   </style>
</head>
<body>
<div class="form-container">
   <form action="" method="post" onsubmit="return validateForm()">
      <h3>Register Now</h3>
      <?php if (isset($error)) foreach ($error as $msg) echo '<span class="error-msg">' . htmlspecialchars($msg) . '</span>'; ?>
      <input type="text" name="firstname" style="text-transform:capitalize;" required placeholder="First Name">
      <input type="text" name="middleinitial" style="text-transform:capitalize;" required placeholder="Middle Initial">
      <input type="text" name="lastname" style="text-transform:capitalize;" required placeholder="Last Name">
      <input type="email" name="email" required placeholder="Enter your email" pattern="[a-zA-Z0-9._%+-]+@gmail\.com" title="Please enter a valid Gmail address (e.g., example@gmail.com)">
      <div class="password-container">
         <input type="password" id="password" name="password" required placeholder="Enter your password" minlength="8">
         <span class="toggle-eye" onclick="togglePassword('password', 'eye-icon1')">
            <img id="eye-icon1" src="./image/show.png" alt="Show Password">
         </span>
      </div>
      <div class="password-container">
         <input type="password" id="cpassword" name="cpassword" required placeholder="Confirm your password" minlength="8">
         <span class="toggle-eye" onclick="togglePassword('cpassword', 'eye-icon2')">
            <img id="eye-icon2" src="./image/show.png" alt="Show Password">
         </span>
      </div>
      <select name="user_type">
         <option value="employee">Employee</option>
         <!-- <option value="admin">Admin</option> -->
      </select>
      <input type="submit" name="submit" value="Register Now" class="form-btn">
      <p>Already have an account? <a href="login_form.php">Login Now</a></p>
   </form>
</div>
<script>
function togglePassword(fieldId, eyeIconId) {
   const passwordField = document.getElementById(fieldId);
   const eyeIcon = document.getElementById(eyeIconId);
   if (passwordField.type === "password") {
      passwordField.type = "text";
      eyeIcon.src = "./image/hide.png";
      eyeIcon.alt = "Hide Password";
   } else {
      passwordField.type = "password";
      eyeIcon.src = "./image/show.png";
      eyeIcon.alt = "Show Password";
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
</script>
</body>
</html>
