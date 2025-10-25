<?php 
include('config.php');

// === BALANCE TODAY ===
$sql_balance_today = "
    SELECT SUM(total_balance) as total 
    FROM items 
    WHERE DATE(pawn_date) = CURDATE()
";
$total_balance_today = $conn->query($sql_balance_today)->fetch_assoc()['total'] ?? 0;

// === BALANCE (Last 7 Days) ===
$sql_balance_last_7_days = "
    SELECT SUM(total_balance) as total 
    FROM items 
    WHERE DATE(pawn_date) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
";
$total_balance_last_7_days = $conn->query($sql_balance_last_7_days)->fetch_assoc()['total'] ?? 0;

// === CUSTOMERS ===
$sql_total_customers = "
    SELECT COUNT(DISTINCT c.customer_id) as total 
    FROM custumer_info c
    INNER JOIN items i ON c.customer_id = i.customer_id
    WHERE i.item_status = 'Pawned'
";
$total_customers = $conn->query($sql_total_customers)->fetch_assoc()['total'] ?? 0;

// === INTEREST ===
$sql_interest_today = "
    SELECT SUM(interest_rate) as total 
    FROM items 
    WHERE DATE(pawn_date) = CURDATE()
";
$total_interest_today = $conn->query($sql_interest_today)->fetch_assoc()['total'] ?? 0;

$sql_interest_last_7_days = "
    SELECT SUM(interest_rate) as total 
    FROM items 
    WHERE DATE(pawn_date) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
";
$total_interest_last_7_days = $conn->query($sql_interest_last_7_days)->fetch_assoc()['total'] ?? 0;

include('header_employee.php');
?>

<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
    .stat-card {
        color: #fff;
        border: none;
        border-radius: 20px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    }
    .icon-circle {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,0.2);
        margin: 0 auto 15px;
    }
</style>

<div class="container mt-5">
    <div class="text-center mb-5">
        <h1 class="fw-bold text-primary"><?= htmlspecialchars($_SESSION['user_name']); ?> Dashboard</h1>
        <p class="text-muted">Performance overview for the past few days</p>
    </div>

    <div class="row g-4 text-center">
        <!-- Balance Today -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card stat-card shadow h-100" style="background: linear-gradient(135deg, #1e3c72, #2a5298);">
                <div class="card-body">
                    <div class="icon-circle">
                        <i class="bi bi-cash-coin display-5"></i>
                    </div>
                    <h5 class="fw-semibold">Balance Today</h5>
                    <p class="display-6 fw-bold">₱<?= number_format($total_balance_today, 2); ?></p>
                </div>
            </div>
        </div>

        <!-- Balance (Last 7 Days) -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card stat-card shadow h-100" style="background: linear-gradient(135deg, #283593, #5c6bc0);">
                <div class="card-body">
                    <div class="icon-circle">
                        <i class="bi bi-calendar-week display-5"></i>
                    </div>
                    <h5 class="fw-semibold">Balance (Last 7 Days)</h5>
                    <p class="display-6 fw-bold">₱<?= number_format($total_balance_last_7_days, 2); ?></p>
                </div>
            </div>
        </div>

        <!-- Total Customers -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card stat-card shadow h-100" style="background: linear-gradient(135deg, #512da8, #9575cd);">
                <div class="card-body">
                    <div class="icon-circle">
                        <i class="bi bi-people-fill display-5"></i>
                    </div>
                    <h5 class="fw-semibold">Total Customers</h5>
                    <p class="display-6 fw-bold"><?= $total_customers; ?></p>
                </div>
            </div>
        </div>

        <!-- Interest Today -->
        <div class="col-12 col-md-6 col-lg-6">
            <div class="card stat-card shadow h-100" style="background: linear-gradient(135deg, #283e51, #485563);">
                <div class="card-body">
                    <div class="icon-circle">
                        <i class="bi bi-graph-up-arrow display-5"></i>
                    </div>
                    <h5 class="fw-semibold">Interest Today</h5>
                    <p class="display-6 fw-bold">₱<?= number_format($total_interest_today, 2); ?></p>
                </div>
            </div>
        </div>

        <!-- Interest (Last 7 Days) -->
        <div class="col-12 col-md-6 col-lg-6">
            <div class="card stat-card shadow h-100" style="background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);">
                <div class="card-body">
                    <div class="icon-circle">
                        <i class="bi bi-bar-chart-fill display-5"></i>
                    </div>
                    <h5 class="fw-semibold">Interest (Last 7 Days)</h5>
                    <p class="display-6 fw-bold">₱<?= number_format($total_interest_last_7_days, 2); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>
