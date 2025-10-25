<?php
@include 'config.php';
session_start();

if (!isset($_SESSION['verified_email'])) {
    // Redirect back to forgot_password.php if no verified email exists
    header('Location: forgot_password.php');
    exit();
}

if (isset($_POST['reset_password'])) {
    $new_password = md5($_POST['password']); // Hash the new password
    $email = $_SESSION['verified_email'];

    // Update the password and clear the reset token and expiry
    $query = "UPDATE users SET password = ?, reset_token = NULL, token_expiry = NULL WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ss', $new_password, $email);

    if ($stmt->execute()) {
        // Clear session and redirect to login
        unset($_SESSION['verified_email']);
        header('Location: login_form.php?success=Password reset successfully. Please log in.');
        exit();
    } else {
        $error_message = "Error resetting password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Reset Password</h2>
        <?php if (isset($error_message)) { echo "<div class='alert alert-danger'>$error_message</div>"; } ?>
        <form action="" method="post" class="p-4 border rounded">
            <div class="mb-3">
                <label for="password" class="form-label">New Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <button type="submit" name="reset_password" class="btn btn-primary">Reset Password</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
