<?php
session_start();
@include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: customer_login_page.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch active pawned items
$active_items_query = "
    SELECT i.brand AS item_name, i.model, i.loan_amount, i.due_date 
    FROM items i
    JOIN custumer_info c ON i.customer_id = c.customer_id
    WHERE c.user_id = $user_id AND i.item_status = 'Pawned'";
$active_items = mysqli_query($conn, $active_items_query);

// Count active items
$active_count = mysqli_num_rows($active_items);

// Fetch transaction history
$history_query = "
    SELECT t.transaction_date, t.amount, t.payment_method, t.handled_by
    FROM transactions t
    JOIN custumer_info c ON t.customer_id = c.customer_id
    WHERE c.user_id = $user_id
    ORDER BY t.transaction_date DESC";
$history = mysqli_query($conn, $history_query);

// Calculate total transaction amount
$total_amount = 0;
$history_count = mysqli_num_rows($history);
mysqli_data_seek($history, 0);
while ($txn = mysqli_fetch_assoc($history)) {
    $total_amount += $txn['amount'];
}
mysqli_data_seek($history, 0);

// Logout
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: customer_login_page.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Customer Dashboard - ArMaTech Pawnshop</title>

<!-- Bootstrap & Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
/* === GLOBAL === */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    font-family: 'Poppins', sans-serif;
    color: #333;
    min-height: 100vh;
    position: relative;
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

/* === NAVBAR === */
.navbar {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    padding: 1rem 0;
    position: relative;
    z-index: 100;
}

.navbar-brand {
    font-weight: 700;
    color: #667eea !important;
    font-size: 1.5rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.navbar-brand i {
    color: #ffc107;
    font-size: 1.8rem;
}

.navbar .user-info {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 8px 20px;
    border-radius: 25px;
    font-weight: 500;
    margin-right: 15px;
}

.navbar .btn-logout {
    background: linear-gradient(135deg, #ffc107, #e0a800);
    border: none;
    color: #000;
    font-weight: 600;
    padding: 10px 25px;
    border-radius: 25px;
    transition: all 0.3s ease;
    box-shadow: 0 3px 10px rgba(255, 193, 7, 0.3);
}

.navbar .btn-logout:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(255, 193, 7, 0.5);
    color: #000;
}

/* === DASHBOARD CONTAINER === */
.dashboard-container {
    position: relative;
    z-index: 1;
    margin-top: 40px;
    margin-bottom: 40px;
}

/* === WELCOME SECTION === */
.welcome-section {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    animation: slideDown 0.6s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.welcome-section h2 {
    color: #667eea;
    font-weight: 700;
    margin-bottom: 10px;
}

.welcome-section p {
    color: #666;
    margin: 0;
}

/* === STATS CARDS === */
.stats-row {
    margin-bottom: 30px;
}

.stat-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 16px;
    padding: 25px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    animation: fadeInUp 0.6s ease-out;
    animation-fill-mode: both;
}

.stat-card:nth-child(1) { animation-delay: 0.1s; }
.stat-card:nth-child(2) { animation-delay: 0.2s; }
.stat-card:nth-child(3) { animation-delay: 0.3s; }

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
}

.stat-card .icon {
    width: 60px;
    height: 60px;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    margin-bottom: 15px;
}

.stat-card.items .icon {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
}

.stat-card.transactions .icon {
    background: linear-gradient(135deg, #f093fb, #f5576c);
    color: white;
}

.stat-card.amount .icon {
    background: linear-gradient(135deg, #ffc107, #e0a800);
    color: white;
}

.stat-card h3 {
    font-size: 2rem;
    font-weight: 700;
    color: #333;
    margin: 10px 0;
}

.stat-card p {
    color: #666;
    font-size: 0.9rem;
    margin: 0;
    font-weight: 500;
}

/* === DATA CARDS === */
.data-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    animation: fadeInUp 0.6s ease-out 0.4s;
    animation-fill-mode: both;
    margin-bottom: 30px;
}

.data-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
}

.data-card-header {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 20px 25px;
    font-weight: 600;
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.data-card-header.success {
    background: linear-gradient(135deg, #11998e, #38ef7d);
}

.data-card-body {
    padding: 25px;
}

/* === TABLE === */
.table-container {
    overflow-x: auto;
    border-radius: 10px;
}

.custom-table {
    width: 100%;
    margin: 0;
}

.custom-table thead {
    background: #f8f9fa;
}

.custom-table thead th {
    color: #667eea;
    font-weight: 600;
    padding: 15px;
    border: none;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.custom-table tbody tr {
    transition: all 0.2s ease;
    border-bottom: 1px solid #f0f0f0;
}

.custom-table tbody tr:hover {
    background: rgba(102, 126, 234, 0.05);
}

.custom-table tbody td {
    padding: 15px;
    color: #333;
    vertical-align: middle;
}

.badge-status {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
}

.badge-active {
    background: linear-gradient(135deg, #11998e, #38ef7d);
    color: white;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #999;
}

.empty-state i {
    font-size: 3rem;
    color: #ddd;
    margin-bottom: 15px;
}

.empty-state p {
    margin: 0;
    font-size: 1.1rem;
}

/* === MODAL === */
.modal-content {
    border-radius: 20px;
    border: none;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
}

.modal-header {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border-radius: 20px 20px 0 0;
    padding: 20px 25px;
}

.modal-header .btn-close {
    filter: brightness(0) invert(1);
}

.modal-body {
    padding: 30px 25px;
}

.modal-footer {
    border-top: none;
    padding: 20px 25px;
}

.modal-footer .btn {
    border-radius: 25px;
    padding: 10px 25px;
    font-weight: 600;
}

/* === RESPONSIVE === */
@media (max-width: 768px) {
    .navbar-brand {
        font-size: 1.2rem;
    }
    
    .navbar .user-info {
        display: none;
    }
    
    .welcome-section h2 {
        font-size: 1.5rem;
    }
    
    .stat-card {
        margin-bottom: 15px;
    }
    
    .stat-card h3 {
        font-size: 1.5rem;
    }
    
    .custom-table {
        font-size: 0.85rem;
    }
    
    .custom-table thead th,
    .custom-table tbody td {
        padding: 10px;
    }
}
</style>
</head>

<body>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="#">
            <i class="fas fa-mobile-alt"></i>
            <span>ArMaTech Pawnshop</span>
        </a>
        <div class="d-flex align-items-center">
            <span class="user-info d-none d-md-inline-block">
                <i class="fas fa-user-circle me-2"></i><?php echo htmlspecialchars($_SESSION['user_name']); ?>
            </span>
            <button class="btn btn-logout" data-bs-toggle="modal" data-bs-target="#logoutModal">
                <i class="fas fa-sign-out-alt me-2"></i>Logout
            </button>
        </div>
    </div>
</nav>

<!-- Dashboard Container -->
<div class="container dashboard-container">
    <!-- Welcome Section -->
    <div class="welcome-section">
        <h2><i class="fas fa-hand-wave me-2" style="color: #ffc107;"></i>Welcome Back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h2>
        <p>Here's an overview of your pawnshop activity</p>
    </div>

    <!-- Stats Row -->
    <div class="row stats-row g-4">
        <div class="col-md-4">
            <div class="stat-card items">
                <div class="icon">
                    <i class="fas fa-box"></i>
                </div>
                <h3><?php echo $active_count; ?></h3>
                <p>Active Pawned Items</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card transactions">
                <div class="icon">
                    <i class="fas fa-receipt"></i>
                </div>
                <h3><?php echo $history_count; ?></h3>
                <p>Total Transactions</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card amount">
                <div class="icon">
                    <i class="fas fa-peso-sign"></i>
                </div>
                <h3>₱<?php echo number_format($total_amount, 2); ?></h3>
                <p>Total Amount</p>
            </div>
        </div>
    </div>

    <!-- Data Cards Row -->
    <div class="row g-4">
        <!-- Active Pawned Items -->
        <div class="col-lg-6">
            <div class="data-card">
                <div class="data-card-header">
                    <i class="fas fa-hand-holding-usd"></i>
                    <span>Active Pawned Items</span>
                </div>
                <div class="data-card-body">
                    <?php 
                    mysqli_data_seek($active_items, 0);
                    if (mysqli_num_rows($active_items) > 0) { ?>
                    <div class="table-container">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>Brand</th>
                                    <th>Model</th>
                                    <th>Loan Amount</th>
                                    <th>Due Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($item = mysqli_fetch_assoc($active_items)) { ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($item['item_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($item['model']); ?></td>
                                    <td><strong style="color: #ffc107;">₱<?php echo number_format($item['loan_amount'], 2); ?></strong></td>
                                    <td>
                                        <span class="badge-status badge-active">
                                            <i class="far fa-calendar me-1"></i><?php echo date('M d, Y', strtotime($item['due_date'])); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <?php } else { ?>
                    <div class="empty-state">
                        <i class="fas fa-box-open"></i>
                        <p>No active pawned items found</p>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <!-- Transaction History -->
        <div class="col-lg-6">
            <div class="data-card">
                <div class="data-card-header success">
                    <i class="fas fa-history"></i>
                    <span>Transaction History</span>
                </div>
                <div class="data-card-body">
                    <?php 
                    mysqli_data_seek($history, 0);
                    if (mysqli_num_rows($history) > 0) { ?>
                    <div class="table-container">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($txn = mysqli_fetch_assoc($history)) { ?>
                                <tr>
                                    <td>
                                        <i class="far fa-calendar-alt me-2" style="color: #667eea;"></i>
                                        <?php echo date('M d, Y', strtotime($txn['transaction_date'])); ?>
                                    </td>
                                    <td><strong style="color: #11998e;">₱<?php echo number_format($txn['amount'], 2); ?></strong></td>
                                    <td>
                                        <i class="fas fa-credit-card me-1" style="color: #764ba2;"></i>
                                        <?php echo htmlspecialchars($txn['payment_method']); ?>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <?php } else { ?>
                    <div class="empty-state">
                        <i class="fas fa-file-invoice"></i>
                        <p>No transaction history found</p>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Logout Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logoutModalLabel">
                    <i class="fas fa-sign-out-alt me-2"></i>Confirm Logout
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-question-circle" style="font-size: 3rem; color: #667eea; margin-bottom: 15px;"></i>
                <p class="mb-0" style="font-size: 1.1rem; color: #333;">Are you sure you want to log out?</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <a href="logout.php" class="btn" style="background: linear-gradient(135deg, #ffc107, #e0a800); color: #000; font-weight: 600;">
                    <i class="fas fa-sign-out-alt me-2"></i>Yes, Logout
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>