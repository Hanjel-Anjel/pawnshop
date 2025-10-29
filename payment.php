<?php
@include 'config.php';
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header("Location: login.php");
    exit();
}

// Get item_id from URL
if (!isset($_GET['item_id'])) {
    die("Invalid request.");
}
$item_id = $_GET['item_id'];

// Fetch item info
$stmt = $conn->prepare("SELECT i.*, c.first_name, c.last_name 
                        FROM items i 
                        JOIN custumer_info c ON i.customer_id = c.customer_id 
                        WHERE i.item_id = ?");
$stmt->bind_param("i", $item_id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

if (!$item) {
    die("Item not found.");
}

// Handle form submission
if (isset($_POST['submit_payment'])) {
    $amount_paid = $_POST['amount_paid'];
    $payment_method = $_POST['payment_method'];
    $remarks = $_POST['remarks'];
    $handled_by = $_SESSION['user_id']; // whoever is logged in

    // Compute new balance
    $remaining_balance = $item['total_balance'] - $amount_paid;
    if ($remaining_balance < 0) $remaining_balance = 0;

    // Insert into payments table
    $insert = $conn->prepare("INSERT INTO payments (item_id, customer_id, amount_paid, remaining_balance, payment_method, handled_by, remarks)
                              VALUES (?, ?, ?, ?, ?, ?, ?)");
    $insert->bind_param("iiddsis", $item_id, $item['customer_id'], $amount_paid, $remaining_balance, $payment_method, $handled_by, $remarks);
    $insert->execute();

    // Update the item’s total_balance
    $update = $conn->prepare("UPDATE items SET total_balance = ? WHERE item_id = ?");
    $update->bind_param("di", $remaining_balance, $item_id);
    $update->execute();

    // Update item status if fully paid
    if ($remaining_balance == 0) {
        $conn->query("UPDATE items SET item_status = 'Fully Paid' WHERE item_id = $item_id");
    }

    // Insert into transactions table for Payment
    $transaction_date = date("Y-m-d");
    $transaction_type = 'Payment';
    $customer_id = $item['customer_id'];

    $insert_transaction = $conn->prepare("
        INSERT INTO transactions 
        (customer_id, item_id, transaction_date, amount, total_amount, payment_method, handled_by, transaction_type)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $total_amount = $remaining_balance + $amount_paid;

    $insert_transaction->bind_param(
        "iissdsss",
        $customer_id,
        $item_id,
        $transaction_date,
        $amount_paid,
        $total_amount,
        $payment_method,
        $handled_by,
        $transaction_type
    );

    $insert_transaction->execute();

    // Redirect based on user type
    if ($_SESSION['user_type'] === 'admin') {
        header("Location: item_table.php");
    } else if ($_SESSION['user_type'] === 'employee') {
        header("Location: inventory_employee.php");
    } else {
        header("Location: login.php"); // fallback
    }
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow p-4">
        <h3 class="mb-4 text-center">Make Payment</h3>

        <div class="mb-3">
            <strong>Customer:</strong> <?= htmlspecialchars($item['first_name'] . ' ' . $item['last_name']); ?><br>
            <strong>Item:</strong> <?= htmlspecialchars($item['brand'] . ' ' . $item['model']); ?><br>
            <strong>Total Balance:</strong> ₱<?= number_format($item['total_balance'], 2); ?><br>
        </div>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Amount to Pay</label>
                <input type="number" step="0.01" name="amount_paid" class="form-control" required max="<?= $item['total_balance']; ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Payment Method</label>
                <select name="payment_method" class="form-select" required>
                    <option value="">Select method</option>
                    <option value="Cash">Cash</option>
                    <option value="GCash">GCash</option>
                    <option value="Card">Card</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Remarks</label>
                <input type="text" name="remarks" class="form-control" placeholder="Optional notes...">
            </div>
            <button type="submit" name="submit_payment" class="btn btn-success w-100">Submit Payment</button>
        </form>

        <?php
        $back_page = ($_SESSION['user_type'] === 'admin') ? 'item_table.php' : 'inventory_employee.php';
        ?>
        <a href="<?= $back_page ?>" class="btn btn-secondary mt-3 w-100">Back to Items</a>

    </div>
</div>
</body>
</html>
