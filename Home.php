<?php 
include('config.php');

/* ===============================
   🪙 BALANCE (ACTIVE PAWNED ITEMS)
   =============================== */
$sql_balance_today = "
    SELECT SUM(total_balance) AS total 
    FROM items 
    WHERE DATE(pawn_date) = CURDATE()
    AND item_status = 'Pawned'
";
$total_balance_today = $conn->query($sql_balance_today)->fetch_assoc()['total'] ?? 0;

$sql_balance_last_7_days = "
    SELECT SUM(total_balance) AS total 
    FROM items 
    WHERE DATE(pawn_date) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    AND item_status = 'Pawned'
";
$total_balance_last_7_days = $conn->query($sql_balance_last_7_days)->fetch_assoc()['total'] ?? 0;

/* ===============================
   👥 CUSTOMERS
   =============================== */
$sql_total_customers = "
    SELECT COUNT(DISTINCT c.customer_id) AS total
    FROM custumer_info c
    LEFT JOIN customer_users u ON c.user_id = u.user_id
";
$total_customers = $conn->query($sql_total_customers)->fetch_assoc()['total'] ?? 0;

$sql_pawned_customers = "
    SELECT COUNT(DISTINCT c.customer_id) AS total
    FROM custumer_info c
    INNER JOIN items i ON c.customer_id = i.customer_id
    WHERE i.item_status = 'Pawned'
";
$total_pawned_customers = $conn->query($sql_pawned_customers)->fetch_assoc()['total'] ?? 0;

$sql_active_customers = "
    SELECT COUNT(*) AS total 
    FROM custumer_info 
    WHERE status = 'Active'
";
$total_active_customers = $conn->query($sql_active_customers)->fetch_assoc()['total'] ?? 0;

$sql_inactive_customers = "
    SELECT COUNT(*) AS total 
    FROM custumer_info 
    WHERE status = 'Inactive'
";
$total_inactive_customers = $conn->query($sql_inactive_customers)->fetch_assoc()['total'] ?? 0;

/* ===============================
   💰 INTEREST (in Pesos)
   =============================== */
$sql_interest_today = "
    SELECT SUM(interest_rate) AS total 
    FROM items 
    WHERE DATE(pawn_date) = CURDATE()
    AND item_status = 'Pawned'
";
$total_interest_today = $conn->query($sql_interest_today)->fetch_assoc()['total'] ?? 0;

$sql_interest_last_7_days = "
    SELECT SUM(interest_rate) AS total 
    FROM items 
    WHERE DATE(pawn_date) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    AND item_status = 'Pawned'
";
$total_interest_last_7_days = $conn->query($sql_interest_last_7_days)->fetch_assoc()['total'] ?? 0;

/* ===============================
   🧾 PAWNED / REDEEMED VALUE
   =============================== */
$sql_total_pawned = "
    SELECT SUM(loan_amount) AS total 
    FROM items 
    WHERE item_status = 'Pawned'
";
$total_pawned = $conn->query($sql_total_pawned)->fetch_assoc()['total'] ?? 0;

$sql_total_redeemed = "
    SELECT COUNT(*) AS total 
    FROM items 
    WHERE item_status = 'Fully Paid'
";
$total_redeemed = $conn->query($sql_total_redeemed)->fetch_assoc()['total'] ?? 0;

/* ===============================
   ⏰ OVERDUE ITEMS
   =============================== */
$sql_overdue_items = "
    SELECT COUNT(*) AS total 
    FROM items 
    WHERE due_date < CURDATE()
    AND item_status = 'Pawned'
";
$total_overdue = $conn->query($sql_overdue_items)->fetch_assoc()['total'] ?? 0;

/* ===============================
   👷 EMPLOYEES
   =============================== */
$sql_total_employee = "SELECT COUNT(*) AS total FROM employee_details";
$total_employee = $conn->query($sql_total_employee)->fetch_assoc()['total'] ?? 0;

/* ===============================
   📊 CHART DATA (Pawned vs Redeemed - Last 7 Days)
   =============================== */
// Generate all 7 days first
$dates = [];
$pawned = [];
$redeemed = [];

for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dates[] = date('M d', strtotime($date));
    
    // Get pawned amount for this date
    $pawned_query = "
        SELECT COALESCE(SUM(loan_amount), 0) AS total
        FROM items
        WHERE DATE(pawn_date) = '$date' AND item_status = 'Pawned'
    ";
    $pawned_result = $conn->query($pawned_query);
    $pawned[] = $pawned_result->fetch_assoc()['total'] ?? 0;
    
    // Get redeemed amount for this date
    $redeemed_query = "
        SELECT COALESCE(SUM(loan_amount), 0) AS total
        FROM items
        WHERE DATE(pawn_date) = '$date' AND item_status = 'Fully Paid'
    ";
    $redeemed_result = $conn->query($redeemed_query);
    $redeemed[] = $redeemed_result->fetch_assoc()['total'] ?? 0;
}

/* ===============================
   📊 CUSTOMER STATUS CHART DATA
   =============================== */
$customer_status_query = "
    SELECT status, COUNT(*) as count
    FROM custumer_info
    GROUP BY status
";
$result_customer_status = $conn->query($customer_status_query);
$customer_statuses = [];
$customer_status_counts = [];
while ($row = $result_customer_status->fetch_assoc()) {
    $customer_statuses[] = $row['status'];
    $customer_status_counts[] = $row['count'];
}

/* ===============================
   📊 ITEM STATUS DISTRIBUTION
   =============================== */
$item_status_query = "
    SELECT item_status, COUNT(*) as count
    FROM items
    GROUP BY item_status
";
$result_item_status = $conn->query($item_status_query);
$item_statuses = [];
$item_status_counts = [];
while ($row = $result_item_status->fetch_assoc()) {
    $item_statuses[] = $row['item_status'];
    $item_status_counts[] = $row['count'];
}

include('header.php');
?>


<title>Admin Dashboard</title>



<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        --warning-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        --info-gradient: linear-gradient(135deg, #30cfd0 0%, #330867 100%);
        --danger-gradient: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
    }

    body {
        background: linear-gradient(135deg, #00416a 0%, #1c92d2 100%);
        min-height: 100vh;
        font-family: 'Inter', sans-serif;
        padding-bottom: 40px;
    }

    .dashboard-header {
        background: white;
        border-radius: 24px;
        padding: 48px 32px;
        margin-bottom: 32px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        position: relative;
        overflow: hidden;
        animation: slideDown 0.6s ease-out;
    }

    .dashboard-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 400px;
        height: 400px;
        background: var(--primary-gradient);
        border-radius: 50%;
        opacity: 0.1;
    }

    .dashboard-header h1 {
        font-size: 2.5rem;
        font-weight: 800;
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin: 0;
        position: relative;
        z-index: 1;
    }

    .dashboard-header .welcome-badge {
        display: inline-block;
        background: var(--primary-gradient);
        color: white;
        padding: 8px 20px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 12px;
        position: relative;
        z-index: 1;
    }

    .dashboard-header p {
        color: #64748b;
        font-size: 1.1rem;
        margin: 8px 0 0 0;
        position: relative;
        z-index: 1;
    }

    .stat-card {
        background: white;
        border: none;
        border-radius: 24px;
        padding: 0;
        height: 100%;
        position: relative;
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        animation: fadeInUp 0.6s ease-out forwards;
        opacity: 0;
    }

    .stat-card:nth-child(1) { animation-delay: 0.1s; }
    .stat-card:nth-child(2) { animation-delay: 0.2s; }
    .stat-card:nth-child(3) { animation-delay: 0.3s; }
    .stat-card:nth-child(4) { animation-delay: 0.4s; }
    .stat-card:nth-child(5) { animation-delay: 0.5s; }
    .stat-card:nth-child(6) { animation-delay: 0.6s; }
    .stat-card:nth-child(7) { animation-delay: 0.7s; }
    .stat-card:nth-child(8) { animation-delay: 0.8s; }

    .stat-card:hover {
        transform: translateY(-12px) scale(1.02);
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 6px;
        background: var(--primary-gradient);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.4s ease;
    }

    .stat-card:hover::before {
        transform: scaleX(1);
    }

    .stat-card-body {
        padding: 32px;
        position: relative;
        z-index: 1;
    }

    .icon-wrapper {
        width: 80px;
        height: 80px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        position: relative;
        transition: all 0.4s ease;
    }

    .stat-card:hover .icon-wrapper {
        transform: rotate(10deg) scale(1.1);
    }

    .icon-wrapper i {
        font-size: 2.5rem;
        color: white;
        position: relative;
        z-index: 1;
    }

    /* Individual card gradients */
    .card-balance-today .icon-wrapper { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .card-balance-week .icon-wrapper { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
    .card-customers .icon-wrapper { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
    .card-interest-today .icon-wrapper { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
    .card-interest-week .icon-wrapper { background: linear-gradient(135deg, #30cfd0 0%, #330867 100%); }
    .card-pawned .icon-wrapper { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); }
    .card-redeemed .icon-wrapper { background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%); }
    .card-overdue .icon-wrapper { background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%); }

    .stat-label {
        font-size: 0.95rem;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 12px;
    }

    .stat-value {
        font-size: 2.5rem;
        font-weight: 800;
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin: 0;
        line-height: 1;
    }

    .stat-card-balance-today .stat-value { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .stat-card-balance-week .stat-value { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .stat-card-customers .stat-value { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .stat-card-interest-today .stat-value { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .stat-card-interest-week .stat-value { background: linear-gradient(135deg, #30cfd0 0%, #330867 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .stat-card-pawned .stat-value { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .stat-card-redeemed .stat-value { background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .stat-card-overdue .stat-value { background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

    .chart-card {
        background: white;
        border-radius: 24px;
        padding: 40px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        animation: fadeIn 0.8s ease-out 0.9s forwards;
        opacity: 0;
        margin-top: 32px;
    }
    
    .chart-card canvas {
        max-height: 350px;
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 32px;
    }

    .chart-title {
        font-size: 1.5rem;
        font-weight: 700;
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin: 0;
    }

    .chart-badge {
        background: var(--primary-gradient);
        color: white;
        padding: 8px 20px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

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

    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    .floating-particles {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 0;
    }

    .particle {
        position: absolute;
        background: white;
        border-radius: 50%;
        opacity: 0.1;
        animation: float 15s infinite ease-in-out;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0) translateX(0); }
        50% { transform: translateY(-100px) translateX(50px); }
    }

    .container {
        position: relative;
        z-index: 1;
    }

    .charts-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 32px;
        margin-top: 32px;
    }

    @media (max-width: 992px) {
        .charts-grid {
            grid-template-columns: 1fr;
        }

        .dashboard-header h1 {
            font-size: 2rem;
        }
        
        .stat-value {
            font-size: 2rem;
        }
        
        .chart-card {
            padding: 24px;
        }

        .chart-header {
            flex-direction: column;
            gap: 12px;
            align-items: flex-start;
        }
    }

    .stat-card::after {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        animation: shimmer 3s infinite;
    }

    @keyframes shimmer {
        0% { background-position: -1000px 0; }
        100% { background-position: 1000px 0; }
    }
</style>

<!-- Floating Particles Background -->
<div class="floating-particles">
    <div class="particle" style="width: 6px; height: 6px; left: 10%; top: 20%; animation-delay: 0s;"></div>
    <div class="particle" style="width: 8px; height: 8px; left: 80%; top: 30%; animation-delay: 2s;"></div>
    <div class="particle" style="width: 5px; height: 5px; left: 50%; top: 50%; animation-delay: 4s;"></div>
    <div class="particle" style="width: 7px; height: 7px; left: 20%; top: 70%; animation-delay: 1s;"></div>
    <div class="particle" style="width: 6px; height: 6px; left: 70%; top: 60%; animation-delay: 3s;"></div>
</div>

<div class="container mt-5">
    <div class="dashboard-header text-center">
        <div class="welcome-badge">👋 Welcome Back</div>
        <h1><?= htmlspecialchars($_SESSION['user_name']); ?></h1>
        <p>Performance overview and daily statistics</p>
    </div>

    <div class="row g-4">
        <!-- Balance Today -->
        <div class="col-12 col-md-6 col-lg-3">
            <div class="stat-card card-balance-today">
                <div class="stat-card-body text-center">
                    <div class="icon-wrapper">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                    <div class="stat-label">Balance Today</div>
                    <h2 class="stat-value stat-card-balance-today">₱<?= number_format($total_balance_today, 2); ?></h2>
                </div>
            </div>
        </div>

        <!-- Balance (Last 7 Days) -->
        <div class="col-12 col-md-6 col-lg-3">
            <div class="stat-card card-balance-week">
                <div class="stat-card-body text-center">
                    <div class="icon-wrapper">
                        <i class="bi bi-calendar-week"></i>
                    </div>
                    <div class="stat-label">Balance (7 Days)</div>
                    <h2 class="stat-value stat-card-balance-week">₱<?= number_format($total_balance_last_7_days, 2); ?></h2>
                </div>
            </div>
        </div>

        <!-- Total Customers -->
        <div class="col-12 col-md-6 col-lg-3">
            <div class="stat-card card-customers">
                <div class="stat-card-body text-center">
                    <div class="icon-wrapper">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div class="stat-label">Total Customers</div>
                    <h2 class="stat-value stat-card-customers"><?= number_format($total_customers); ?></h2>
                </div>
            </div>
        </div>

        

        <!-- Active Customers -->
        <div class="col-12 col-md-6 col-lg-3">
            <div class="stat-card card-pawned">
                <div class="stat-card-body text-center">
                    <div class="icon-wrapper">
                       <i class="bi bi-person-badge-fill"></i>
                    </div>
                    <div class="stat-label">Total Employee</div>
                   <h2 class="stat-value stat-card-employees"><?= number_format($total_employee); ?></h2>
                </div>
            </div>
        </div>

        <!-- Interest Today -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="stat-card card-interest-today">
                <div class="stat-card-body text-center">
                    <div class="icon-wrapper">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <div class="stat-label">Interest Today</div>
                    <h2 class="stat-value stat-card-interest-today">₱<?= number_format($total_interest_today, 2); ?></h2>
                </div>
            </div>
        </div>

        <!-- Interest (Last 7 Days) -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="stat-card card-interest-week">
                <div class="stat-card-body text-center">
                    <div class="icon-wrapper">
                        <i class="bi bi-bar-chart-fill"></i>
                    </div>
                    <div class="stat-label">Interest (7 Days)</div>
                    <h2 class="stat-value stat-card-interest-week">₱<?= number_format($total_interest_last_7_days, 2); ?></h2>
                </div>
            </div>
        </div>

        <!-- Total Pawned Value -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="stat-card card-redeemed">
                <div class="stat-card-body text-center">
                    <div class="icon-wrapper">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div class="stat-label">Total Pawned</div>
                    <h2 class="stat-value stat-card-redeemed">₱<?= number_format($total_pawned, 2); ?></h2>
                </div>
            </div>
        </div>

        <!-- Total Redeemed Value -->
        <div class="col-12 col-md-6 col-lg-6">
            <div class="stat-card card-redeemed">
                <div class="stat-card-body text-center">
                    <div class="icon-wrapper">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <div class="stat-label">Total Item Fully Paid
                    </div>
                    <h2 class="stat-value stat-card-redeemed"><?= number_format($total_redeemed); ?></h2>
                </div>
            </div>
        </div>

        <!-- Overdue Items -->
        <div class="col-12 col-md-6 col-lg-6">
            <div class="stat-card card-overdue">
                <div class="stat-card-body text-center">
                    <div class="icon-wrapper">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <div class="stat-label">Overdue Items</div>
                    <h2 class="stat-value stat-card-overdue"><?= number_format($total_overdue); ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Grid -->
    <div class="charts-grid">
        <!-- Pawned vs Redeemed Trend -->
        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title">Daily Transaction Value</h3>
                <span class="chart-badge">Last 7 Days</span>
            </div>
            <canvas id="pawnedRedeemedChart"></canvas>
        </div>

        <!-- Item Status Distribution -->
        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title">Item Status</h3>
                <span class="chart-badge" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">Distribution</span>
            </div>
            <canvas id="itemStatusChart"></canvas>
        </div>

        <!-- Customer Status -->
        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title">Customer Status</h3>
                <span class="chart-badge" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">Overview</span>
            </div>
            <canvas id="customerStatusChart"></canvas>
        </div>

        <!-- Summary Card -->
        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title">Quick Summary</h3>
                <span class="chart-badge" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">Today</span>
            </div>
            <div style="padding: 20px 0;">
                <div style="margin-bottom: 24px; padding: 20px; background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%); border-radius: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <span style="font-weight: 600; color: #64748b;">Active Pawned Items</span>
                        <span style="font-size: 1.5rem; font-weight: 800; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"><?= number_format($total_pawned_customers); ?></span>
                    </div>
                    <div style="width: 100%; height: 8px; background: #e5e7eb; border-radius: 10px; overflow: hidden;">
                        <div style="width: <?= $total_customers > 0 ? ($total_pawned_customers / $total_customers * 100) : 0 ?>%; height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"></div>
                    </div>
                </div>

                <div style="margin-bottom: 24px; padding: 20px; background: linear-gradient(135deg, rgba(240, 147, 251, 0.1) 0%, rgba(245, 87, 108, 0.1) 100%); border-radius: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <span style="font-weight: 600; color: #64748b;">Inactive Customers</span>
                        <span style="font-size: 1.5rem; font-weight: 800; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"><?= number_format($total_inactive_customers); ?></span>
                    </div>
                    <div style="width: 100%; height: 8px; background: #e5e7eb; border-radius: 10px; overflow: hidden;">
                        <div style="width: <?= $total_customers > 0 ? ($total_inactive_customers / $total_customers * 100) : 0 ?>%; height: 100%; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);"></div>
                    </div>
                </div>

                <div style="padding: 20px; background: linear-gradient(135deg, rgba(255, 107, 107, 0.1) 0%, rgba(238, 90, 111, 0.1) 100%); border-radius: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <span style="font-weight: 600; color: #64748b;">Overdue Rate</span>
                        <span style="font-size: 1.5rem; font-weight: 800; background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"><?= $total_pawned_customers > 0 ? number_format(($total_overdue / $total_pawned_customers * 100), 1) : 0 ?>%</span>
                    </div>
                    <div style="width: 100%; height: 8px; background: #e5e7eb; border-radius: 10px; overflow: hidden;">
                        <div style="width: <?= $total_pawned_customers > 0 ? ($total_overdue / $total_pawned_customers * 100) : 0 ?>%; height: 100%; background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Pawned vs Redeemed Chart (Line Chart)
const ctxPawnedRedeemed = document.getElementById('pawnedRedeemedChart').getContext('2d');
const gradientPawned = ctxPawnedRedeemed.createLinearGradient(0, 0, 0, 300);
gradientPawned.addColorStop(0, 'rgba(102, 126, 234, 0.4)');
gradientPawned.addColorStop(1, 'rgba(118, 75, 162, 0.0)');

const gradientRedeemed = ctxPawnedRedeemed.createLinearGradient(0, 0, 0, 300);
gradientRedeemed.addColorStop(0, 'rgba(240, 147, 251, 0.4)');
gradientRedeemed.addColorStop(1, 'rgba(245, 87, 108, 0.0)');

new Chart(ctxPawnedRedeemed, {
    type: 'bar',
    data: {
        labels: <?= json_encode($dates); ?>,
        datasets: [
            {
                label: 'Pawned',
                data: <?= json_encode($pawned); ?>,
                backgroundColor: 'rgba(102, 126, 234, 0.8)',
                borderColor: '#667eea',
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false
            },
            {
                label: 'Redeemed',
                data: <?= json_encode($redeemed); ?>,
                backgroundColor: 'rgba(240, 147, 251, 0.8)',
                borderColor: '#f093fb',
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 20,
                    font: { size: 13, weight: '600' },
                    usePointStyle: true,
                    pointStyle: 'circle'
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                padding: 16,
                cornerRadius: 12,
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': ₱' + context.parsed.y.toLocaleString('en-PH', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    }
                }
            }
        },
        scales: {
            y: { 
                beginAtZero: true,
                grid: { color: 'rgba(0, 0, 0, 0.05)', drawBorder: false },
                ticks: {
                    callback: function(value) {
                        return '₱' + value.toLocaleString();
                    }
                }
            },
            x: {
                grid: { display: false, drawBorder: false }
            }
        }
    }
});

// Item Status Chart (Doughnut)
const ctxItemStatus = document.getElementById('itemStatusChart').getContext('2d');
new Chart(ctxItemStatus, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($item_statuses); ?>,
        datasets: [{
            data: <?= json_encode($item_status_counts); ?>,
            backgroundColor: [
                '#667eea',
                '#f093fb',
                '#4facfe',
                '#fa709a',
                '#30cfd0'
            ],
            borderWidth: 0,
            hoverOffset: 20
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 20,
                    font: { size: 13, weight: '600' },
                    usePointStyle: true,
                    pointStyle: 'circle'
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                padding: 16,
                cornerRadius: 12,
                callbacks: {
                    label: function(context) {
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((context.parsed / total) * 100).toFixed(1);
                        return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                    }
                }
            }
        }
    }
});

// Customer Status Chart (Pie)
const ctxCustomerStatus = document.getElementById('customerStatusChart').getContext('2d');
new Chart(ctxCustomerStatus, {
    type: 'pie',
    data: {
        labels: <?= json_encode($customer_statuses); ?>,
        datasets: [{
            data: <?= json_encode($customer_status_counts); ?>,
            backgroundColor: [
                '#4facfe',
                '#f093fb'
            ],
            borderWidth: 0,
            hoverOffset: 20
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 20,
                    font: { size: 13, weight: '600' },
                    usePointStyle: true,
                    pointStyle: 'circle'
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                padding: 16,
                cornerRadius: 12,
                callbacks: {
                    label: function(context) {
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((context.parsed / total) * 100).toFixed(1);
                        return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                    }
                }
            }
        }
    }
});

// Counter animation for stat values
document.addEventListener('DOMContentLoaded', function() {
    const statValues = document.querySelectorAll('.stat-value');
    
    statValues.forEach(stat => {
        const text = stat.textContent;
        const haspeso = text.includes('₱');
        const numericValue = parseFloat(text.replace(/[₱,]/g, ''));
        
        if (!isNaN(numericValue)) {
            let current = 0;
            const increment = numericValue / 50;
            const timer = setInterval(() => {
                current += increment;
                if (current >= numericValue) {
                    current = numericValue;
                    clearInterval(timer);
                }
                
                if (haspeso) {
                    stat.textContent = '₱' + current.toLocaleString('en-PH', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                } else {
                    stat.textContent = Math.floor(current).toLocaleString();
                }
            }, 20);
        }
    });
});
</script>

<?php include('footer.php'); ?>

