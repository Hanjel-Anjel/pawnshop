<?php
@include 'config.php';
include('header_employee.php');

$current_user_id = $_SESSION['user_id'];
$current_user_name = $_SESSION['first_name'] ?? '';

// Fetch transactions handled by the current user
function fetchTransactions($conn, $userId, $dateFilter = 'all') {
     $query = "
        SELECT 
            t.transaction_id, 
            t.transaction_type,
            t.transaction_date, 
            t.amount, 
            t.payment_method, 
            c.first_name AS customer_first_name, 
            c.last_name AS customer_last_name, 
            i.model AS item_model, 
            u.first_name AS handler_first_name, 
            u.last_name AS handler_last_name
        FROM transactions AS t
        JOIN custumer_info AS c ON t.customer_id = c.customer_id
        JOIN items AS i ON t.item_id = i.item_id
        JOIN users AS u ON t.handled_by = u.user_id
        WHERE t.handled_by = ?
    ";

    switch ($dateFilter) {
        case 'today':
            $query .= " AND DATE(t.transaction_date) = CURDATE()";
            break;
        case 'week':
            $query .= " AND WEEK(t.transaction_date) = WEEK(CURDATE())";
            break;
        case 'month':
            $query .= " AND MONTH(t.transaction_date) = MONTH(CURDATE())";
            break;
        case '3months':
            $query .= " AND t.transaction_date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)";
            break;
        case 'all':
        default:
            break;
    }

    $query .= " ORDER BY t.transaction_date DESC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    return $stmt->get_result();
}

// Calculate transaction statistics
function getTransactionStats($conn, $userId, $dateFilter = 'all') {
    $query = "
        SELECT 
            COUNT(*) as total_transactions,
            SUM(amount) as total_amount,
            AVG(amount) as avg_amount
        FROM transactions
        WHERE handled_by = ?
    ";

    switch ($dateFilter) {
        case 'today':
            $query .= " AND DATE(transaction_date) = CURDATE()";
            break;
        case 'week':
            $query .= " AND WEEK(transaction_date) = WEEK(CURDATE())";
            break;
        case 'month':
            $query .= " AND MONTH(transaction_date) = MONTH(CURDATE())";
            break;
        case '3months':
            $query .= " AND transaction_date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)";
            break;
    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

$date_filter = $_GET['date_filter'] ?? 'all';
$transactions = fetchTransactions($conn, $current_user_id, $date_filter);
$stats = getTransactionStats($conn, $current_user_id, $date_filter);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History</title>
    <link rel="stylesheet" href="path-to-bootstrap.css">
    <style>
        :root {
            --primary-color: #1976d2;
            --primary-dark: #1565c0;
            --primary-light: #42a5f5;
            --success-color: #2e7d32;
            --info-color: #0288d1;
            --warning-color: #f57c00;
            --surface: #ffffff;
            --background: #f5f5f5;
            --text-primary: #212121;
            --text-secondary: #757575;
            --divider: #e0e0e0;
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.08);
            --shadow-md: 0 4px 8px rgba(0,0,0,0.12);
            --shadow-lg: 0 8px 16px rgba(0,0,0,0.15);
        }

        body {
            background-color: var(--background);
            font-family: 'Roboto', 'Segoe UI', sans-serif;
        }

        .container {
            max-width: 1400px;
        }

        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 2.5rem 0;
            margin: -1rem -1rem 2rem -1rem;
            box-shadow: var(--shadow-md);
        }

        .page-header h2 {
            margin: 0;
            font-weight: 500;
            font-size: 2rem;
            letter-spacing: 0.5px;
        }

        .page-header .subtitle {
            margin-top: 0.5rem;
            opacity: 0.9;
            font-size: 0.95rem;
        }

        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--surface);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary-color);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
        }

        .stat-card.success {
            border-left-color: var(--success-color);
        }

        .stat-card.info {
            border-left-color: var(--info-color);
        }

        .stat-card.warning {
            border-left-color: var(--warning-color);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .stat-card .stat-icon {
            background: rgba(25, 118, 210, 0.1);
            color: var(--primary-color);
        }

        .stat-card.success .stat-icon {
            background: rgba(46, 125, 50, 0.1);
            color: var(--success-color);
        }

        .stat-card.info .stat-icon {
            background: rgba(2, 136, 209, 0.1);
            color: var(--info-color);
        }

        .stat-label {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-secondary);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--text-primary);
            line-height: 1.2;
        }

        /* Filter Card */
        .filter-card {
            background: var(--surface);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            margin-bottom: 2rem;
        }

        .filter-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.25rem;
        }

        .filter-header h5 {
            margin: 0;
            font-weight: 500;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-primary);
        }

        .filter-header i {
            color: var(--primary-color);
        }

        /* Form Controls */
        .form-label {
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .form-select {
            border: 1px solid var(--divider);
            border-radius: 8px;
            padding: 0.625rem 0.875rem;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(25, 118, 210, 0.1);
            outline: none;
        }

        /* Buttons */
        .btn {
            border-radius: 8px;
            padding: 0.625rem 1.5rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            border: none;
            font-size: 0.875rem;
        }

        .btn-primary {
            background: var(--primary-color);
            box-shadow: var(--shadow-sm);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            box-shadow: var(--shadow-md);
            transform: translateY(-1px);
        }

        .btn-success {
            background: var(--success-color);
            box-shadow: var(--shadow-sm);
        }

        .btn-success:hover {
            background: #1b5e20;
            box-shadow: var(--shadow-md);
            transform: translateY(-1px);
        }

        /* Table Container */
        .table-container {
            background: var(--surface);
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .table-header {
            background: linear-gradient(135deg, #37474f 0%, #263238 100%);
            color: white;
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .table-header h5 {
            margin: 0;
            font-weight: 500;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: #263238;
            color: white;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.8rem;
            padding: 1rem;
            border: none;
            white-space: nowrap;
        }

        .table tbody tr {
            transition: all 0.2s ease;
            border-bottom: 1px solid var(--divider);
        }

        .table tbody tr:hover {
            background-color: rgba(25, 118, 210, 0.04);
        }

        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            color: var(--text-primary);
            font-size: 0.9rem;
        }

        /* Payment Method Badge */
        .payment-badge {
            display: inline-block;
            padding: 0.375rem 0.875rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .payment-cash {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .payment-card {
            background-color: #e3f2fd;
            color: #1565c0;
        }

        .payment-online {
            background-color: #f3e5f5;
            color: #7b1fa2;
        }

        .payment-bank {
            background-color: #fff3e0;
            color: #e65100;
        }

        /* Amount Styling */
        .amount-cell {
            font-weight: 600;
            color: var(--success-color);
            font-size: 1rem;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-secondary);
        }

        .empty-state i {
            font-size: 5rem;
            margin-bottom: 1.5rem;
            opacity: 0.3;
        }

        .empty-state h5 {
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }

        .empty-state p {
            margin: 0;
        }

        /* Transaction ID Badge */
        .transaction-id {
            font-family: 'Courier New', monospace;
            font-weight: 600;
            color: var(--text-primary);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .page-header h2 {
                font-size: 1.5rem;
            }

            .stats-container {
                grid-template-columns: 1fr;
            }

            .stat-value {
                font-size: 1.5rem;
            }

            .filter-card form {
                flex-direction: column;
                gap: 1rem;
            }

            .form-select {
                width: 100% !important;
            }
        }

        
        
       /* Print Styles */
@media print {
    /* Hide everything except the table */
    body * {
        visibility: hidden;
    }
    
    .table-container,
    .table-container * {
        visibility: visible;
    }
    
    .table-container {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        box-shadow: none;
        border-radius: 0;
    }
    
    /* Hide filter, buttons, and stats */
    .filter-card,
    .btn,
    .stats-container,
    .page-header {
        display: none !important;
    }
    
    /* Reset table styling for print */
    .table-header {
        background: white !important;
        color: black !important;
        border-bottom: 2px solid #000;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    .table thead th {
        background: #f0f0f0 !important;
        color: black !important;
        border: 1px solid #ddd !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    .table tbody td {
        border: 1px solid #ddd !important;
        color: black !important;
    }
    
    .table tbody tr:hover {
        background-color: transparent !important;
    }
    
    /* Ensure payment badges are visible */
    .payment-badge {
        border: 1px solid #000;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    /* Add print header */
    .table-container::before {
        content: "Transaction History Report";
        display: block;
        text-align: center;
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 20px;
        padding: 10px;
    }
    
    /* Add print date */
    .table-header::after {
        content: "Printed on: " attr(data-print-date);
        display: block;
        font-size: 12px;
        margin-top: 5px;
    }
    
    /* Adjust table for better print layout */
    .table {
        font-size: 11px;
    }
    
    .table thead th,
    .table tbody td {
        padding: 8px 4px !important;
    }
    
    /* Page break settings */
    .table {
        page-break-inside: auto;
    }
    
    .table tr {
        page-break-inside: avoid;
        page-break-after: auto;
    }
    
    .table thead {
        display: table-header-group;
    }
    
    .table tfoot {
        display: table-footer-group;

        .stat-icon {
    font-size: 2rem;
    color: #0d6efd; /* Bootstrap primary color */
    margin-bottom: 0.5rem;
}

    }
}
    </style>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">


</head>
<body>
<div class="container mt-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h2><i class="bi bi-receipt-cutoff me-2"></i>Transaction History</h2>
            <p class="subtitle">View and manage all your processed transactions</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-receipt"></i>
            </div>
            <div class="stat-label">Total Transactions</div>
            <div class="stat-value"><?php echo number_format($stats['total_transactions']); ?></div>
        </div>

        <div class="stat-card success">
            <div class="stat-icon">
                <i class="bi bi-currency-dollar"></i>
            </div>
            <div class="stat-label">Total Amount</div>
            <div class="stat-value">₱<?php echo number_format($stats['total_amount'] ?? 0, 2); ?></div>
        </div>

        <div class="stat-card info">
            <div class="stat-icon">
                <i class="bi bi-graph-up"></i>
            </div>
            <div class="stat-label">Average Transaction</div>
            <div class="stat-value">₱<?php echo number_format($stats['avg_amount'] ?? 0, 2); ?></div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="filter-card">
        <div class="filter-header">
            <i class="bi bi-funnel"></i>
            <h5>Filter Transactions</h5>
        </div>
        <form method="GET" class="d-flex gap-3 align-items-end">
            <div class="flex-fill">
                <label for="date_filter" class="form-label">Time Period</label>
                <select name="date_filter" id="date_filter" class="form-select">
                    <option value="all" <?php echo ($date_filter === 'all') ? 'selected' : ''; ?>>All Time</option>
                    <option value="today" <?php echo ($date_filter === 'today') ? 'selected' : ''; ?>>Today</option>
                    <option value="week" <?php echo ($date_filter === 'week') ? 'selected' : ''; ?>>This Week</option>
                    <option value="month" <?php echo ($date_filter === 'month') ? 'selected' : ''; ?>>This Month</option>
                    <option value="3months" <?php echo ($date_filter === '3months') ? 'selected' : ''; ?>>Last 3 Months</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-search me-1"></i>Apply Filter
            </button>
            <button type="button" class="btn btn-success" onclick="window.print()">
                <i class="bi bi-printer me-1"></i>Print
            </button>
        </form>
    </div>

    <!-- Transaction Table -->
    <div class="table-container">
        <div class="table-header">
            <h5>
                <i class="bi bi-list-ul me-2"></i>Transaction Records
            </h5>
            <span><?php echo $transactions->num_rows; ?> transactions</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Transaction ID</th>
                        <th>Type</th>
                        <th>Customer Name</th>
                        <th>Item</th>
                        <th>Date & Time</th>
                        <th>Amount</th>
                        <th>Payment Method</th>
                        <th>Handler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($transactions && $transactions->num_rows > 0): ?>
                        <?php $id = 1; ?>
                        <?php while ($row = $transactions->fetch_assoc()): ?>
                            <?php
                                $payment_class = 'payment-cash';
                                $payment_method = strtolower($row['payment_method']);
                                if (strpos($payment_method, 'card') !== false || strpos($payment_method, 'credit') !== false) {
                                    $payment_class = 'payment-card';
                                } elseif (strpos($payment_method, 'online') !== false || strpos($payment_method, 'gcash') !== false) {
                                    $payment_class = 'payment-online';
                                } elseif (strpos($payment_method, 'bank') !== false) {
                                    $payment_class = 'payment-bank';
                                }
                            ?>
                            <tr>
                                <td><strong><?php echo $id++; ?></strong></td>
                                <td>
                                    <span class="transaction-id">#<?php echo str_pad($row['transaction_id'], 6, '0', STR_PAD_LEFT); ?></span>
                                </td>
                                <td>
                                    <span class="badge 
                                        <?php 
                                            echo ($row['transaction_type'] == 'Payment') ? 'bg-success' : 
                                                (($row['transaction_type'] == 'New Loan') ? 'bg-primary' : 'bg-secondary');
                                        ?>">
                                        <?php echo htmlspecialchars($row['transaction_type']); ?>
                                    </span>
                                </td>

                                <td><?php echo htmlspecialchars($row['customer_first_name'] . ' ' . $row['customer_last_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['item_model']); ?></td>
                                <td><?php echo date('M d, Y h:i A', strtotime($row['transaction_date'])); ?></td>
                                <td class="amount-cell">₱<?php echo number_format($row['amount'], 2); ?></td>
                                <td>
                                    <span class="payment-badge <?php echo $payment_class; ?>">
                                        <?php echo htmlspecialchars($row['payment_method']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($row['handler_first_name'] . ' ' . $row['handler_last_name']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <h5>No Transactions Found</h5>
                                    <p>There are no transactions for the selected time period.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="path-to-bootstrap.js"></script>
<?php include('footer.php'); ?>
</body>
</html>