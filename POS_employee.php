<?php
@include 'config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('location:login_form.php');
    exit();
}

$handled_by = $_SESSION['user_id']; // Automatically set the handler to the logged-in user ID

if (isset($_POST['add_customer_item'])) {
    // Capture customer data
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);

    // Insert new customer into the database
    $insert_customer_query = "INSERT INTO custumer_info (first_name, last_name, email, phone_no, address)
        VALUES ('$first_name', '$last_name', '$email', '$phone', '$address')";

    if (mysqli_query($conn, $insert_customer_query)) {
        $customer_id = mysqli_insert_id($conn); // Get the last inserted customer ID

        // Capture item data
        $item_name = mysqli_real_escape_string($conn, $_POST['item_name']);
        $item_value = mysqli_real_escape_string($conn, $_POST['item_value']);
        $loan_amount = mysqli_real_escape_string($conn, $_POST['loan_amount']);
        $category = mysqli_real_escape_string($conn, $_POST['category']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $pawn_date = mysqli_real_escape_string($conn, $_POST['pawn_date']);
        $due_date = mysqli_real_escape_string($conn, $_POST['due_date']);

        // Calculate interest
        $interest_rate = 0.03; // 3% monthly interest rate
        $pawn_date = new DateTime($_POST['pawn_date']); 
        $due_date = new DateTime($_POST['due_date']); 
        $diff = $pawn_date->diff($due_date);
        $months_diff = ($diff->y * 12) + $diff->m + ($diff->d / 30); // Calculate difference in months

        // Calculate compounded interest
        $principal = $_POST['loan_amount'];
        $compound_interest = $principal * pow((1 + $interest_rate), $months_diff) - $principal; 
        $total_balance = $principal + $compound_interest;

        // Format dates correctly for the SQL query
        $pawn_date_formatted = $pawn_date->format('Y-m-d'); // Format as 'YYYY-MM-DD'
        $due_date_formatted = $due_date->format('Y-m-d'); // Format as 'YYYY-MM-DD'

        // Insert new item into the database with calculated interest and total balance
        $insert_item_query = "INSERT INTO items (customer_id, item_name, item_value, loan_amount, item_category, item_status, pawn_date, due_date, interest_rate, total_balance) 
                              VALUES ('$customer_id', '$item_name', '$item_value', '$loan_amount', '$category', '$status', '$pawn_date_formatted', '$due_date_formatted', '$compound_interest', '$total_balance')";

        if (mysqli_query($conn, $insert_item_query)) {
            // Automatically record the transaction
            $transaction_date = date('Y-m-d');
            $insert_transaction_query = "INSERT INTO transactions (customer_id, item_id, transaction_date, amount, payment_method, handled_by) VALUES ('$customer_id', LAST_INSERT_ID(), '$transaction_date', '$loan_amount', 'Cash', '$handled_by')";

            if (mysqli_query($conn, $insert_transaction_query)) {
                $success_message = "Customer, item, and transaction recorded successfully.";
                // Redirect automatically to home 
                header('location: home_employee.php');
            } else {
                $error_message = "Error adding transaction: " . mysqli_error($conn);
            }
        } else {
            $error_message = "Error adding item: " . mysqli_error($conn);
        }
    } else {
        $error_message = "Error adding customer: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pawnshop POS System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h2 class="mb-4">Add New Customer and Item</h2>

        <?php if (isset($success_message)) {
            echo "<div class='alert alert-success'>$success_message</div>";
        } ?>
        <?php if (isset($error_message)) {
            echo "<div class='alert alert-danger'>$error_message</div>";
        } ?>

        <div class="card">
            <div class="card-header">Add New Customer and Item</div>
            <div class="card-body">
                <form action="" method="post">
                    <h5>Customer Information</h5>
                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" name="first_name" id="first_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" name="last_name" id="last_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" name="phone" id="phone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" name="address" id="address" class="form-control">
                    </div>

                    <h5 class="mt-4">Item Information</h5>
                    <div class="mb-3">
                        <label for="item_name" class="form-label">Item Name</label>
                        <input type="text" name="item_name" id="item_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="item_value" class="form-label">Item Value</label>
                        <input type="number" step="0.01" name="item_value" id="item_value" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="loan_amount" class="form-label">Loan Amount</label>
                        <input type="number" step="0.01" name="loan_amount" id="loan_amount" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <input type="text" name="category" id="category" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <input type="text" name="status" id="status" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="pawn_date" class="form-label">Pawn Date</label>
                        <input type="date" name="pawn_date" id="pawn_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="due_date" class="form-label">Due Date</label>
                        <input type="date" name="due_date" id="due_date" class="form-control" required>
                    </div>

                    <button type="submit" name="add_customer_item" class="btn btn-primary">Add Customer and Item</button>
                    <a href="home_employee.php" class="btn btn-danger">Cancel</a>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>