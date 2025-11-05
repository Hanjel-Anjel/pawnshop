<?php

session_start();

include('config.php');

// Handle delete item request
if (isset($_GET['delete_item'])) {
    $item_id = $_GET['delete_item'];

    $sql = "DELETE FROM items WHERE item_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $item_id);

    if ($stmt->execute()) {
        header("Location: item_table.php");
        exit();
    } else {
        echo "Error deleting item: " . $conn->error;
    }

    $stmt->close();
}

// Add Item functionality
if (isset($_POST['add_item'])) {
    $customer_id = mysqli_real_escape_string($conn, $_POST['customer_id']);
    $brand = mysqli_real_escape_string($conn, $_POST['brand']);
    $model = mysqli_real_escape_string($conn, $_POST['model']);
    $condition = mysqli_real_escape_string($conn, $_POST['condition']);
    $specifications = mysqli_real_escape_string($conn, $_POST['specifications']);
    $item_value = mysqli_real_escape_string($conn, $_POST['item_value']);
    $loan_amount = mysqli_real_escape_string($conn, $_POST['loan_amount']);
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $handled_by = $_SESSION['user_id'];

    $category_query = "SELECT * FROM item_categories WHERE category_id = '$category_id'";
    $result_category = mysqli_query($conn, $category_query);

    if ($result_category && mysqli_num_rows($result_category) > 0) {
        $row_category = mysqli_fetch_assoc($result_category);

        $pawn_date = date('Y-m-d');
        $due_date = $_POST['due_date'];
        $expiry_date = $_POST['expiry_date'];

        $interest_rate = 0.03;
        $principal = $loan_amount;
        $months_diff = (strtotime($due_date) - strtotime($pawn_date)) / (30 * 24 * 60 * 60);
        $compound_interest = $principal * pow((1 + $interest_rate), $months_diff) - $principal;
        $total_balance = $principal + $compound_interest;

        $insert_item_query = "
            INSERT INTO items (customer_id, brand, model, specifications, category_id, item_value, loan_amount, item_status, pawn_date, due_date, expiry_date, interest_rate, total_balance) 
            VALUES ('$customer_id', '$brand', '$model', '$specifications', '$category_id', '$item_value', '$loan_amount', '$status', '$pawn_date', '$due_date', '$expiry_date', '$compound_interest', '$total_balance')";

        if (mysqli_query($conn, $insert_item_query)) {
            $transaction_date = date('Y-m-d');
            $insert_transaction_query = "
                INSERT INTO transactions (customer_id, item_id, transaction_date, amount, payment_method, handled_by, 
                loan_amount, interest_amount, total_amount) 
                VALUES ('$customer_id', LAST_INSERT_ID(), '$transaction_date', '$total_balance', 'Cash', '$handled_by',
                '$loan_amount', '$compound_interest', '$total_balance')";

            if (mysqli_query($conn, $insert_transaction_query)) {
                updateCustomerStatus($conn);
                header('Location: ' . ($_SESSION['user_type'] == 'admin' ? 'item_table.php' : 'inventory_employee.php'));
                exit();
            } else {
                $error_message = "Error recording transaction: " . mysqli_error($conn);
            }
        } else {
            $error_message = "Error adding item: " . mysqli_error($conn);
        }
    } else {
        $error_message = "Invalid category selected.";
    }
}

function updateCustomerStatus($conn) {
    $sql = "
        SELECT c.customer_id, 
               COUNT(i.item_id) AS active_items
        FROM custumer_info c
        LEFT JOIN items i 
        ON c.customer_id = i.customer_id 
           AND i.item_status NOT IN ('Redeemed', 'Forfeited')
        GROUP BY c.customer_id";
        
    $result = $conn->query($sql);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $status = $row['active_items'] > 0 ? 'Active' : 'Inactive';

            $update_sql = "UPDATE custumer_info SET status = ? WHERE customer_id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("si", $status, $row['customer_id']);
            $stmt->execute();
        }
    }
}

include('header.php');
?>

<title>Inventory Management - ArMaTech Pawnshop</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

<style>
* {
    font-family: 'Roboto', sans-serif;
}

body {
    background: #fafafa;
    min-height: 100vh;
}

/* Material Design Elevation Shadows */
.elevation-1 { box-shadow: 0 2px 2px 0 rgba(0,0,0,0.14), 0 3px 1px -2px rgba(0,0,0,0.12), 0 1px 5px 0 rgba(0,0,0,0.2); }
.elevation-2 { box-shadow: 0 4px 5px 0 rgba(0,0,0,0.14), 0 1px 10px 0 rgba(0,0,0,0.12), 0 2px 4px -1px rgba(0,0,0,0.2); }
.elevation-3 { box-shadow: 0 6px 10px 0 rgba(0,0,0,0.14), 0 1px 18px 0 rgba(0,0,0,0.12), 0 3px 5px -1px rgba(0,0,0,0.2); }

/* Page Header */
.page-header {
    background: #ffc107;
    padding: 24px 0;
    margin-bottom: 24px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.page-header h2 {
    color: rgba(0, 0, 0, 0.87);
    font-weight: 500;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 16px;
    font-size: 1.75rem;
    letter-spacing: 0.5px;
}

.page-header h2 i {
    font-size: 2rem;
}

/* Cards */
.card {
    border: none;
    border-radius: 4px;
    background: white;
    transition: box-shadow 0.3s cubic-bezier(0.4, 0.0, 0.2, 1);
}

.filter-card {
    margin-bottom: 24px;
}

.card-body {
    padding: 24px;
}

/* Material Buttons */
.btn {
    border-radius: 4px;
    font-weight: 500;
    padding: 10px 24px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s cubic-bezier(0.4, 0.0, 0.2, 1);
    box-shadow: 0 2px 5px rgba(0,0,0,0.15);
    border: none;
    font-size: 0.875rem;
}

.btn:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    transform: translateY(-1px);
}

.btn:active {
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    transform: translateY(0);
}

.btn-primary {
    background: #2196f3;
    color: white;
}

.btn-primary:hover {
    background: #1976d2;
    color: white;
}

.btn-success {
    background: #4caf50;
    color: white;
}

.btn-success:hover {
    background: #388e3c;
    color: white;
}

.btn-warning {
    background: #ff9800;
    color: white;
}

.btn-warning:hover {
    background: #f57c00;
    color: white;
}

.btn-danger {
    background: #f44336;
    color: white;
}

.btn-danger:hover {
    background: #d32f2f;
    color: white;
}

.btn-secondary {
    background: #757575;
    color: white;
}

.btn-secondary:hover {
    background: #616161;
    color: white;
}

.btn-sm {
    padding: 6px 16px;
    font-size: 0.8125rem;
}

/* Form Elements */
.form-label {
    color: rgba(0, 0, 0, 0.6);
    font-weight: 500;
    margin-bottom: 8px;
    font-size: 0.875rem;
}

.form-control,
.form-select {
    border-radius: 4px;
    border: 1px solid rgba(0, 0, 0, 0.12);
    padding: 12px 16px;
    transition: all 0.3s cubic-bezier(0.4, 0.0, 0.2, 1);
    font-size: 1rem;
    color: rgba(0, 0, 0, 0.87);
}

.form-control:focus,
.form-select:focus {
    border-color: #ffc107;
    box-shadow: 0 0 0 2px rgba(255, 193, 7, 0.2);
    outline: none;
}

.form-control::placeholder {
    color: rgba(0, 0, 0, 0.38);
}

/* Material Table */
.table-responsive {
    border-radius: 4px;
    overflow: hidden;
}

.table {
    margin: 0;
}

.table thead {
    background: #ffc107;
}

.table thead th {
    color: rgba(0, 0, 0, 0.87);
    font-weight: 500;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 1px;
    padding: 16px;
    border: none;
}

.table tbody tr {
    transition: background 0.2s cubic-bezier(0.4, 0.0, 0.2, 1);
    border-bottom: 1px solid rgba(0, 0, 0, 0.08);
}

.table tbody tr:hover {
    background: rgba(255, 193, 7, 0.08);
}

.table tbody td {
    padding: 16px;
    vertical-align: middle;
    color: rgba(0, 0, 0, 0.87);
    font-size: 0.875rem;
}

/* Status Badges */
.status-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

.status-pawned {
    background: #fff9c4;
    color: #f57f17;
}

.status-redeemed {
    background: #c8e6c9;
    color: #2e7d32;
}

.status-forfeited {
    background: #ffcdd2;
    color: #c62828;
}

.status-on-sale {
    background: #bbdefb;
    color: #1565c0;
}

.status-sold {
    background: #e1bee7;
    color: #6a1b9a;
}

/* Material Modal */
.modal-content {
    border-radius: 4px;
    border: none;
    box-shadow: 0 11px 15px -7px rgba(0,0,0,0.2), 0 24px 38px 3px rgba(0,0,0,0.14), 0 9px 46px 8px rgba(0,0,0,0.12);
}

.modal-header {
    border-radius: 4px 4px 0 0;
    padding: 24px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.08);
}

.modal-header.bg-primary {
    background: #2196f3 !important;
    color: white;
}

.modal-header.bg-success {
    background: #4caf50 !important;
    color: white;
}

.modal-header .modal-title {
    font-size: 1.25rem;
    font-weight: 500;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.modal-body {
    padding: 24px;
}

.modal-footer {
    border-top: 1px solid rgba(0, 0, 0, 0.08);
    padding: 16px 24px;
}

/* Empty State */
.empty-state {
    padding: 48px 20px;
    text-align: center;
    color: rgba(0, 0, 0, 0.38);
}

.empty-state i {
    font-size: 4rem;
    color: rgba(0, 0, 0, 0.12);
    margin-bottom: 16px;
}

.empty-state p {
    font-size: 1rem;
    margin: 0;
}

/* Loading Spinner */
.spinner-border {
    width: 3rem;
    height: 3rem;
}

/* Animations */
.fade-in {
    animation: fadeIn 0.3s cubic-bezier(0.4, 0.0, 0.2, 1);
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Input Text Transform */
input[type=text], textarea {
    text-transform: capitalize;
}

/* Alert */
.alert {
    border: none;
    border-radius: 4px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    padding: 16px;
}

.alert-success {
    background: #4caf50;
    color: white;
}

.alert-danger {
    background: #f44336;
    color: white;
}

/* Filter Labels */
.filter-label {
    color: rgba(0, 0, 0, 0.6);
    font-weight: 500;
    font-size: 0.875rem;
    margin-bottom: 8px;
    display: block;
}

/* Responsive */
@media (max-width: 768px) {
    .page-header h2 {
        font-size: 1.5rem;
    }
    
    .card-body {
        padding: 16px;
    }
    
    .table {
        font-size: 0.8125rem;
    }
    
    .table thead th,
    .table tbody td {
        padding: 12px 8px;
    }
    
    .btn-sm {
        padding: 4px 12px;
        font-size: 0.75rem;
    }
}
</style>

<!-- Page Header -->
<div class="page-header elevation-2">
    <div class="container-fluid">
        <h2>
            <i class="bi bi-box-seam-fill"></i>
            Item Inventory
        </h2>
    </div>
</div>

<div class="container-fluid fade-in">
    <?php if (isset($error_message)) : ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error_message) ?>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filter Card -->
    <div class="card filter-card elevation-2">
        <div class="card-body">
            <form method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="search" class="filter-label">
                            <i class="bi bi-search me-1"></i>Search
                        </label>
                        <input type="text" name="search" id="search" class="form-control"
                            placeholder="Brand, model, or customer name"
                            value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                    </div>

                    <div class="col-md-2">
                        <label for="start_date" class="filter-label">
                            <i class="bi bi-calendar me-1"></i>Start Date
                        </label>
                        <input type="date" name="start_date" id="start_date" class="form-control"
                            value="<?= isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : '' ?>">
                    </div>

                    <div class="col-md-2">
                        <label for="end_date" class="filter-label">
                            <i class="bi bi-calendar-check me-1"></i>End Date
                        </label>
                        <input type="date" name="end_date" id="end_date" class="form-control"
                            value="<?= isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : '' ?>">
                    </div>

                    <div class="col-md-2">
                        <label for="item_status" class="filter-label">
                            <i class="bi bi-funnel me-1"></i>Item Status
                        </label>
                        <select name="item_status" id="item_status" class="form-select">
                            <option value="">All Status</option>
                            <option value="Pawned" <?= isset($_GET['item_status']) && $_GET['item_status'] == 'Pawned' ? 'selected' : '' ?>>Pawned</option>
                            <option value="Forfeited" <?= isset($_GET['item_status']) && $_GET['item_status'] == 'Forfeited' ? 'selected' : '' ?>>Forfeited</option>
                            <option value="On Sale" <?= isset($_GET['item_status']) && $_GET['item_status'] == 'On Sale' ? 'selected' : '' ?>>On Sale</option>
                            <option value="Sold" <?= isset($_GET['item_status']) && $_GET['item_status'] == 'Sold' ? 'selected' : '' ?>>Sold</option>
                        </select>
                    </div>

                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search "></i>
                        </button>
                    </div>

                    <div class="col-md-2">
                        <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#addItemModal">
                            <i class="bi bi-plus-circle me-1"></i>Add Item
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card elevation-2">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr class="text-center">
                            <th>ID</th>
                            <th>Customer Name</th>
                            <th>Brand</th>
                            <th>Model</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Total Balance</th>
                            <th>Pawn Date</th>
                            <th>Due Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT 
                                    i.item_id,
                                    c.last_name AS customer_last_name,
                                    c.first_name AS customer_first_name,
                                    i.brand,
                                    i.model,
                                    ic.category_name,
                                    i.item_status,
                                    i.loan_amount,
                                    i.pawn_date,
                                    i.due_date,
                                    i.specifications,
                                    i.condition,
                                    i.item_value,
                                    i.interest_rate,
                                    i.total_balance,
                                    i.expiry_date
                                FROM 
                                    items i
                                INNER JOIN 
                                    custumer_info c ON i.customer_id = c.customer_id
                                INNER JOIN 
                                    item_categories ic ON i.category_id = ic.category_id
                                WHERE 1=1";

                        $search = isset($_GET['search']) ? $_GET['search'] : '';
                        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
                        $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
                        $item_status_filter = isset($_GET['item_status']) ? $_GET['item_status'] : '';

                        if (!empty($search)) {
                            $sql .= " AND (
                                i.brand LIKE '%" . $conn->real_escape_string($search) . "%' OR
                                i.model LIKE '%" . $conn->real_escape_string($search) . "%' OR
                                c.last_name LIKE '%" . $conn->real_escape_string($search) . "%' OR
                                c.first_name LIKE '%" . $conn->real_escape_string($search) . "%'
                            )";
                        }

                        if (!empty($start_date)) {
                            $sql .= " AND i.pawn_date >= '" . $conn->real_escape_string($start_date) . "'";
                        }

                        if (!empty($end_date)) {
                            $sql .= " AND i.pawn_date <= '" . $conn->real_escape_string($end_date) . "'";
                        }

                        if (!empty($item_status_filter)) {
                            $sql .= " AND i.item_status = '" . $conn->real_escape_string($item_status_filter) . "'";
                        }

                        $result = $conn->query($sql);

                        if ($result && $result->num_rows > 0) {
                            $id = 1;
                            while ($row = $result->fetch_assoc()) {
                                // Determine status badge class
                                $status_class = '';
                                switch($row['item_status']) {
                                    case 'Pawned':
                                        $status_class = 'status-pawned';
                                        break;
                                    case 'Redeemed':
                                        $status_class = 'status-redeemed';
                                        break;
                                    case 'Forfeited':
                                        $status_class = 'status-forfeited';
                                        break;
                                    case 'On Sale':
                                        $status_class = 'status-on-sale';
                                        break;
                                    case 'Sold':
                                        $status_class = 'status-sold';
                                        break;
                                }

                                echo "<tr class='text-center' data-item-id='" . $row['item_id'] . "'>";
                                echo "<td><strong>" . $id++ . "</strong></td>";
                                echo "<td>" . htmlspecialchars($row['customer_last_name']) . ", " . htmlspecialchars($row['customer_first_name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['brand']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['model']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['category_name']) . "</td>";
                                echo "<td><span class='status-badge " . $status_class . "'>" . htmlspecialchars($row['item_status']) . "</span></td>";
                                echo "<td><strong style='color: #ffc107;'>₱" . number_format($row['total_balance'], 2) . "</strong></td>";
                                echo "<td>" . htmlspecialchars($row['pawn_date']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['due_date']) . "</td>";
                                echo "<td>";
                                
                                echo "<button type='button' class='btn btn-primary btn-sm m-1 view-item-btn' 
                                      data-item-id='" . $row['item_id'] . "'
                                      data-customer-name='" . htmlspecialchars($row['customer_last_name'] . ", " . $row['customer_first_name']) . "'
                                      data-brand='" . htmlspecialchars($row['brand']) . "'
                                      data-model='" . htmlspecialchars($row['model']) . "'
                                      data-specifications='" . htmlspecialchars($row['specifications']) . "'
                                      data-condition='" . htmlspecialchars($row['condition']) . "'
                                      data-category='" . htmlspecialchars($row['category_name']) . "'
                                      data-value='" . htmlspecialchars($row['item_value']) . "'
                                      data-status='" . htmlspecialchars($row['item_status']) . "'
                                      data-loan-amount='" . htmlspecialchars($row['loan_amount']) . "'
                                      data-interest-rate='" . htmlspecialchars($row['interest_rate']) . "'
                                      data-total-balance='" . htmlspecialchars($row['total_balance']) . "'
                                      data-pawn-date='" . htmlspecialchars($row['pawn_date']) . "'
                                      data-due-date='" . htmlspecialchars($row['due_date']) . "'
                                      data-expiry-date='" . htmlspecialchars($row['expiry_date']) . "'
                                      data-bs-toggle='modal' data-bs-target='#viewItemModal'>
                                      <i class='bi bi-eye'></i> view
                                      </button>";
                                      
                               /* echo "<button type='button' class='btn btn-warning btn-sm m-1 edit-item-btn' 
                                      data-item-id='" . $row['item_id'] . "'
                                      data-item-brand='" . htmlspecialchars($row['brand']) . "'
                                      data-item-model='" . htmlspecialchars($row['model']) . "'
                                      data-item-specifications='" . htmlspecialchars($row['specifications']) . "'
                                      data-item-condition='" . htmlspecialchars($row['condition']) . "'
                                      data-item-value='" . htmlspecialchars($row['item_value']) . "'
                                      data-item-status='" . htmlspecialchars($row['item_status']) . "'
                                      data-bs-toggle='modal' data-bs-target='#editItemModal'> 
                                      <i class='bi bi-pencil'></i>
                                      </button>"; */
                                      
                                            if($row['item_status'] === 'Pawned') {
                                                echo "<a href='payment.php?item_id=" . $row['item_id'] . "' 
                                                    class='btn btn-success btn-sm m-1'>
                                                    <i class='bi bi-cash-stack'></i> Pay
                                                    </a>";
                                            }

                                echo "</td>";

                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='10'><div class='empty-state'><i class='bi bi-inbox'></i><p>No items found</p></div></td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Item Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle-fill"></i>Add New Item
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="post" id="addItemForm">
                    <div class="mb-3">
                        <label for="customer_search" class="form-label">Search Customer</label>
                        <input type="text" id="customer_search" class="form-control" placeholder="Type customer name to search...">
                    </div>
                    
                    <div class="mb-3">
                        <label for="customer_id" class="form-label">Customer</label>
                        <select name="customer_id" id="customer_id" class="form-select" required>
                            <option value="">Select a customer</option>
                            <?php
                            $select_customers_query = "SELECT customer_id, first_name, last_name FROM custumer_info";
                            $customers_result = mysqli_query($conn, $select_customers_query);

                            while ($customer = mysqli_fetch_assoc($customers_result)) {
                                echo "<option value='" . $customer['customer_id'] . "'>" . htmlspecialchars($customer['first_name']) . " " . htmlspecialchars($customer['last_name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="brand" class="form-label">Brand</label>
                            <input type="text" name="brand" id="brand" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="model" class="form-label">Model</label>
                            <input type="text" name="model" id="model" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="specifications" class="form-label">Specifications</label>
                        <textarea name="specifications" id="specifications" class="form-control" rows="3" required></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="condition" class="form-label">Condition</label>
                            <select name="condition" id="condition" class="form-select" required>
                                <option value="Used">Used</option>
                                <option value="Damaged">Damaged</option>
                                <option value="Brand New">Brand New</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label">Category</label>
                            <select name="category_id" id="category_id" class="form-select" required>
                                <option value="">Select a category</option>
                                <?php
                                $select_categories_query = "SELECT * FROM item_categories";
                                $categories_result = mysqli_query($conn, $select_categories_query);

                                while ($category = mysqli_fetch_assoc($categories_result)) {
                                    echo "<option value='" . $category['category_id'] . "'>" . htmlspecialchars($category['category_name']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="item_value" class="form-label">Item Value</label>
                            <input type="number" name="item_value" id="item_value" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="loan_amount" class="form-label">Loan Amount</label>
                            <input type="number" name="loan_amount" id="loan_amount" class="form-control" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="pawn_date" class="form-label">Pawn Date</label>
                            <input type="date" id="pawn_date" name="pawn_date" class="form-control" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="plan" class="form-label">Plan</label>
                            <select id="plan" name="plan" class="form-select" required>
                                <option value="" disabled selected>Select Plan</option>
                                <option value="1-month">1 Month</option>
                                <option value="3-months">3 Months</option>
                                <option value="6-months">6 Months</option>
                                <option value="1-year">1 Year</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="status" class="form-label">Item Status</label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="Pawned">Pawned</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="due_date" class="form-label">Due Date</label>
                            <input type="date" id="due_date" name="due_date" class="form-control" readonly>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="expiry_date" class="form-label">Expiry Date</label>
                            <input type="date" id="expiry_date" name="expiry_date" class="form-control" readonly>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_item" class="btn btn-success">Add Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Item Modal -->
<div class="modal fade" id="editItemModal" tabindex="-1" aria-labelledby="editItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square"></i>Edit Item
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editItemForm" method="post" class="row g-3">
                    <input type="hidden" id="edit_item_id" name="item_id">

                    <div class="col-md-6">
                        <label for="item_brand" class="form-label">Brand</label>
                        <input type="text" id="item_brand" name="item_brand" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label for="item_model" class="form-label">Model</label>
                        <input type="text" id="item_model" name="item_model" class="form-control" required>
                    </div>

                    <div class="col-md-12">
                        <label for="item_specifications" class="form-label">Specifications</label>
                        <textarea name="item_specifications" id="item_specifications" class="form-control" rows="3" required></textarea>
                    </div>

                    <div class="col-md-6">
                        <label for="item_condition" class="form-label">Condition</label>
                        <select name="item_condition" id="item_condition" class="form-select" required>
                            <option value="Used">Used</option>
                            <option value="Damaged">Damaged</option>
                            <option value="Brand New">Brand New</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="item_value" class="form-label">Item Value</label>
                        <input type="number" id="item_value" name="item_value" class="form-control" step="0.01" required>
                    </div>

                    <div class="col-md-6">
                        <label for="item_status" class="form-label">Item Status</label>
                        <select id="item_status" name="item_status" class="form-select" required>
                            <option value="Pawned">Pawned</option>
                            <option value="Redeemed">Redeemed</option>
                            <option value="Forfeited">Forfeited</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="updateItemBtn">Update Item</button>
            </div>
        </div>
    </div>
</div>

<!-- View Item Modal -->
<div class="modal fade" id="viewItemModal" tabindex="-1" aria-labelledby="viewItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title">
                    <i class="bi bi-info-circle-fill"></i>Item Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <p style="color: rgba(0,0,0,0.6); margin-bottom: 4px; font-size: 0.875rem;">Customer Name</p>
                        <p style="color: rgba(0,0,0,0.87); font-weight: 500;"><span id="modal_customer_name"></span></p>
                    </div>
                    <div class="col-md-6">
                        <p style="color: rgba(0,0,0,0.6); margin-bottom: 4px; font-size: 0.875rem;">Category</p>
                        <p style="color: rgba(0,0,0,0.87); font-weight: 500;"><span id="modal_category"></span></p>
                    </div>
                    <div class="col-md-6">
                        <p style="color: rgba(0,0,0,0.6); margin-bottom: 4px; font-size: 0.875rem;">Brand</p>
                        <p style="color: rgba(0,0,0,0.87); font-weight: 500;"><span id="modal_brand"></span></p>
                    </div>
                    <div class="col-md-6">
                        <p style="color: rgba(0,0,0,0.6); margin-bottom: 4px; font-size: 0.875rem;">Model</p>
                        <p style="color: rgba(0,0,0,0.87); font-weight: 500;"><span id="modal_model"></span></p>
                    </div>
                    <div class="col-md-12">
                        <p style="color: rgba(0,0,0,0.6); margin-bottom: 4px; font-size: 0.875rem;">Specifications</p>
                        <p style="color: rgba(0,0,0,0.87); font-weight: 500;"><span id="modal_specifications"></span></p>
                    </div>
                    <div class="col-md-6">
                        <p style="color: rgba(0,0,0,0.6); margin-bottom: 4px; font-size: 0.875rem;">Condition</p>
                        <p style="color: rgba(0,0,0,0.87); font-weight: 500;"><span id="modal_condition"></span></p>
                    </div>
                    <div class="col-md-6">
                        <p style="color: rgba(0,0,0,0.6); margin-bottom: 4px; font-size: 0.875rem;">Value</p>
                        <p style="color: rgba(0,0,0,0.87); font-weight: 500;">₱<span id="modal_value"></span></p>
                    </div>
                    <div class="col-md-6">
                        <p style="color: rgba(0,0,0,0.6); margin-bottom: 4px; font-size: 0.875rem;">Status</p>
                        <p style="color: rgba(0,0,0,0.87); font-weight: 500;"><span id="modal_status"></span></p>
                    </div>
                    <div class="col-md-6">
                        <p style="color: rgba(0,0,0,0.6); margin-bottom: 4px; font-size: 0.875rem;">Loan Amount</p>
                        <p style="color: #ffc107; font-weight: 500;">₱<span id="modal_loan_amount"></span></p>
                    </div>
                    <div class="col-md-6">
                        <p style="color: rgba(0,0,0,0.6); margin-bottom: 4px; font-size: 0.875rem;">Interest Amount</p>
                        <p style="color: rgba(0,0,0,0.87); font-weight: 500;">₱<span id="modal_interest_rate"></span></p>
                    </div>
                    <div class="col-md-6">
                        <p style="color: rgba(0,0,0,0.6); margin-bottom: 4px; font-size: 0.875rem;">Total Balance</p>
                        <p style="color: #ffc107; font-weight: 500;">₱<span id="modal_total_balance"></span></p>
                    </div>
                    <div class="col-md-4">
                        <p style="color: rgba(0,0,0,0.6); margin-bottom: 4px; font-size: 0.875rem;">Pawn Date</p>
                        <p style="color: rgba(0,0,0,0.87); font-weight: 500;"><span id="modal_pawn_date"></span></p>
                    </div>
                    <div class="col-md-4">
                        <p style="color: rgba(0,0,0,0.6); margin-bottom: 4px; font-size: 0.875rem;">Due Date</p>
                        <p style="color: rgba(0,0,0,0.87); font-weight: 500;"><span id="modal_due_date"></span></p>
                    </div>
                    <div class="col-md-4">
                        <p style="color: rgba(0,0,0,0.6); margin-bottom: 4px; font-size: 0.875rem;">Expiry Date</p>
                        <p style="color: rgba(0,0,0,0.87); font-weight: 500;"><span id="modal_expiry_date"></span></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const today = new Date();
        const formattedDate = today.toISOString().split('T')[0];
        document.getElementById('pawn_date').value = formattedDate;
        
        const pawnDateInput = document.getElementById("pawn_date");
        const dueDateInput = document.getElementById("due_date");
        const expiryDateInput = document.getElementById("expiry_date");

        const maturityMonths = {
            "1-month": 1,
            "3-months": 3,
            "6-months": 6,
            "1-year": 12
        };
        const expiryDaysAfterDue = 7;

        function updateDates() {
            const pawnDateValue = pawnDateInput.value;
            const selectedPlan = document.getElementById("plan").value;

            if (pawnDateValue && selectedPlan) {
                const pawnDate = new Date(pawnDateValue);

                const dueDate = new Date(pawnDate);
                dueDate.setMonth(dueDate.getMonth() + maturityMonths[selectedPlan]);

                const expiryDate = new Date(dueDate);
                expiryDate.setDate(expiryDate.getDate() + expiryDaysAfterDue);

                dueDateInput.value = dueDate.toISOString().split("T")[0];
                expiryDateInput.value = expiryDate.toISOString().split("T")[0];
            } else {
                dueDateInput.value = "";
                expiryDateInput.value = "";
            }
        }

        pawnDateInput.addEventListener("change", updateDates);
        document.getElementById("plan").addEventListener("change", updateDates);

        const searchInput = document.getElementById("customer_search");
        const customerSelect = document.getElementById("customer_id");

        searchInput.addEventListener("keyup", function () {
            const searchValue = searchInput.value.toLowerCase();

            for (let option of customerSelect.options) {
                if (option.text.toLowerCase().includes(searchValue) || option.value === "") {
                    option.style.display = "";
                } else {
                    option.style.display = "none";
                }
            }
        });

        const viewButtons = document.querySelectorAll('.view-item-btn');
        viewButtons.forEach(button => {
            button.addEventListener('click', function() {
                const customerName = this.getAttribute('data-customer-name');
                const brand = this.getAttribute('data-brand');
                const model = this.getAttribute('data-model');
                const specifications = this.getAttribute('data-specifications');
                const condition = this.getAttribute('data-condition');
                const category = this.getAttribute('data-category');
                const value = this.getAttribute('data-value');
                const status = this.getAttribute('data-status');
                const loanAmount = this.getAttribute('data-loan-amount');
                const interestRate = this.getAttribute('data-interest-rate');
                const totalBalance = this.getAttribute('data-total-balance');
                const pawnDate = this.getAttribute('data-pawn-date');
                const dueDate = this.getAttribute('data-due-date');
                const expiryDate = this.getAttribute('data-expiry-date');
                
                document.getElementById('modal_customer_name').textContent = customerName;
                document.getElementById('modal_brand').textContent = brand;
                document.getElementById('modal_model').textContent = model;
                document.getElementById('modal_specifications').textContent = specifications;
                document.getElementById('modal_condition').textContent = condition;
                document.getElementById('modal_category').textContent = category;
                document.getElementById('modal_value').textContent = parseFloat(value).toFixed(2);
                document.getElementById('modal_status').textContent = status;
                document.getElementById('modal_loan_amount').textContent = parseFloat(loanAmount).toFixed(2);
                document.getElementById('modal_interest_rate').textContent = parseFloat(interestRate).toFixed(2);
                document.getElementById('modal_total_balance').textContent = parseFloat(totalBalance).toFixed(2);
                document.getElementById('modal_pawn_date').textContent = pawnDate;
                document.getElementById('modal_due_date').textContent = dueDate;
                document.getElementById('modal_expiry_date').textContent = expiryDate;
            });
        });

        const editButtons = document.querySelectorAll('.edit-item-btn');
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const itemId = this.getAttribute('data-item-id');
                const brand = this.getAttribute('data-item-brand');
                const model = this.getAttribute('data-item-model');
                const specifications = this.getAttribute('data-item-specifications');
                const condition = this.getAttribute('data-item-condition');
                const value = this.getAttribute('data-item-value');
                const status = this.getAttribute('data-item-status');
                
                document.getElementById('edit_item_id').value = itemId;
                document.getElementById('item_brand').value = brand;
                document.getElementById('item_model').value = model;
                document.getElementById('item_specifications').value = specifications;
                document.getElementById('item_condition').value = condition;
                document.getElementById('item_value').value = value;
                document.getElementById('item_status').value = status;
            });
        });

        document.getElementById('updateItemBtn').addEventListener('click', function() {
            const itemId = document.getElementById('edit_item_id').value;
            const brand = document.getElementById('item_brand').value;
            const model = document.getElementById('item_model').value;
            const specifications = document.getElementById('item_specifications').value;
            const condition = document.getElementById('item_condition').value;
            const value = document.getElementById('item_value').value;
            const status = document.getElementById('item_status').value;
            
            if (!brand || !model || !specifications || !value) {
                alert('Please fill in all required fields');
                return;
            }
            
            const formData = new FormData();
            formData.append('update_item', 'true');
            formData.append('item_id', itemId);
            formData.append('brand', brand);
            formData.append('model', model);
            formData.append('specifications', specifications);
            formData.append('condition', condition);
            formData.append('item_value', value);
            formData.append('status', status);
            
            fetch('update_item.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editItemModal'));
                    modal.hide();
                    
                    const row = document.querySelector(`tr[data-item-id="${itemId}"]`);
                    row.querySelector('td:nth-child(3)').textContent = brand;
                    row.querySelector('td:nth-child(4)').textContent = model;
                    
                    alert('Item updated successfully');
                    
                    if (data.statusChanged) {
                        location.reload();
                    }
                } else {
                    alert('Error updating item: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the item');
            });
        });
    });
</script>

<?php
if (isset($_POST['update_item'])) {
    header('Content-Type: application/json');
    
    $item_id = $_POST['item_id'];
    $brand = mysqli_real_escape_string($conn, $_POST['brand']);
    $model = mysqli_real_escape_string($conn, $_POST['model']);
    $specifications = mysqli_real_escape_string($conn, $_POST['specifications']);
    $condition = mysqli_real_escape_string($conn, $_POST['condition']);
    $item_value = mysqli_real_escape_string($conn, $_POST['item_value']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $status_query = "SELECT item_status FROM items WHERE item_id = ?";
    $stmt = $conn->prepare($status_query);
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $original_status = $row['item_status'];
    $status_changed = ($original_status != $status);
    
    $update_sql = "UPDATE items SET 
                   brand = ?, 
                   model = ?, 
                   specifications = ?, 
                   `condition` = ?, 
                   item_value = ?, 
                   item_status = ? 
                   WHERE item_id = ?";
    
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssssdsi", $brand, $model, $specifications, $condition, $item_value, $status, $item_id);
    
    if ($stmt->execute()) {
        if ($status_changed && ($status == 'Redeemed' || $status == 'Forfeited')) {
            $item_query = "SELECT customer_id, loan_amount, total_balance FROM items WHERE item_id = ?";
            $stmt = $conn->prepare($item_query);
            $stmt->bind_param("i", $item_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $item_data = $result->fetch_assoc();
            
            $customer_id = $item_data['customer_id'];
            $amount = ($status == 'Redeemed') ? $item_data['total_balance'] : 0;
            $transaction_type = ($status == 'Redeemed') ? 'Redemption' : 'Forfeiture';
            $handled_by = $_SESSION['user_id'];
            
            $transaction_sql = "INSERT INTO transactions 
                               (customer_id, item_id, transaction_date, amount, transaction_type, payment_method, handled_by) 
                               VALUES (?, ?, CURRENT_DATE(), ?, ?, 'Cash', ?)";
            
            $stmt = $conn->prepare($transaction_sql);
            $stmt->bind_param("iidsi", $customer_id, $item_id, $amount, $transaction_type, $handled_by);
            $stmt->execute();
            
            updateCustomerStatus($conn);
        }
        
        echo json_encode(['success' => true, 'statusChanged' => $status_changed]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
    
    exit();
}

include('footer.php');
?>