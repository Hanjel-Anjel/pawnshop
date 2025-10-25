<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_name']) && !isset($_SESSION['user_name'])) {
    header('location:login_form.php');
    exit();
}


?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ArMaTech Pa-wnshop Management System</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome for Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">


    <link rel="stylesheet" type="text/css" href="extensions/filter-control/bootstrap-table-filter-control.css">
    <script src="extensions/filter-control/bootstrap-table-filter-control.js"></script>

    <style>
        <?php
        include('newstyle.css');
        ?>
    </style>
    <!-- Custom CSS -->

</head>

<body>
    <!-- Header -->
    <header class="header">
        <button class="toggle-btn" id="toggle-btn"><i class="fas fa-bars"></i></button>
        <h1>ArMaTech Pa-wnshop Management System</h1>
        <span>Employee, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? $_SESSION['user_name']); ?></span>
    </header>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <ul>
            <li>
                <a href="home_employee.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'home_employee.php' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i>
                    <span class="link-text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="customer_employee.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'customer_employee.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i>
                    <span class="link-text">Customer</span>
                </a>
            </li>
            <li>
                <a href="inventory_employee.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'inventory_employee.php' ? 'active' : ''; ?>">
                    <i class="fas fa-boxes"></i>
                    <span class="link-text">Inventory</span>
                </a>
            </li>
            <li>
                <a href="transanction_employee.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'transanction_employee.php' ? 'active' : ''; ?>">
                    <i class="fas fa-file-invoice"></i>
                    <span class="link-text">Transactions</span>
                </a>
            </li>
            
        </ul>
        <div class="logout">
            <a href="#" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#logoutModal">
                <i class="fas fa-sign-out-alt"></i>
                <span class="link-text">Log Out</span>
            </a>
        </div>
    </div>


    <!-- Main Content -->
    <div class="main-content" id="main-content">



        <!-- JavaScript -->
        <script>
            const toggleBtn = document.getElementById('toggle-btn');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');

            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('shrink');
                mainContent.classList.toggle('expand');
            });

            
        </script>


        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>