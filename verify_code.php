<?php
@include 'config.php';
session_start();

if (!isset($_SESSION['email'])) {
    // Redirect back to forgot_password.php if no email is stored
    header('Location: forgot_password.php');
    exit();
}

if (isset($_POST['verify'])) {
    $email = $_SESSION['email'];
    $verification_code = mysqli_real_escape_string($conn, $_POST['verification_code']);

    // Validate the verification code and expiry
    $query = "SELECT * FROM users WHERE email = ? AND reset_token = ? AND token_expiry > NOW()";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ss', $email, $verification_code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Code is valid, redirect to reset_password.php
        $_SESSION['verified_email'] = $email; // Store verified email for password reset
        header('Location: reset_password.php');
        exit();
    } else {
        $error_message = "Invalid or expired verification code.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Code</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Enter Verification Code</h2>
        <?php if (isset($error_message)) { echo "<div class='alert alert-danger'>$error_message</div>"; } ?>
        <form action="" method="post" class="p-4 border rounded">
            <div class="mb-3">
                <label for="verification_code" class="form-label">Verification Code</label>
                <input type="text" name="verification_code" id="verification_code" class="form-control" required>
            </div>
            <button type="submit" name="verify" class="btn btn-primary">Verify</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
