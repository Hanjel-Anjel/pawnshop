<?php
@include 'config.php';

session_start();

if (isset($_POST['submit'])) {
    // Get the email and password from POST data
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = md5($_POST['password']); // Hash the entered password using md5()

    // Validate email to ensure it's a Gmail address
    if (!preg_match('/^[a-zA-Z0-9._%+-]+@gmail\.com$/', $email)) {
        $error[] = 'Only Gmail addresses are allowed.';
    } else {
        // Prepare a statement to prevent SQL injection
        $query = $conn->prepare("SELECT * FROM users WHERE email = ? AND password = ?");
        $query->bind_param("ss", $email, $password); // Bind both email and password
        $query->execute();
        $result = $query->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            // Store session data dynamically
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['user_name'] = $row['first_name'];
            $_SESSION['user_type'] = $row['user_type'];

            // Redirect based on user type
            if ($row['user_type'] == 'admin') {
                header('location:Home.php');
            } elseif ($row['user_type'] == 'employee') {
                header('location:home_employee.php');
            } else {
                header('location:dashboard.php'); // Redirect to a general dashboard if needed
            }
        } else {
            $error[] = 'Incorrect email or password!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin/Employee Login - ArMaTech Pawnshop</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Poppins', sans-serif;
            color: white;
            overflow-x: hidden;
        }

        /* Background matching landing page */
        .login-wrapper {
            position: relative;
            background: url('./image/bg_pic.jpg') no-repeat center center/cover;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        /* Dark overlay */
        .login-wrapper::before {
            content: "";
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 0;
        }

        /* Back button */
        .back-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            z-index: 2;
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: #ffc107;
            font-weight: 600;
            padding: 10px 25px;
            border-radius: 30px;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .back-btn:hover {
            background-color: #ffc107;
            color: #000;
        }

        /* Glassmorphic form container */
        .form-container {
            position: relative;
            z-index: 1;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 50px 40px;
            backdrop-filter: blur(10px);
            width: 100%;
            max-width: 450px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            animation: fadeIn 1s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-container h3 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #ffc107;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.7);
            margin-bottom: 10px;
            text-align: center;
        }

        .form-container .subtitle {
            text-align: center;
            color: #eee;
            font-size: 0.95rem;
            margin-bottom: 30px;
        }

        .form-container .badge-container {
            text-align: center;
            margin-bottom: 25px;
        }

        .form-container .badge-admin {
            background: linear-gradient(135deg, #dc3545, #c82333);
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            box-shadow: 0 3px 10px rgba(220, 53, 69, 0.4);
            display: inline-block;
        }

        /* Error messages */
        .error-msg {
            display: block;
            background: rgba(220, 53, 69, 0.9);
            color: white;
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #dc3545;
            animation: shake 0.4s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        /* Input groups */
        .input-group-custom {
            margin-bottom: 20px;
            position: relative;
        }

        .input-group-custom label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #ffc107;
            margin-bottom: 8px;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5);
        }

        .input-group-custom input {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            font-size: 15px;
            font-family: 'Poppins', sans-serif;
            background: rgba(255, 255, 255, 0.15);
            color: white;
            transition: all 0.3s ease;
        }

        .input-group-custom input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .input-group-custom input:focus {
            outline: none;
            border-color: #ffc107;
            background: rgba(255, 255, 255, 0.2);
            box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.2);
        }

        /* Password container */
        .password-container {
            position: relative;
        }

        .password-container input {
            padding-right: 50px;
        }

        .toggle-eye {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #ffc107;
            font-size: 18px;
            transition: all 0.3s ease;
        }

        .toggle-eye:hover {
            color: #e0a800;
        }

        /* Forgot password link */
        .forgot-password {
            text-align: right;
            margin-top: 10px;
        }

        .forgot-password a {
            color: #ffc107;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .forgot-password a:hover {
            color: #e0a800;
            text-decoration: underline;
        }

        /* Submit button matching landing page */
        .form-btn {
            width: 100%;
            padding: 14px;
            border-radius: 30px;
            font-weight: 600;
            text-transform: uppercase;
            color: #fff;
            background: linear-gradient(135deg, #ffc107, #e0a800);
            border: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 193, 7, 0.4);
            cursor: pointer;
            font-size: 16px;
            margin-top: 30px;
        }

        .form-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(255, 193, 7, 0.5);
        }

        .form-btn:active {
            transform: translateY(-1px);
        }

        /* Register link */
        .register-link {
            text-align: center;
            margin-top: 25px;
            font-size: 14px;
            color: #eee;
        }

        .register-link a {
            color: #ffc107;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .register-link a:hover {
            color: #e0a800;
            text-decoration: underline;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .form-container {
                padding: 40px 30px;
            }

            .form-container h3 {
                font-size: 2rem;
            }

            .back-btn {
                padding: 8px 20px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <a href="index.php" class="back-btn">
            <i class="fas fa-arrow-left me-2"></i>Back to Home
        </a>
        
        <div class="form-container">
            <form action="" method="post" autocomplete="off">
                <h3><i class="fas fa-user-shield me-2"></i>Staff Login</h3>
                <p class="subtitle">Admin & Employee Access Portal</p>
                
                <div class="badge-container">
                    <span class="badge-admin">
                        <i class="fas fa-lock me-1"></i>Authorized Personnel Only
                    </span>
                </div>
                
                <?php
                // Display error messages
                if (isset($error)) {
                    foreach ($error as $msg) {
                        echo '<span class="error-msg"><i class="fas fa-exclamation-circle me-2"></i>' . htmlspecialchars($msg) . '</span>';
                    }
                }
                ?>
                
                <div class="input-group-custom">
                    <label for="email"><i class="fas fa-envelope me-2"></i>Email Address</label>
                    <input type="email" id="email" name="email" required placeholder="your.email@gmail.com"
                           pattern="[a-zA-Z0-9._%+-]+@gmail\.com"
                           title="Please enter a valid Gmail address (e.g., example@gmail.com)">
                </div>
                
                <div class="input-group-custom">
                    <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
                    <div class="password-container">
                        <input type="password" id="password" name="password" required placeholder="Enter your password">
                        <span class="toggle-eye" onclick="togglePasswordVisibility()">
                            <i class="fas fa-eye" id="eye-icon"></i>
                        </span>
                    </div>
                </div>
                
                <div class="forgot-password">
                    <a href="forgot_password.php"><i class="fas fa-key me-1"></i>Forgot Password?</a>
                </div>
                
                <input type="submit" name="submit" value="Login Now" class="form-btn">
                
                <p class="register-link">
                    Don't have an account? <a href="register_form.php">Register now</a>
                </p>
            </form>
        </div>
    </div>

    <script>
        // JavaScript Validation for Gmail addresses
        document.querySelector('[name="email"]').addEventListener('input', function() {
            const emailField = this;
            const emailPattern = /^[a-zA-Z0-9._%+-]+@gmail\.com$/;
            if (emailPattern.test(emailField.value)) {
                emailField.setCustomValidity('');
            } else {
                emailField.setCustomValidity('Only Gmail addresses are allowed.');
            }
        });

        // Toggle password visibility
        function togglePasswordVisibility() {
            const passwordField = document.getElementById("password");
            const eyeIcon = document.getElementById("eye-icon");
            
            if (passwordField.type === "password") {
                passwordField.type = "text";
                eyeIcon.classList.remove("fa-eye");
                eyeIcon.classList.add("fa-eye-slash");
            } else {
                passwordField.type = "password";
                eyeIcon.classList.remove("fa-eye-slash");
                eyeIcon.classList.add("fa-eye");
            }
        }
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>