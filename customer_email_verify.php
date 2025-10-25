<?php
@include 'config.php';
session_start();

// Redirect to the registration page if session data is missing
if (!isset($_SESSION['temp_user'])) {
    header('location:customer_reg_page.php');
    exit();
}

$temp_user = $_SESSION['temp_user'];

if (isset($_POST['verify'])) {
    $entered_code = intval($_POST['verification_code']); // Convert input to an integer

    if ($entered_code == $temp_user['verification_code']) {
        if (strtotime($temp_user['verification_expiry']) > time()) {
            // Check if the email is already registered
            $email_check_query = "SELECT * FROM customer_users WHERE email = '{$temp_user['email']}'";
            $email_check_result = mysqli_query($conn, $email_check_query);

            if (mysqli_num_rows($email_check_result) > 0) {
                $error_message = "This email is already registered. Please log in.";
            } else {
                // Insert into customer_users table
                $insert_query = "INSERT INTO customer_users (username, password, email, created_at) 
                                 VALUES ('{$temp_user['username']}', '{$temp_user['password']}', '{$temp_user['email']}', NOW())";

                if (mysqli_query($conn, $insert_query)) {
                    // Retrieve the newly created user's ID
                    $user_id = mysqli_insert_id($conn);
                    
                    // Store the user ID in the session
                    $_SESSION['user_id'] = $user_id;

                    // Redirect to profile setup without clearing the session
                    header('location:customer_profile_setup.php');
                    exit();
                } else {
                    $error_message = "Failed to save account. Error: " . mysqli_error($conn);
                }
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
            <?php if (isset($error_message)) echo "<span class='error-msg'>" . htmlspecialchars($error_message) . "</span>"; ?>
            <input type="text" name="verification_code" placeholder="Enter verification code" required>
            <button type="submit" name="verify" class="form-btn">Verify</button>
        </form>
    </div>
</body>

</html>
