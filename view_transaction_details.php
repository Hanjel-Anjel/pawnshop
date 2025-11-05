<?php
include('d:\xampp\htdocs\pawnshop\config.php');

if (!isset($_GET['transaction_id'])) {
    die("<h3>Invalid request. No transaction ID provided.</h3>");
}

$transaction_id = intval($_GET['transaction_id']);

// ✅ Fetch transaction details (removed i.category, fixed joins)
$query = "
    SELECT 
        t.transaction_id,
        t.transaction_date,
        t.amount,
        t.loan_amount,
        t.interest_amount,
        t.total_amount,
        t.payment_method,
        t.transaction_type,
        c.customer_id,
        CONCAT(c.first_name, ' ', c.last_name) AS customer_name,
        c.phone_no,
        c.email,
        i.item_id,
        CONCAT(i.brand, ' ', i.model) AS item_name,
        i.brand,
        i.model,
        i.specifications,
        i.item_value AS appraisal_price,
        CONCAT(u.first_name, ' ', u.last_name) AS handler_name
    FROM transactions t
    JOIN custumer_info c ON t.customer_id = c.customer_id
    LEFT JOIN items i ON t.item_id = i.item_id
    LEFT JOIN users u ON t.handled_by = u.user_id
    WHERE t.transaction_id = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $transaction_id);
$stmt->execute();
$transaction = $stmt->get_result()->fetch_assoc();

if (!$transaction) {
    die("<h3>No transaction found for ID: {$transaction_id}</h3>");
}

// ✅ Fetch payment history for the same item
$paymentQuery = "
    SELECT 
        payment_id, 
        payment_date, 
        amount_paid, 
        remaining_balance, 
        payment_method, 
        remarks 
    FROM payments 
    WHERE item_id = ? 
    ORDER BY payment_date DESC
";
$paymentStmt = $conn->prepare($paymentQuery);
$paymentStmt->bind_param("i", $transaction['item_id']);
$paymentStmt->execute();
$payments = $paymentStmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Details - Pawnshop System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        :root {
            --primary-color: #1976d2;
            --primary-dark: #1565c0;
            --primary-light: #e3f2fd;
            --accent-color: #ff4081;
            --success-color: #4caf50;
            --text-primary: #212121;
            --text-secondary: #757575;
            --background: #f5f5f5;
            --card-bg: #ffffff;
            --shadow: 0 2px 4px rgba(0,0,0,0.1);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --shadow-lg: 0 8px 16px rgba(0,0,0,0.15);
        }

        body {
            background-color: var(--background);
            font-family: 'Roboto', sans-serif;
            color: var(--text-primary);
        }

        .container {
            max-width: 1200px;
            margin-top: 30px;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 30px;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            border-radius: 12px 12px 0 0;
            padding: 20px;
            font-weight: 600;
        }

        .card-header h4, .card-header h5 {
            margin: 0;
        }

        .card-body {
            padding: 30px;
        }

        .table {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .table thead {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
        }

        .table tbody tr:hover {
            background-color: var(--primary-light);
        }

        .badge-loan { background-color: var(--primary-color); }
        .badge-payment { background-color: var(--success-color); }
        .badge-renewal { background-color: var(--accent-color); color: #000; }
        .badge-penalty { background-color: #dc3545; }

        .btn-custom {
            background: linear-gradient(135deg, var(--accent-color) 0%, #e91e63 100%);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .btn-custom:hover {
            background: linear-gradient(135deg, #e91e63 0%, #c2185b 100%);
            transform: scale(1.05);
        }

        .icon-gold {
            color: var(--accent-color);
        }

        .summary-box {
            background: var(--primary-light);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .print-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }

        @media print {
            .print-btn, .btn-light {
                display: none;
            }
            .card {
                box-shadow: none;
                border: 1px solid #ddd;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Header with Back Button and Print -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="text-primary"><i class="bi bi-receipt icon-gold"></i> Transaction Details</h1>
        <div>
            <a href="transaction_page.php" class="btn btn-light btn-sm me-2">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            <button class="btn btn-custom btn-sm" onclick="window.print()">
                <i class="bi bi-printer"></i> Print
            </button>
        </div>
    </div>

    <!-- Transaction Summary -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5><i class="bi bi-info-circle icon-gold"></i> Transaction Summary</h5>
            <span class="badge 
                <?= match($transaction['transaction_type']) {
                    'Loan' => 'badge-loan',
                    'Payment' => 'badge-payment',
                    'Renewal' => 'badge-renewal',
                    'Penalty' => 'badge-penalty',
                    default => 'bg-secondary'
                } ?>">
                <?= htmlspecialchars($transaction['transaction_type']) ?>
            </span>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Transaction ID:</strong> #<?= str_pad($transaction['transaction_id'], 4, '0', STR_PAD_LEFT) ?></p>
                    <p><strong>Date:</strong> <?= date('F d, Y \a\t h:i A', strtotime($transaction['transaction_date'])) ?></p>
                    <p><strong>Handler:</strong> <?= htmlspecialchars($transaction['handler_name'] ?? 'N/A') ?></p>
                </div>
                <div class="col-md-6">
                    <div class="summary-box">
                        <h6>Quick Totals</h6>
                        <?php if ($transaction['transaction_type'] === 'Payment'): ?>
                            <p><strong>Amount Paid:</strong> ₱<?= number_format($transaction['amount'], 2) ?></p>
                        <?php else: ?>
                            <p><strong>Total Amount:</strong> ₱<?= number_format($transaction['total_amount'], 2) ?></p>
                        <?php endif; ?>
                        <p><strong>Payment Method:</strong> <?= htmlspecialchars($transaction['payment_method']) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Info -->
    <div class="card">
        <div class="card-header">
            <h5><i class="bi bi-person-circle icon-gold"></i> Customer Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <p><strong>Name:</strong> <?= htmlspecialchars($transaction['customer_name']) ?></p>
                </div>
                <div class="col-md-4">
                    <p><strong>Email:</strong> <a href="mailto:<?= htmlspecialchars($transaction['email']) ?>" style="color: var(--primary-color); text-decoration: none;"><?= htmlspecialchars($transaction['email']) ?></a></p>
                </div>
                <div class="col-md-4">
                    <p><strong>Phone:</strong> <a href="tel:<?= htmlspecialchars($transaction['phone_no']) ?>" style="color: var(--primary-color); text-decoration: none;"><?= htmlspecialchars($transaction['phone_no']) ?></a></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Item Info -->
    <div class="card">
        <div class="card-header">
            <h5><i class="bi bi-box-seam icon-gold"></i> Item Information</h5>
        </div>
        <div class="card-body">
            <?php if (!empty($transaction['item_name'])): ?>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Item Name:</strong> <?= htmlspecialchars($transaction['item_name']) ?></p>
                        <p><strong>Brand:</strong> <?= htmlspecialchars($transaction['brand']) ?></p>
                        <p><strong>Model:</strong> <?= htmlspecialchars($transaction['model']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Specifications:</strong> <?= htmlspecialchars($transaction['specifications']) ?></p>
                        <p><strong>Appraisal Price:</strong> <span class="text-success fw-bold">₱<?= number_format($transaction['appraisal_price'], 2) ?></span></p>
                    </div>
                </div>
            <?php else: ?>
                <p class="text-muted mb-0">No item information available.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Transaction Breakdown -->
    <div class="card">
        <div class="card-header">
            <h5><i class="bi bi-cash-stack icon-gold"></i> Transaction Breakdown</h5>
        </div>
        <div class="card-body">
            <?php if ($transaction['transaction_type'] === 'Payment'): ?>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Amount Paid:</strong> ₱<?= number_format($transaction['amount'], 2) ?></p>
                        <p><strong>Payment Method:</strong> <?= htmlspecialchars($transaction['payment_method']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <?php
                        // ✅ Fetch both latest and previous remaining balances
                        $balanceQuery = "
                            SELECT remaining_balance 
                            FROM payments 
                            WHERE item_id = ? 
                            ORDER BY payment_date DESC 
                            LIMIT 2
                        ";
                        $balanceStmt = $conn->prepare($balanceQuery);
                        $balanceStmt->bind_param("i", $transaction['item_id']);
                        $balanceStmt->execute();
                        $balanceResult = $balanceStmt->get_result();

                        $balances = [];
                        while ($row = $balanceResult->fetch_assoc()) {
                            $balances[] = $row['remaining_balance'];
                        }

                        $remaining_balance = $balances[0] ?? 0; // Latest
                        $previous_balance = $balances[1] ?? null; // Previous (may not exist)
                        ?>

                        <?php if ($previous_balance !== null): ?>
                            <p><strong>Previous Balance:</strong> ₱<?= number_format($previous_balance, 2) ?></p>
                        <?php endif; ?>

                        <p><strong>Remaining Balance:</strong> <span class="text-danger fw-bold">₱<?= number_format($remaining_balance, 2) ?></span></p>
                    </div>
                </div>
            <?php else: ?>
                <div class="row">
                    <div class="col-md-4">
                        <p><strong>Loan Amount:</strong> ₱<?= number_format($transaction['loan_amount'], 2) ?></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Interest:</strong> ₱<?= number_format($transaction['interest_amount'], 2) ?></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Total Amount:</strong> <span class="text-primary fw-bold">₱<?= number_format($transaction['total_amount'], 2) ?></span></p>
                    </div>
                </div>
                <p><strong>Payment Method:</strong> <?= htmlspecialchars($transaction['payment_method']) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Payment History -->
    <div class="card">
        <div class="card-header">
            <h5><i class="bi bi-clock-history icon-gold"></i> Payment History</h5>
        </div>
        <div class="card-body">
            <?php if ($payments->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date & Time</th>
                                <th>Amount Paid</th>
                                <th>Remaining Balance</th>
                                <th>Method</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $n = 1;
                            while ($p = $payments->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $n++ ?></td>
                                    <td><i class="bi bi-calendar-event"></i> <?= date('M d, Y h:i A', strtotime($p['payment_date'])) ?></td>
                                    <td class="text-success fw-bold">₱<?= number_format($p['amount_paid'], 2) ?></td>
                                    <td class="text-danger">₱<?= number_format($p['remaining_balance'], 2) ?></td>
                                    <td><?= htmlspecialchars($p['payment_method']) ?></td>
                                    <td><?= htmlspecialchars($p['remarks'] ?? '—') ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted mb-0"><i class="bi bi-info-circle"></i> No payments have been recorded for this item.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Floating Print Button for Mobile -->
<div class="print-btn d-md-none">
    <button class="btn btn-custom" onclick="window.print()">
        <i class="bi bi-printer"></i>
    </button>
</div>

</body>
</html>
