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

    include('header_employee.php');
    ?>

    <title>Employee Dashboard</title>

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
            --purple-gradient: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
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
            font-size: 3rem;
            font-weight: 800;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0;
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
        .card-employees .icon-wrapper { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); }

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
        .stat-card-employees .stat-value { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

        .chart-card {
            background: white;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            animation: fadeIn 0.8s ease-out 0.7s forwards;
            opacity: 0;
            margin-top: 32px;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }

        .chart-title {
            font-size: 1.75rem;
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

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .dashboard-header h1 {
                font-size: 2rem;
            }
            
            .stat-value {
                font-size: 2rem;
            }
            
            .chart-card {
                padding: 24px;
            }
        }

        /* Subtle shimmer effect on cards */
        @keyframes shimmer {
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
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
            <h1><?= htmlspecialchars($_SESSION['user_name']); ?> Dashboard</h1>
            <p>Real-time overview of your business performance</p>
        </div>

        <div class="row g-4">
            <!-- Balance Today -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="stat-card card-balance-today">
                    <div class="stat-card-body text-center">
                        <div class="icon-wrapper">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                        <div class="stat-label">Balance Today</div>
                        <h2 class="stat-value stat-card-balance-today">₱<?= number_format($total_balance_today, 2); ?></h2>
                    </div>
                </div>
            </div>

            <!-- Balance (Last 7 Days) -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="stat-card card-balance-week">
                    <div class="stat-card-body text-center">
                        <div class="icon-wrapper">
                            <i class="bi bi-calendar-week"></i>
                        </div>
                        <div class="stat-label">Balance (Last 7 Days)</div>
                        <h2 class="stat-value stat-card-balance-week">₱<?= number_format($total_balance_last_7_days, 2); ?></h2>
                    </div>
                </div>
            </div>

            <!-- Total Customers -->
            <div class="col-12 col-md-6 col-lg-4">
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
                        <div class="stat-label">Interest (Last 7 Days)</div>
                        <h2 class="stat-value stat-card-interest-week">₱<?= number_format($total_interest_last_7_days, 2); ?></h2>
                    </div>
                </div>
            </div>

            <!-- Total Employees -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="stat-card card-employees">
                    <div class="stat-card-body text-center">
                        <div class="icon-wrapper">
                            <i class="bi bi-person-check-fill"></i>
                        </div>
                        <div class="stat-label">Active Customers</div>
                         <h2 class="stat-value stat-card-pawned"><?= number_format($total_active_customers); ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart Section -->
        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title">Balance Trend Analysis</h3>
                <span class="chart-badge">Last 7 Days</span>
            </div>
            <canvas id="balanceChart" height="80"></canvas>
        </div>
    </div>

    <script>
    const ctx = document.getElementById('balanceChart').getContext('2d');

    const gradientPawned = ctx.createLinearGradient(0, 0, 0, 400);
    gradientPawned.addColorStop(0, 'rgba(102, 126, 234, 0.4)');
    gradientPawned.addColorStop(1, 'rgba(102, 126, 234, 0.0)');

    const gradientRedeemed = ctx.createLinearGradient(0, 0, 0, 400);
    gradientRedeemed.addColorStop(0, 'rgba(255, 99, 132, 0.4)');
    gradientRedeemed.addColorStop(1, 'rgba(255, 99, 132, 0.0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($dates); ?>,
            datasets: [
                {
                    label: 'Pawned Value',
                    data: <?= json_encode($pawned); ?>,
                    borderColor: '#667eea',
                    backgroundColor: gradientPawned,
                    borderWidth: 4,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#667eea',
                },
                {
                    label: 'Redeemed Value',
                    data: <?= json_encode($redeemed); ?>,
                    borderColor: '#ff6384',
                    backgroundColor: gradientRedeemed,
                    borderWidth: 4,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#ff6384',
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: { font: { size: 14 } }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    displayColors: false,
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
                    ticks: {
                        callback: value => '₱' + value.toLocaleString()
                    }
                }
            }
        }
    });
    </script>


    <?php include('footer.php'); ?>