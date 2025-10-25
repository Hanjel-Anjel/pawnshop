<?php
@include 'config.php';
session_start();

if (!isset($_SESSION['temp_user'])) {
   header('location:register.php');
   exit();
}

if (isset($_POST['verify'])) {
   $entered_code = intval($_POST['verification_code']);
   $temp_user = $_SESSION['temp_user'];

   if ($entered_code == $temp_user['verification_code']) {
      if (strtotime($temp_user['verification_expiry']) > time()) {
         // Insert user into the database
         $insert_query = "INSERT INTO users 
                          (first_name, middle_initial, last_name, email, password, user_type, is_verified) 
                          VALUES 
                          ('{$temp_user['first_name']}', '{$temp_user['middle_initial']}', '{$temp_user['last_name']}', '{$temp_user['email']}', '{$temp_user['password']}', '{$temp_user['user_type']}', 1)";

         if (mysqli_query($conn, $insert_query)) {
            unset($_SESSION['temp_user']); // Clear temp session data
            header('location:login_form.php');
            exit();
         } else {
            $error_message = "Failed to save account. Error: " . mysqli_error($conn);
         }
      } else {
         $error_message = "Verification code expired. Please register again.";
      }
   } else {
      $error_message = "Incorrect verification code.";
   }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Verify Email</title>
   <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="form-container">
   <form action="" method="post">
      <h3>Verify Email</h3>
      <?php if (isset($error_message)) echo "<span class='error-msg'>$error_message</span>"; ?>
      <input type="text" name="verification_code" placeholder="Enter verification code" required>
      <button type="submit" name="verify" class="form-btn">Verify</button>
   </form>
</div>
</body>
</html>
