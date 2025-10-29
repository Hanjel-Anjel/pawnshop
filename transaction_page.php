<?php
@include('d:\xampp\htdocs\pawnshop\config.php');

// Define filters
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$transaction_type = isset($_GET['transaction_type']) ? $_GET['transaction_type'] : 'all';

// Build base WHERE conditions
$conditions = [];

// Date filter
switch ($filter) {
    case 'today':
        $conditions[] = "DATE(transactions.transaction_date) = CURDATE()";
        break;
    case 'last_week':
        $conditions[] = "WEEK(transactions.transaction_date) = WEEK(CURDATE()) - 1 
                         AND YEAR(transactions.transaction_date) = YEAR(CURDATE())";
        break;
    case 'last_month':
        $conditions[] = "MONTH(transactions.transaction_date) = MONTH(CURDATE()) - 1 
                         AND YEAR(transactions.transaction_date) = YEAR(CURDATE())";
        break;
    case 'last_3_months':
        $conditions[] = "transactions.transaction_date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)";
        break;
    case 'last_6_months':
        $conditions[] = "transactions.transaction_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)";
        break;
}

// Transaction type filter
if ($transaction_type !== 'all') {
    $conditions[] = "transactions.transaction_type = '" . mysqli_real_escape_string($conn, $transaction_type) . "'";
}

// Combine conditions
$whereClause = '';
if (!empty($conditions)) {
    $whereClause = "WHERE " . implode(' AND ', $conditions);
}

// Fetch transactions
$query = "
    SELECT 
        transactions.transaction_id, 
        transactions.transaction_date, 
        transactions.amount, 
        transactions.payment_method,
        transactions.transaction_type,
        customers.first_name, 
        customers.last_name, 
        items.model, 
        users.first_name AS handler_first_name, 
        users.last_name AS handler_last_name
    FROM transactions 
    JOIN custumer_info AS customers ON transactions.customer_id = customers.customer_id 
    JOIN items ON transactions.item_id = items.item_id 
    JOIN users ON transactions.handled_by = users.user_id 
    $whereClause
    ORDER BY transactions.transaction_date DESC
";

$result = mysqli_query($conn, $query);

// Calculate totals
$totalAmount = 0;
$transactionCount = 0;
if ($result) {
    $transactionCount = mysqli_num_rows($result);
    mysqli_data_seek($result, 0);
    while ($row = mysqli_fetch_assoc($result)) {
        $totalAmount += $row['amount'];
    }
    mysqli_data_seek($result, 0);
}

// Filter display name
$filterDisplay = match ($filter) {
    'today' => 'Today',
    'last_week' => 'Last Week',
    'last_month' => 'Last Month',
    'last_3_months' => 'Last 3 Months',
    'last_6_months' => 'Last 6 Months',
    default => 'All Time'
};

include('header.php');
?>

<title>Transactions - Modern Dashboard</title>

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
        font-family: 'Roboto', 'Segoe UI', sans-serif;
        color: var(--text-primary);
    }

    .page-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        color: white;
        padding: 2rem 0;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-md);
    }

    .page-header h1 {
        font-weight: 500;
        font-size: 2rem;
        margin: 0;
        letter-spacing: 0.5px;
    }

    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: var(--card-bg);
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: var(--shadow);
        transition: all 0.3s ease;
        border-left: 4px solid var(--primary-color);
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
    }

    .stat-card.accent {
        border-left-color: var(--accent-color);
    }

    .stat-label {
        color: var(--text-secondary);
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
        font-weight: 500;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 500;
        color: var(--text-primary);
    }

    .filter-card {
        background: var(--card-bg);
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: var(--shadow);
        margin-bottom: 2rem;
    }

    .filter-card label {
        font-weight: 500;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
        display: block;
    }

    .form-select {
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        transition: all 0.3s ease;
        min-width: 200px;
    }

    .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px var(--primary-light);
        outline: none;
    }

    .btn-apply {
        background-color: var(--primary-color);
        color: white;
        border: none;
        border-radius: 8px;
        padding: 0.75rem 2rem;
        font-weight: 500;
        transition: all 0.3s ease;
        cursor: pointer;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.875rem;
    }

    .btn-apply:hover {
        background-color: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .table-card {
        background: var(--card-bg);
        border-radius: 12px;
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .modern-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin: 0;
    }

    .modern-table thead {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        color: white;
    }

    .modern-table thead th {
        padding: 1.25rem 1rem;
        text-align: left;
        font-weight: 500;
        text-transform: uppercase;
        font-size: 0.813rem;
        letter-spacing: 0.5px;
        border: none;
    }

    .modern-table tbody tr {
        transition: all 0.3s ease;
        border-bottom: 1px solid #f0f0f0;
    }

    .modern-table tbody tr:hover {
        background-color: var(--primary-light);
        transform: scale(1.01);
    }

    .modern-table tbody tr:last-child {
        border-bottom: none;
    }

    .modern-table tbody td {
        padding: 1.25rem 1rem;
        color: var(--text-primary);
        border: none;
    }

    .transaction-id {
        font-weight: 500;
        color: var(--primary-color);
    }

    .amount {
        font-weight: 600;
        color: var(--success-color);
        font-size: 1.1rem;
    }

    .badge {
        display: inline-block;
        padding: 0.375rem 0.75rem;
        border-radius: 20px;
        font-size: 0.813rem;
        font-weight: 500;
        text-transform: capitalize;
    }

        /* Payment Method Badges */
        .badge-cash {
            background-color: #e8f5e9; /* light green background */
            color: #2e7d32;           /* dark green text */
        }

        .badge-card {
            background-color: #e3f2fd; /* light blue background */
            color: #1565c0;           /* blue text */
        }

        .badge-online {
            background-color: #fff3e0; /* light orange background */
            color: #ef6c00;           /* orange text */
        }


    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: var(--text-secondary);
    }

    .empty-state svg {
        width: 120px;
        height: 120px;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    /* Print Header Styles - Hidden on screen */
    .print-header {
        display: none;
    }

    @media (max-width: 768px) {
        .stats-container {
            grid-template-columns: 1fr;
        }

        .page-header h1 {
            font-size: 1.5rem;
        }

        .modern-table {
            font-size: 0.875rem;
        }

        .modern-table thead th,
        .modern-table tbody td {
            padding: 0.875rem 0.5rem;
        }

                /* === Transaction Type Row Highlights === */
        .modern-table tbody tr.loan-row {
            background-color: #e3f2fd; /* Light blue */
            border-left: 5px solid var(--primary-color);
        }

        .modern-table tbody tr.payment-row {
            background-color: #e8f5e9; /* Light green */
            border-left: 5px solid var(--success-color);
        }

        .modern-table tbody tr.loan-row:hover {
            background-color: #bbdefb;
        }

        .modern-table tbody tr.payment-row:hover {
            background-color: #c8e6c9;
        }



    }
    

    /* ========================================
       PRINT STYLES - COMPLETE OVERRIDE
       ======================================== */
    @media print {
        /* STEP 1: Hide absolutely everything first */
        * {
            visibility: hidden !important;
        }

        /* STEP 2: Reset html and body completely */
        html, body {
            width: 100% !important;
            height: auto !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: visible !important;
            background: white !important;
        }

        /* STEP 3: Show only print elements */
        .print-header,
        .print-header *,
        .print-container,
        .print-container *,
        .table-card,
        .table-card *,
        .modern-table,
        .modern-table * {
            visibility: visible !important;
        }

        /* STEP 4: Position print header */
        .print-header {
            display: block !important;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            width: 100%;
            text-align: center;
            padding: 20px;
            border-bottom: 3px solid #333;
            background: white;
            z-index: 1000;
        }

        .print-header h1 {
            margin: 0 0 10px 0 !important;
            font-size: 28px !important;
            color: #333 !important;
            font-weight: bold !important;
        }

        .print-header .print-info {
            display: flex !important;
            justify-content: space-between !important;
            margin-top: 15px !important;
            font-size: 12px !important;
            color: #666 !important;
        }

        .print-header .print-stats {
            display: flex !important;
            justify-content: center !important;
            gap: 40px !important;
            margin-top: 10px !important;
            font-size: 14px !important;
        }

        .print-header .print-stat {
            font-weight: bold !important;
        }

        /* STEP 5: Position table container */
        .print-container {
            display: block !important;
            position: absolute;
            top: 160px;
            left: 0;
            right: 0;
            width: 100%;
            padding: 0 20px;
        }

        /* STEP 6: Table styles */
        .table-card {
            display: block !important;
            width: 100% !important;
            box-shadow: none !important;
            border-radius: 0 !important;
            background: white !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .table-responsive {
            overflow: visible !important;
        }

        .modern-table {
            width: 100% !important;
            border-collapse: collapse !important;
            font-size: 10px !important;
        }

        .modern-table thead {
            display: table-header-group !important;
            background: #333 !important;
        }

        .modern-table thead th {
            padding: 10px 6px !important;
            border: 1px solid #333 !important;
            background: #333 !important;
            color: white !important;
            font-weight: bold !important;
            text-align: left !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .modern-table tbody {
            display: table-row-group !important;
        }

        .modern-table tbody tr {
            display: table-row !important;
            page-break-inside: avoid !important;
            border-bottom: 1px solid #ddd !important;
        }

        .modern-table tbody td {
            display: table-cell !important;
            padding: 8px 6px !important;
            border: 1px solid #ddd !important;
            color: #333 !important;
        }

        /* STEP 7: Fix specific elements */
        .transaction-id {
            color: #333 !important;
            font-weight: bold !important;
        }

        .amount {
            color: #000 !important;
            font-weight: bold !important;
        }

        .badge {
            display: inline-block !important;
            border: 1px solid #333 !important;
            background: white !important;
            color: #333 !important;
            padding: 2px 6px !important;
            font-size: 9px !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* STEP 8: Page settings */
        @page {
            size: A4 landscape;
            margin: 1cm;
        }

        /* STEP 9: Hide empty state if present */
        .empty-state {
            display: none !important;
        }
    }
</style>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<div class="page-header">
    <div class="container">
        <h1>Transaction Management</h1>
    </div>
</div>

<div class="container">
    <!-- Stats Cards -->
    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-label">Total Transactions</div>
            <div class="stat-value"><?= number_format($transactionCount) ?></div>
        </div>
        <div class="stat-card accent">
            <div class="stat-label">Total Amount</div>
            <div class="stat-value">₱<?= number_format($totalAmount, 2) ?></div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="filter-card">
        <form method="get" class="d-flex align-items-end gap-3 flex-wrap">
            <div class="flex-grow-1">
                <label for="filter">Filter by Date Range</label>
                <select name="filter" id="filter" class="form-select">
                    <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All Transactions</option>
                    <option value="today" <?= $filter === 'today' ? 'selected' : '' ?>>Today</option>
                    <option value="last_week" <?= $filter === 'last_week' ? 'selected' : '' ?>>Last Week</option>
                    <option value="last_month" <?= $filter === 'last_month' ? 'selected' : '' ?>>Last Month</option>
                    <option value="last_3_months" <?= $filter === 'last_3_months' ? 'selected' : '' ?>>Last 3 Months</option>
                    <option value="last_6_months" <?= $filter === 'last_6_months' ? 'selected' : '' ?>>Last 6 Months</option>
                </select>
            </div>

            <div class="flex-grow-1">
                <label for="transaction_type">Transaction Type</label>
                <select name="transaction_type" id="transaction_type" class="form-select">
                    <option value="all" <?= $transaction_type === 'all' ? 'selected' : '' ?>>All Types</option>
                    <option value="Loan" <?= $transaction_type === 'Loan' ? 'selected' : '' ?>>Loan</option>
                    <option value="Payment" <?= $transaction_type === 'Payment' ? 'selected' : '' ?>>Payment</option>
                </select>
            </div>

            <button type="submit" class="btn-apply">Apply Filter</button>
            <button type="button" class="btn btn-success" onclick="window.print()">
                <i class="bi bi-printer me-1"></i>Print
            </button>
        </form>
    </div>

    <!-- Print Header -->
    <div class="print-header">
        <h1>Transaction Report</h1>
        <div class="print-info">
            <div><strong>Period:</strong> <?= $filterDisplay ?></div>
            <div><strong>Generated:</strong> <?= date('F d, Y h:i A') ?></div>
        </div>
        <div class="print-stats">
            <div class="print-stat">Total Transactions: <?= number_format($transactionCount) ?></div>
            <div class="print-stat">Total Amount: ₱<?= number_format($totalAmount, 2) ?></div>
        </div>
    </div>

    <!-- Table -->
    <div class="print-container">
        <div class="table-card">
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Transaction ID</th>
                            <th>Customer</th>
                            <th>Item</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Type</th>
                            <th>Payment</th>
                            <th>Handler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result && mysqli_num_rows($result) > 0) {
                            $id = 1;
                            while ($row = $result->fetch_assoc()) {
                                $idDisplay = $id;
                                $transactionId = str_pad($row['transaction_id'], 4, '0', STR_PAD_LEFT);
                                $customerName = htmlspecialchars($row['first_name'] . ' ' . $row['last_name']);
                                $itemModel = htmlspecialchars($row['model']);
                                $transactionDate = date('M d, Y', strtotime($row['transaction_date']));
                                $amount = number_format($row['amount'], 2);
                                $paymentMethod = htmlspecialchars($row['payment_method']);
                                $handlerName = htmlspecialchars($row['handler_first_name'] . ' ' . $row['handler_last_name']);
                                $badgeClass = ($row['transaction_type'] === 'Loan') ? 'badge-card' : 'badge-cash';
                                $transactionType = htmlspecialchars($row['transaction_type']);

                                echo "
                                    <tr>
                                        <td>{$idDisplay}</td>
                                        <td><span class='transaction-id'>#{$transactionId}</span></td>
                                        <td>{$customerName}</td>
                                        <td>{$itemModel}</td>
                                        <td>{$transactionDate}</td>
                                        <td><span class='amount'>₱{$amount}</span></td>
                                        <td><span class='badge {$badgeClass}'>{$transactionType}</span></td>
                                        <td><span class='badge badge-online'>{$paymentMethod}</span></td>
                                        <td>{$handlerName}</td>
                                    </tr>
                                ";

                                $id++;
                            }

                        } else {
                            echo "<tr><td colspan='9'>
                                <div class='empty-state'>
                                    <svg viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'>
                                        <circle cx='12' cy='12' r='10'></circle>
                                        <line x1='12' y1='8' x2='12' y2='12'></line>
                                        <line x1='12' y1='16' x2='12.01' y2='16'></line>
                                    </svg>
                                    <h3>No Transactions Found</h3>
                                    <p>Try adjusting your filters</p>
                                </div>
                            </td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Auto-submit filter script -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll('#filter, #transaction_type').forEach(select => {
        select.addEventListener('change', function() {
            this.form.submit();
        });
    });
});
</script>