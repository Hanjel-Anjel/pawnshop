<?php
@include('c:\xampp\htdocs\pawnshop\config.php');

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('location:login_form.php');
    exit();
}


// Automatically set the handler to the current logged-in user ID
$handled_by = $_SESSION['user_id'];

// Fetch customers and items for the dropdowns
$customers_result = mysqli_query($conn, "SELECT customer_id, first_name, last_name FROM custumer_info");
$items_result = mysqli_query($conn, "SELECT item_id, item_name FROM items");

if (isset($_POST['submit'])) {
    $customer_id = mysqli_real_escape_string($conn, $_POST['customer_id']);
    $item_id = mysqli_real_escape_string($conn, $_POST['item_id']);
    $transaction_date = mysqli_real_escape_string($conn, $_POST['transaction_date']);
    $amount = mysqli_real_escape_string($conn, $_POST['amount']);
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);

    // Insert transaction into the database
    $insert_query = "INSERT INTO transactions (customer_id, item_id, transaction_date, amount, payment_method, handled_by) 
                     VALUES ('$customer_id', '$item_id', '$transaction_date', '$amount', '$payment_method', '$handled_by')";

    if (mysqli_query($conn, $insert_query)) {
        $success_message = "Transaction added successfully.";
        header('location: transaction_page.php');
    } else {
        $error_message = "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Transaction</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2 class="mb-4">Add New Transaction</h2>

    <?php if (isset($success_message)) { echo "<div class='alert alert-success'>$success_message</div>"; } ?>
    <?php if (isset($error_message)) { echo "<div class='alert alert-danger'>$error_message</div>"; } ?>

    <form action="" method="post">
        <div class="mb-3">
            <label for="customer_id" class="form-label">Customer</label>
            <select name="customer_id" id="customer_id" class="form-select" required>
                <option value="">Select a Customer</option>
                <?php while ($customer = mysqli_fetch_assoc($customers_result)) {
                    echo "<option value='{$customer['customer_id']}'>{$customer['first_name']} {$customer['last_name']}</option>";
                } ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="item_id" class="form-label">Item</label>
            <select name="item_id" id="item_id" class="form-select" required>
                <option value="">Select an Item</option>
                <?php while ($item = mysqli_fetch_assoc($items_result)) {
                    echo "<option value='{$item['item_id']}'>{$item['item_name']}</option>";
                } ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="transaction_date" class="form-label">Transaction Date</label>
            <input type="date" name="transaction_date" id="transaction_date" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="amount" class="form-label">Amount</label>
            <input type="number" step="0.01" name="amount" id="amount" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="payment_method" class="form-label">Payment Method</label>
            <select name="payment_method" id="payment_method" class="form-select" required>
                <option value="Cash">Cash</option>
                <option value="Card">Card</option>
                <option value="Online">Online</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="handled_by" class="form-label">Handled By</label>
            <input type="text" id="handled_by" class="form-control" 
       value="<?php echo htmlspecialchars($_SESSION['admin_name'] ?? $_SESSION['user_name']); ?>" readonly>

         </div>

        <button type="submit" name="submit" class="btn btn-primary">Add Transaction</button>
        <a class="btn btn-danger" href="transaction_page.php">Cancel</a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
