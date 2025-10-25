<?php
include('d:\xampp\htdocs\pawnshop\config.php');

// Reset auto-increment counter to start from 1 if table is empty
$checkEmptyTable = "SELECT COUNT(*) as count FROM custumer_info";
$result = $conn->query($checkEmptyTable);
$row = $result->fetch_assoc();
if ($row['count'] == 0) {
    $resetAutoIncrement = "ALTER TABLE custumer_info AUTO_INCREMENT = 1";
    $conn->query($resetAutoIncrement);
}

// Process new customer form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_customer'])) {
    $last_name = $_POST['last_name'];
    $first_name = $_POST['first_name'];
    $middle_initial = $_POST['middle_initial'];
    $gender = $_POST['gender'];
    $birthday = $_POST['birthday'];
    $email = $_POST['email'];
    $phone_no = $_POST['phone_no'];
    $address = $_POST['address'];

    // Validate email to ensure it's a Gmail address
    if (!preg_match('/^[a-zA-Z0-9._%+-]+@gmail\\.com$/', $email)) {
        $error_message = "Only Gmail addresses are allowed.";
    } elseif (!preg_match('/^(09\\d{9}|\\+639\\d{9})$/', $phone_no)) {
        $error_message = "Please enter a valid Philippine mobile number (e.g., 09171234567 or +639171234567).";
    } else {
        // Image upload handling
        $valid_id_image = null;
        if (isset($_FILES['valid_id_image']) && $_FILES['valid_id_image']['error'] === UPLOAD_ERR_OK) {
            $image_tmp_name = $_FILES['valid_id_image']['tmp_name'];
            $valid_id_image = file_get_contents($image_tmp_name);
        }

        if (empty($last_name) || empty($first_name) || empty($email) || empty($phone_no) || empty($address) || empty($gender) || empty($birthday)) {
            $error_message = "All fields are required.";
        } else {
            $sql = "INSERT INTO custumer_info (last_name, first_name, middle_initial, gender, birthday, email, phone_no, address, valid_id_image) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssss", $last_name, $first_name, $middle_initial, $gender, $birthday, $email, $phone_no, $address, $valid_id_image);

            if ($stmt->execute()) {
                $success_message = "Customer added successfully!";
            } else {
                $error_message = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Handle delete functionality
if (isset($_POST['confirm_delete'])) {
    $customerid = $_POST['delete_id'];

    $deleteTransactionsSql = "DELETE FROM transactions WHERE item_id IN (SELECT item_id FROM items WHERE customer_id = ?)";
    $stmt = $conn->prepare($deleteTransactionsSql);
    $stmt->bind_param("i", $customerid);
    $stmt->execute();
    $stmt->close();

    $deleteItemsSql = "DELETE FROM items WHERE customer_id = ?";
    $stmt = $conn->prepare($deleteItemsSql);
    $stmt->bind_param("i", $customerid);
    $stmt->execute();
    $stmt->close();

    $deleteCustomerSql = "DELETE FROM custumer_info WHERE customer_id = ?";
    $stmt = $conn->prepare($deleteCustomerSql);
    $stmt->bind_param("i", $customerid);
    
    if ($stmt->execute()) {
        $checkCustomersExist = "SELECT COUNT(*) as count FROM custumer_info";
        $result = $conn->query($checkCustomersExist);
        $row = $result->fetch_assoc();
        
        if ($row['count'] == 0) {
            $resetAutoIncrement = "ALTER TABLE custumer_info AUTO_INCREMENT = 1";
            $conn->query($resetAutoIncrement);
        }
        
        header('location: Customer_page.php');
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

include('header.php');
?>

<title>Customer Management - ArMaTech Pawnshop</title>

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
.elevation-4 { box-shadow: 0 8px 10px 1px rgba(0,0,0,0.14), 0 3px 14px 2px rgba(0,0,0,0.12), 0 5px 5px -3px rgba(0,0,0,0.2); }

/* Page Header - Material Style */
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

/* Material Alert */
.alert {
    border: none;
    border-radius: 4px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    animation: slideDown 0.3s cubic-bezier(0.4, 0.0, 0.2, 1);
    padding: 16px;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-16px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.alert-success {
    background: #4caf50;
    color: white;
}

.alert-danger {
    background: #f44336;
    color: white;
}

/* Material Cards */
.card {
    border: none;
    border-radius: 4px;
    background: white;
    transition: box-shadow 0.3s cubic-bezier(0.4, 0.0, 0.2, 1);
}

.card:hover {
    box-shadow: 0 8px 16px 0 rgba(0,0,0,0.14), 0 3px 18px 0 rgba(0,0,0,0.12), 0 5px 5px -3px rgba(0,0,0,0.2);
}

.search-card {
    background: white;
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

/* Material Form Elements */
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

/* Input Group Material Style */
.input-group-text {
    background: transparent;
    border: 1px solid rgba(0, 0, 0, 0.12);
    border-right: none;
    color: rgba(0, 0, 0, 0.54);
}

.input-group .form-control {
    border-left: none;
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

/* Material Badge */
.badge {
    padding: 4px 12px;
    font-weight: 500;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
    border-radius: 12px;
}

.badge.bg-success {
    background: #4caf50 !important;
}

.badge.bg-secondary {
    background: #9e9e9e !important;
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

.modal-header.bg-warning {
    background: #ff9800 !important;
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

/* Form Section Headers */
.form-section-header {
    color: rgba(0, 0, 0, 0.87);
    font-weight: 500;
    font-size: 1rem;
    margin: 24px 0 16px 0;
    padding-bottom: 8px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.12);
    display: flex;
    align-items: center;
    gap: 12px;
    letter-spacing: 0.5px;
}

.form-section-header i {
    color: #ffc107;
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
    font-weight: 400;
}

/* Material Ripple Effect (simulated with transition) */
.btn, .table tbody tr {
    position: relative;
    overflow: hidden;
}

/* Loading Spinner */
.spinner-border {
    width: 3rem;
    height: 3rem;
}

/* Input Text Transform */
input[type=text] {
    text-transform: capitalize;
}

/* Material Fade In Animation */
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

/* Search Labels */
.search-label {
    color: rgba(0, 0, 0, 0.6);
    font-weight: 500;
    font-size: 0.875rem;
    margin-bottom: 8px;
    display: block;
}

/* Icon Colors */
.text-primary { color: #2196f3 !important; }
.text-success { color: #4caf50 !important; }
.text-warning { color: #ff9800 !important; }
.text-danger { color: #f44336 !important; }

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
    <div class="container">
        <h2>
            <i class="bi bi-people-fill"></i>
            Customer Management
        </h2>
    </div>
</div>

<section class="container fade-in">
    <?php if (isset($success_message)) : ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success_message) ?>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background: #f44336; color: white;">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle-fill"></i>Confirm Deletion
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="bi bi-trash3" style="font-size: 3rem; color: #f44336;"></i>
                    <p class="mt-3 mb-0" style="font-size: 1rem; color: rgba(0,0,0,0.87);">Are you sure you want to delete this customer?</p>
                    <small style="color: rgba(0,0,0,0.54);">This action cannot be undone.</small>
                </div>
                <div class="modal-footer justify-content-center">
                    <form method="POST">
                        <input type="hidden" name="delete_id" id="delete_id">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" name="confirm_delete" class="btn btn-danger">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Customer Modal -->
    <div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title">
                        <i class="bi bi-person-plus-fill"></i>Add New Customer
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <?php if (isset($error_message)) : ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error_message) ?>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form id="addCustomerForm" action="" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <div class="row g-3">
                            <div class="col-12">
                                <h6 class="form-section-header">
                                    <i class="bi bi-person-badge-fill"></i>Personal Information
                                </h6>
                            </div>

                            <div class="col-md-4">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" id="last_name" class="form-control" name="last_name" required>
                            </div>

                            <div class="col-md-4">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" id="first_name" class="form-control" name="first_name" required>
                            </div>

                            <div class="col-md-4">
                                <label for="middle_initial" class="form-label">Middle Initial</label>
                                <input type="text" id="middle_initial" class="form-control" name="middle_initial" maxlength="2">
                            </div>

                            <div class="col-md-6">
                                <label for="gender" class="form-label">Gender</label>
                                <select id="gender" name="gender" class="form-select" required>
                                    <option value="">Select Gender</option>
                                    <option>Male</option>
                                    <option>Female</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="birthday" class="form-label">Birthday</label>
                                <input type="date" id="birthday" name="birthday" class="form-control" required>
                            </div>

                            <div class="col-12">
                                <h6 class="form-section-header">
                                    <i class="bi bi-telephone-fill"></i>Contact Information
                                </h6>
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">Email (Gmail only)</label>
                                <input type="email" id="email" name="email" class="form-control"
                                       pattern="[a-zA-Z0-9._%+-]+@gmail\.com"
                                       title="Please enter a valid Gmail address"
                                       required>
                            </div>

                            <div class="col-md-6">
                                <label for="phone_no" class="form-label">Phone Number</label>
                                <input type="tel" id="phone_no" name="phone_no" class="form-control"
                                       pattern="^(09\d{9}|\+639\d{9})$"
                                       title="Enter a valid Philippine mobile number"
                                       maxlength="13" required>
                            </div>

                            <div class="col-12">
                                <h6 class="form-section-header">
                                    <i class="bi bi-geo-alt-fill"></i>Address
                                </h6>
                            </div>

                            <div class="col-md-12">
                                <label for="address" class="form-label">Complete Address</label>
                                <input type="text" id="address" class="form-control" name="address" 
                                       placeholder="e.g., 123 Main St, Barangay X, City, Province" required>
                            </div>

                            <div class="col-12">
                                <h6 class="form-section-header">
                                    <i class="bi bi-card-image"></i>Upload Document
                                </h6>
                            </div>

                            <div class="col-md-12">
                                <label for="valid_id_image" class="form-label">Valid ID Image</label>
                                <input type="file" id="valid_id_image" name="valid_id_image" class="form-control">
                                <small style="color: rgba(0,0,0,0.54);">Accepted formats: JPG, PNG, PDF (Max: 2MB)</small>
                            </div>
                        </div>

                        <div class="modal-footer mt-4">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-success" name="add_customer">
                                Add Customer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Customer Modal -->
    <div class="modal fade" id="updateCustomerModal" tabindex="-1" aria-labelledby="updateCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square"></i>Edit Customer
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="updateCustomerContent">
                    <div class="text-center py-5" style="color: rgba(0,0,0,0.54);">
                        <div class="spinner-border text-warning" role="status"></div>
                        <p class="mt-3 mb-0">Loading customer data...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter Section -->
    <div class="card search-card elevation-2">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="search-label">
                        <i class="bi bi-search me-1"></i>Search Customer
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control"
                               placeholder="Search by name..."
                               value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="search-label">
                        <i class="bi bi-funnel me-1"></i>Status Filter
                    </label>
                    <select name="status_filter" class="form-select">
                        <option value="">All Status</option>
                        <option value="Active" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] === 'Active') ? 'selected' : ''; ?>>Active</option>
                        <option value="Inactive" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] === 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel-fill me-1"></i>Filter
                    </button>
                </div>

                <div class="col-md-2">
                    <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                        <i class="bi bi-plus-circle me-1"></i>Add New
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Customer Table -->
    <div class="card elevation-2">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr class="text-center">
                            <th>ID</th>
                            <th>Last Name</th>
                            <th>First Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $search = $_GET['search'] ?? '';
                        $status_filter = $_GET['status_filter'] ?? '';
                        $sql = "SELECT * FROM custumer_info WHERE 1";

                        if (!empty($search)) {
                            $sql .= " AND (first_name LIKE ? OR last_name LIKE ?)";
                        }
                        if (!empty($status_filter)) {
                            $sql .= " AND status = ?";
                        }

                        $stmt = $conn->prepare($sql);
                        if (!empty($search) && !empty($status_filter)) {
                            $search_term = "%$search%";
                            $stmt->bind_param("sss", $search_term, $search_term, $status_filter);
                        } elseif (!empty($search)) {
                            $search_term = "%$search%";
                            $stmt->bind_param("ss", $search_term, $search_term);
                        } elseif (!empty($status_filter)) {
                            $stmt->bind_param("s", $status_filter);
                        }

                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) { ?>
                                <tr class="text-center">
                                    <td><strong><?= $row['customer_id']; ?></strong></td>
                                    <td><?= htmlspecialchars($row['last_name']); ?></td>
                                    <td><?= htmlspecialchars($row['first_name']); ?></td>
                                    <td><i class="bi bi-envelope me-1 text-primary"></i><?= htmlspecialchars($row['email']); ?></td>
                                    <td><i class="bi bi-telephone me-1 text-success"></i><?= htmlspecialchars($row['phone_no']); ?></td>
                                    <td><?= htmlspecialchars($row['address']); ?></td>
                                    <td>
                                        <span class="badge rounded-pill <?= $row['status'] === 'Active' ? 'bg-success' : 'bg-secondary'; ?>">
                                            <?= htmlspecialchars($row['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-primary btn-sm me-1 view-btn" 
                                                data-id="<?= $row['customer_id']; ?>" 
                                                data-bs-toggle="modal" data-bs-target="#viewCustomerModal">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-warning btn-sm me-1"
                                                data-id="<?= $row['customer_id']; ?>"
                                                data-bs-toggle="modal" data-bs-target="#updateCustomerModal">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm"
                                                onclick="confirmDelete(<?= $row['customer_id']; ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                        <?php } 
                        } else { ?>
                            <tr>
                                <td colspan="8" class="text-center">
                                    <div class="empty-state">
                                        <i class="bi bi-inbox"></i>
                                        <p>No customers found</p>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- View Customer Modal -->
<div class="modal fade" id="viewCustomerModal" tabindex="-1" aria-labelledby="viewCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title">
                    <i class="bi bi-person-vcard"></i>Customer Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="customerDetails">
                <div class="text-center py-5" style="color: rgba(0,0,0,0.54);">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-3 mb-0">Loading customer details...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // View Customer Details via AJAX
    $(document).ready(function() {
        $(".view-btn").click(function() {
            var customerId = $(this).data("id");

            $.ajax({
                url: "view_customer.php",
                type: "GET",
                data: { id: customerId },
                success: function(response) {
                    $("#customerDetails").html(response);
                }
            });
        });

        // Load Update Customer Form into Modal
        $("#updateCustomerModal").on("show.bs.modal", function(e) {
            var customerId = $(e.relatedTarget).data("id");

            $.ajax({
                url: "update_customer.php?id=" + customerId,
                type: "GET",
                success: function(response) {
                    $("#updateCustomerContent").html(response);
                }
            });
        });

        // Phone number validation
        document.getElementById('phone_no').addEventListener('input', function() {
            const phoneField = this;
            const phonePattern = /^(09\d{9}|\+639\d{9})$/;
            if (phonePattern.test(phoneField.value)) {
                phoneField.setCustomValidity('');
            } else {
                phoneField.setCustomValidity('Please enter a valid Philippine mobile number.');
            }
        });

        // Show add customer modal if there was an error
        <?php if (isset($error_message) && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_customer'])): ?>
            var addCustomerModal = new bootstrap.Modal(document.getElementById('addCustomerModal'));
            addCustomerModal.show();
        <?php endif; ?>
    });

    // Confirm Delete
    function confirmDelete(id) {
        document.getElementById('delete_id').value = id;
        var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    }
</script>

<?php include('footer.php'); ?>