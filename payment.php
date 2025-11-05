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

    // Insert into payments table (added payment_date = NOW())
    $insert = $conn->prepare("INSERT INTO payments (item_id, customer_id, payment_date, amount_paid, remaining_balance, payment_method, handled_by, remarks)
                              VALUES (?, ?, NOW(), ?, ?, ?, ?, ?)");
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

    // Insert into transactions table for Payment (transaction_date is now DATETIME)
    $transaction_date = date("Y-m-d H:i:s"); // Full DATETIME format
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - ArMaTech Pawnshop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1976d2;
            --primary-dark: #1565c0;
            --success-color: #2e7d32;
            --warning-color: #f57c00;
            --danger-color: #d32f2f;
            --info-color: #0288d1;
            --surface-color: #ffffff;
        }

        * {
            font-family: 'Roboto', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 1rem;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated background elements */
        body::before {
            content: '';
            position: fixed;
            width: 500px;
            height: 500px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            top: -250px;
            right: -250px;
            animation: float 20s infinite ease-in-out;
            z-index: 0;
        }

        body::after {
            content: '';
            position: fixed;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 50%;
            bottom: -200px;
            left: -200px;
            animation: float 15s infinite ease-in-out reverse;
            z-index: 0;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(10deg);
            }
        }

        /* Main Container */
        .payment-container {
            max-width: 700px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        /* Payment Card */
        .payment-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Header */
        .payment-header {
            background: linear-gradient(135deg, var(--success-color) 0%, #388e3c 100%);
            color: white;
            padding: 2rem;
            text-align: center;
            position: relative;
        }

        .payment-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="20" height="20" patternUnits="userSpaceOnUse"><path d="M 20 0 L 0 0 0 20" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }

        .payment-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            position: relative;
            z-index: 1;
        }

        .payment-icon i {
            font-size: 2.5rem;
            color: white;
        }

        .payment-header h3 {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
            position: relative;
            z-index: 1;
        }

        .payment-header p {
            margin: 0.5rem 0 0 0;
            opacity: 0.95;
            position: relative;
            z-index: 1;
        }

        /* Payment Body */
        .payment-body {
            padding: 2rem;
        }

        /* Info Section */
        .info-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border-left: 4px solid var(--primary-color);
        }

        .info-title {
            color: var(--primary-color);
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #546e7a;
            font-weight: 500;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-label i {
            color: var(--primary-color);
            font-size: 1.125rem;
        }

        .info-value {
            color: #263238;
            font-weight: 600;
            font-size: 1rem;
        }

        .balance-highlight {
            color: var(--success-color);
            font-size: 1.5rem;
            font-weight: 700;
        }

        /* Form Section */
        .form-section-title {
            color: #263238;
            font-weight: 600;
            font-size: 1rem;
            margin: 2rem 0 1rem 0;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--primary-color);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .form-section-title i {
            color: var(--primary-color);
            font-size: 1.25rem;
        }

        /* Form Controls */
        .form-label {
            color: #546e7a;
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-label i {
            color: var(--primary-color);
        }

        .form-control,
        .form-select {
            border-radius: 12px;
            border: 2px solid #e0e0e0;
            padding: 0.875rem 1rem;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(25, 118, 210, 0.15);
        }

        /* Payment Method Icons */
        .payment-method-option {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Buttons */
        .btn {
            border-radius: 12px;
            font-weight: 600;
            padding: 0.875rem 1.5rem;
            border: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success-color) 0%, #388e3c 100%);
            box-shadow: 0 4px 12px rgba(46, 125, 50, 0.3);
            color: white;
        }

        .btn-success:hover {
            box-shadow: 0 6px 16px rgba(46, 125, 50, 0.4);
            color: white;
        }

        .btn-secondary {
            background: #e0e0e0;
            color: #546e7a;
        }

        .btn-secondary:hover {
            background: #bdbdbd;
            color: #546e7a;
        }

        /* Amount Input with Currency */
        .currency-input-wrapper {
            position: relative;
        }

        .currency-symbol {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--success-color);
            font-weight: 700;
            font-size: 1.125rem;
            z-index: 10;
        }

        .currency-input-wrapper .form-control {
            padding-left: 2.5rem;
        }

        /* Calculation Preview */
        .calculation-preview {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            border-radius: 12px;
            padding: 1rem;
            margin-top: 1rem;
            border-left: 4px solid var(--info-color);
        }

        .calculation-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            color: #263238;
        }

        .calculation-label {
            font-weight: 500;
        }

        .calculation-value {
            font-weight: 700;
        }

        .new-balance {
            font-size: 1.125rem;
            color: var(--success-color);
            padding-top: 0.5rem;
            border-top: 2px solid rgba(0, 0, 0, 0.1);
            margin-top: 0.5rem;
        }

        /* Responsive */
        @media (max-width: 576px) {
            .payment-body {
                padding: 1.5rem;
            }

            .payment-header h3 {
                font-size: 1.5rem;
            }

            .payment-icon {
                width: 70px;
                height: 70px;
            }

            .payment-icon i {
                font-size: 2rem;
            }

            .balance-highlight {
                font-size: 1.25rem;
            }
        }
    </style>
</head>
<body>

<div class="payment-container">
    <div class="payment-card">
        <!-- Header -->
        <div class="payment-header">
            <div class="payment-icon">
                <i class="bi bi-cash-coin"></i>
            </div>
            <h3>Process Payment</h3>
            <p>Complete the payment transaction</p>
        </div>

        <!-- Body -->
        <div class="payment-body">
            <!-- Customer & Item Information -->
            <div class="info-section">
                <div class="info-title">
                    <i class="bi bi-info-circle-fill"></i>
                    Transaction Details
                </div>

                <div class="info-row">
                    <div class="info-label">
                        <i class="bi bi-person-fill"></i>
                        Customer
                    </div>
                    <div class="info-value"><?= htmlspecialchars($item['first_name'] . ' ' . $item['last_name']); ?></div>
                </div>

                <div class="info-row">
                    <div class="info-label">
                        <i class="bi bi-box-seam"></i>
                        Item
                    </div>
                    <div class="info-value"><?= htmlspecialchars($item['brand'] . ' ' . $item['model']); ?></div>
                </div>

                <div class="info-row">
                    <div class="info-label">
                        <i class="bi bi-wallet2"></i>
                        Current Balance
                    </div>
                    <div class="info-value balance-highlight">₱<?= number_format($item['total_balance'], 2); ?></div>
                </div>
            </div>

            <!-- Payment Form -->
            <form method="POST" id="paymentForm">
                <h6 class="form-section-title">
                    <i class="bi bi-credit-card-fill"></i>
                    Payment Information
                </h6>

                <div class="mb-3">
                    <label class="form-label">
                        <i class="bi bi-cash-stack"></i>
                        Amount to Pay
                    </label>
                    <div class="currency-input-wrapper">
                        <span class="currency-symbol">₱</span>
                        <input type="number" 
                               step="0.01" 
                               name="amount_paid" 
                               id="amount_paid"
                               class="form-control" 
                               placeholder="0.00"
                               required 
                               max="<?= $item['total_balance']; ?>"
                               oninput="calculateBalance()">
                    </div>
                    <small class="text-muted">Maximum: ₱<?= number_format($item['total_balance'], 2); ?></small>
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        <i class="bi bi-credit-card-2-front"></i>
                        Payment Method
                    </label>
                    <select name="payment_method" class="form-select" required>
                        <option value="">Select payment method</option>
                        <option value="Cash"> Cash</option>
                        <option value="GCash"> GCash</option>
                        <option value="Card"> Card</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        <i class="bi bi-chat-left-text"></i>
                        Remarks (Optional)
                    </label>
                    <input type="text" 
                           name="remarks" 
                           class="form-control" 
                           placeholder="Add any notes or comments...">
                </div>

                <!-- Calculation Preview -->
                <div class="calculation-preview" id="calculationPreview" style="display: none;">
                    <div class="calculation-row">
                        <span class="calculation-label">Current Balance:</span>
                        <span class="calculation-value">₱<?= number_format($item['total_balance'], 2); ?></span>
                    </div>
                    <div class="calculation-row">
                        <span class="calculation-label">Payment Amount:</span>
                        <span class="calculation-value" id="paymentAmount">₱0.00</span>
                    </div>
                    <div class="calculation-row new-balance">
                        <span class="calculation-label">New Balance:</span>
                        <span class="calculation-value" id="newBalance">₱<?= number_format($item['total_balance'], 2); ?></span>
                    </div>
                </div>

                <button type="submit" name="submit_payment" class="btn btn-success w-100 mt-4">
                    <i class="bi bi-check-circle"></i>
                    Submit Payment
                </button>
            </form>

            <?php
            $back_page = ($_SESSION['user_type'] === 'admin') ? 'item_table.php' : 'inventory_employee.php';
            ?>
            <a href="<?= $back_page ?>" class="btn btn-secondary w-100 mt-2">
                <i class="bi bi-arrow-left"></i>
                Back to Items
            </a>
        </div>
    </div>
</div>

<script>
    const currentBalance = <?= $item['total_balance']; ?>;

    function calculateBalance() {
        const amountInput = document.getElementById('amount_paid');
        const amountPaid = parseFloat(amountInput.value) || 0;
        const preview = document.getElementById('calculationPreview');
        
        if (amountPaid > 0) {
            preview.style.display = 'block';
            
            const newBalance = Math.max(0, currentBalance - amountPaid);
            
            document.getElementById('paymentAmount').textContent = 
                '₱' + amountPaid.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            document.getElementById('newBalance').textContent = 
                '₱' + newBalance.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        } else {
            preview.style.display = 'none';
        }
    }

    // Form validation
    document.getElementById('paymentForm').addEventListener('submit', function(e) {
        const amountPaid = parseFloat(document.getElementById('amount_paid').value);
        
        if (amountPaid <= 0) {
            e.preventDefault();
            alert('Please enter a valid payment amount.');
            return false;
        }
        
        if (amountPaid > currentBalance) {
            e.preventDefault();
            alert('Payment amount cannot exceed the current balance.');
            return false;
        }
        
        return confirm('Are you sure you want to process this payment?');
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>