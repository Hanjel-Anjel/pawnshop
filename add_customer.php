<?php
include('config.php');

session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_name']) && !isset($_SESSION['user_name'])) {
    header('location:login_form.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $last_name = $_POST['last_name'];
    $first_name = $_POST['first_name'];
    $middle_initial = $_POST['middle_initial'];
    $gender = $_POST['gender'];
    $birthday = $_POST['birthday'];
    $email = $_POST['email'];
    $phone_no = $_POST['phone_no'];
    $address = $_POST['address'];

    // Validate email to ensure it's a Gmail address
    if (!preg_match('/^[a-zA-Z0-9._%+-]+@gmail\\.com$/', $email)) {
        $error_message = "Only Gmail addresses are allowed.";
    } elseif (!preg_match('/^(09\\d{9}|\\+639\\d{9})$/', $phone_no)) {
        // Validate Philippine mobile number
        $error_message = "Please enter a valid Philippine mobile number (e.g., 09171234567 or +639171234567).";
    } else {
        // Image upload handling
        $valid_id_image = null;
        if (isset($_FILES['valid_id_image']) && $_FILES['valid_id_image']['error'] === UPLOAD_ERR_OK) {
            $image_tmp_name = $_FILES['valid_id_image']['tmp_name'];
            $valid_id_image = file_get_contents($image_tmp_name);
        }

        // Basic input validation
        if (empty($last_name) || empty($first_name) || empty($email) || empty($phone_no) || empty($address) || empty($gender) || empty($birthday)) {
            $error_message = "All fields are required.";
        } else {
            // Prepare and execute the SQL query
            $sql = "INSERT INTO custumer_info (last_name, first_name, middle_initial, gender, birthday, email, phone_no, address, valid_id_image) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssss", $last_name, $first_name, $middle_initial, $gender, $birthday, $email, $phone_no, $address, $valid_id_image);

            if ($stmt->execute()) {
                if ($_SESSION['user_type'] == 'admin') {
                    header('Location: Customer_page.php');
                } else {
                    header('Location: customer_employee.php');
                }
                exit();
            } else {
                $error_message = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Customer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        input[type=text] {
            text-transform: capitalize;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card m-5 p-2">
            <div class="card-body">
                <h1>Add Customer</h1>

                <?php if (isset($error_message)) : ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($error_message) ?>
                    </div>
                <?php endif; ?>

                <form id="addCustomerForm" action="" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="last_name">Last Name:</label>
                        <input type="text" id="last_name" class="form-control" name="last_name" required><br>
                    </div>

                    <div class="form-group">
                        <label for="first_name">First Name:</label>
                        <input type="text" id="first_name" class="form-control" name="first_name" required><br>
                    </div>

                    <div class="form-group">
                        <label for="middle_initial">Middle Initial:</label>
                        <input type="text" id="middle_initial" class="form-control" name="middle_initial" maxlength="5"><br>
                    </div>

                    <div class="form-group">
                        <label for="gender">Gender:</label>
                        <select id="gender" class="form-control" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select><br>
                    </div>

                    <div class="form-group">
                        <label for="birthday">Birthday:</label>
                        <input type="date" id="birthday" class="form-control" name="birthday" required><br>
                    </div>

                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" class="form-control" name="email"
                            pattern="[a-zA-Z0-9._%+-]+@gmail\.com"
                            title="Please enter a valid Gmail address (e.g., example@gmail.com)" required><br>
                    </div>

                    <div class="form-group">
                        <label for="phone_no">Phone No.:</label>
                        <input type="tel" id="phone_no" class="form-control" name="phone_no"
                            pattern="^(09\d{9}|\+639\d{9})$"
                            title="Enter a valid Philippine mobile number (e.g., 09171234567 or +639171234567)"
                            maxlength="11" required
                            oninput="this.value = this.value.replace(/[^0-9+]/g, '').slice(0, 13);"><br>
                    </div>

                    <div class="form-group">
                        <label for="address">Address:</label>
                        <input type="text" id="address" class="form-control" name="address" required><br>
                    </div>

                    <div class="form-group">
                        <label for="valid_id_image">Valid ID Image:</label>
                        <input type="file" id="valid_id_image" class="form-control" name="valid_id_image"><br>
                    </div>

                    <input type="submit" class="btn btn-success" name="add_customer" value="Add">

                    <?php
                    if (isset($_SESSION['user_type'])) {
                        $cancel_redirect = ($_SESSION['user_type'] == 'admin') ? 'Customer_page.php' : 'customer_employee.php';
                        echo '<a class="btn btn-danger" href="' . $cancel_redirect . '" role="button">Cancel</a>';
                    } else {
                        echo "<p>No role type set in session.</p>";
                    }
                    ?>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('phone_no').addEventListener('input', function() {
            const phoneField = this;
            const phonePattern = /^(09\d{9}|\+639\d{9})$/;
            if (phonePattern.test(phoneField.value)) {
                phoneField.setCustomValidity('');
            } else {
                phoneField.setCustomValidity('Please enter a valid Philippine mobile number.');
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>