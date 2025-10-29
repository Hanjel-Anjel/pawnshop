<?php
include('d:\xampp\htdocs\pawnshop\config.php');

// Reset auto-increment counter if table empty
$checkEmptyTable = "SELECT COUNT(*) as count FROM custumer_info";
$result = $conn->query($checkEmptyTable);
$row = $result->fetch_assoc();
if ($row['count'] == 0) {
    $conn->query("ALTER TABLE custumer_info AUTO_INCREMENT = 1");
}

// Process new customer form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_customer'])) {
    $last_name = $_POST['last_name'];
    $first_name = $_POST['first_name'];
    $middle_initial = $_POST['middle_initial'];
    $gender = $_POST['gender'];
    $birthday = $_POST['birthday'];
    $email = $_POST['email'];
    $phone_no = $_POST['phone_no'];

    // Address fields
    $region = $_POST['region'];
    $province = $_POST['province'];
    $city = $_POST['city'];
    $barangay = $_POST['barangay'];
    $street = $_POST['street'];
    $house_number = $_POST['house_number'];

    // Validate email (must be Gmail)
    if (!preg_match('/^[a-zA-Z0-9._%+-]+@gmail\\.com$/', $email)) {
        $error_message = "Only Gmail addresses are allowed.";
    } elseif (!preg_match('/^(09\\d{9}|\\+639\\d{9})$/', $phone_no)) {
        $error_message = "Please enter a valid Philippine mobile number (e.g., 09171234567 or +639171234567).";
    } else {
        // Image upload
        $valid_id_image = null;
        if (isset($_FILES['valid_id_image']) && $_FILES['valid_id_image']['error'] === UPLOAD_ERR_OK) {
            $image_tmp_name = $_FILES['valid_id_image']['tmp_name'];
            $valid_id_image = file_get_contents($image_tmp_name);
        }

        // Check required fields
        if (empty($last_name) || empty($first_name) || empty($email) || empty($phone_no) || empty($gender) || empty($birthday) ||
            empty($region) || empty($province) || empty($city) || empty($barangay) || empty($street) || empty($house_number)) {
            $error_message = "All fields are required.";
        } else {
            // Insert customer info (updated)
            $sql = "INSERT INTO custumer_info 
                    (last_name, first_name, middle_initial, gender, birthday, email, phone_no, region, province, city, barangay, street, house_number, valid_id_image)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssssssssb",
                $last_name, $first_name, $middle_initial, $gender, $birthday, $email, $phone_no,
                $region, $province, $city, $barangay, $street, $house_number, $valid_id_image
            );

            if ($stmt->execute()) {
                $success_message = "Customer added successfully!";
            } else {
                $error_message = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Delete customer (with items and transactions)
if (isset($_POST['confirm_delete'])) {
    $customerid = $_POST['delete_id'];

    // Delete related transactions
    $deleteTransactionsSql = "DELETE FROM transactions WHERE item_id IN (SELECT item_id FROM items WHERE customer_id = ?)";
    $stmt = $conn->prepare($deleteTransactionsSql);
    $stmt->bind_param("i", $customerid);
    $stmt->execute();
    $stmt->close();

    // Delete related items
    $deleteItemsSql = "DELETE FROM items WHERE customer_id = ?";
    $stmt = $conn->prepare($deleteItemsSql);
    $stmt->bind_param("i", $customerid);
    $stmt->execute();
    $stmt->close();

    // Delete customer
    $deleteCustomerSql = "DELETE FROM custumer_info WHERE customer_id = ?";
    $stmt = $conn->prepare($deleteCustomerSql);
    $stmt->bind_param("i", $customerid);

    if ($stmt->execute()) {
        $checkCustomersExist = "SELECT COUNT(*) as count FROM custumer_info";
        $result = $conn->query($checkCustomersExist);
        $row = $result->fetch_assoc();

        if ($row['count'] == 0) {
            $conn->query("ALTER TABLE custumer_info AUTO_INCREMENT = 1");
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
:root {
    --primary-color: #1976d2;
    --primary-dark: #1565c0;
    --success-color: #2e7d32;
    --warning-color: #f57c00;
    --danger-color: #d32f2f;
    --info-color: #0288d1;
    --surface-color: #ffffff;
    --background-color: #f5f7fa;
}

* {
    font-family: 'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

body {
    background: linear-gradient(135deg, #f5f7fa 0%, #f5f7fa 100%);
    min-height: 100vh;
    padding-bottom: 3rem;
}

/* Page Header */
.page-header {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
    color: white;
    padding: 2rem 0;
    margin-bottom: 2rem;
    border-radius: 0 0 24px 24px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.page-header h2 {
    font-weight: 600;
    font-size: 2rem;
    margin: 0;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.page-header h2 i {
    font-size: 2.5rem;
}

/* Enhanced Alerts */
.alert {
    border: none;
    border-radius: 12px;
    padding: 1rem 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    margin-bottom: 1.5rem;
    animation: slideDown 0.4s ease-out;
    display: flex;
    align-items: center;
    gap: 0.75rem;
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

.alert-success {
    background: linear-gradient(135deg, #4caf50 0%, #388e3c 100%);
    color: white;
}

.alert-danger {
    background: linear-gradient(135deg, var(--danger-color) 0%, #c62828 100%);
    color: white;
}

.alert i {
    font-size: 1.5rem;
}

/* Stats Cards */
.stats-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    animation: fadeInUp 0.5s ease-out;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    margin-bottom: 1rem;
}

.stat-card.total .stat-icon {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
    color: white;
}

.stat-card.active .stat-icon {
    background: linear-gradient(135deg, var(--success-color) 0%, #388e3c 100%);
    color: white;
}

.stat-card.inactive .stat-icon {
    background: linear-gradient(135deg, #757575 0%, #616161 100%);
    color: white;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #263238;
    margin: 0.5rem 0;
}

.stat-label {
    color: #757575;
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Search Card */
.search-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    animation: fadeInUp 0.5s ease-out 0.1s both;
}

.search-label {
    color: #546e7a;
    font-weight: 600;
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.search-label i {
    color: var(--primary-color);
}

/* Form Controls */
.form-control, .form-select {
    border-radius: 12px;
    border: 2px solid #e0e0e0;
    padding: 0.75rem 1rem;
    transition: all 0.3s ease;
    font-size: 1rem;
}

.form-control:focus, .form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(25, 118, 210, 0.15);
}

.input-group-text {
    background: transparent;
    border: 2px solid #e0e0e0;
    border-right: none;
    border-radius: 12px 0 0 12px;
    color: #757575;
}

.input-group .form-control {
    border-left: none;
    border-radius: 0 12px 12px 0;
}

.input-group:focus-within .input-group-text {
    border-color: var(--primary-color);
    color: var(--primary-color);
}

/* Buttons */
.btn {
    border-radius: 12px;
    font-weight: 600;
    padding: 0.75rem 1.5rem;
    border: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    justify-content: center;
}

.btn:hover {
    transform: translateY(-2px);
}

.btn-primary {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
    box-shadow: 0 4px 12px rgba(25, 118, 210, 0.3);
}

.btn-primary:hover {
    box-shadow: 0 6px 16px rgba(25, 118, 210, 0.4);
}

.btn-success {
    background: linear-gradient(135deg, var(--success-color) 0%, #388e3c 100%);
    box-shadow: 0 4px 12px rgba(46, 125, 50, 0.3);
}

.btn-success:hover {
    box-shadow: 0 6px 16px rgba(46, 125, 50, 0.4);
}

.btn-warning {
    background: linear-gradient(135deg, var(--warning-color) 0%, #fb8c00 100%);
    box-shadow: 0 4px 12px rgba(245, 124, 0, 0.3);
    color: white;
}

.btn-warning:hover {
    box-shadow: 0 6px 16px rgba(245, 124, 0, 0.4);
    color: white;
}

.btn-danger {
    background: linear-gradient(135deg, var(--danger-color) 0%, #c62828 100%);
    box-shadow: 0 4px 12px rgba(211, 47, 47, 0.3);
}

.btn-danger:hover {
    box-shadow: 0 6px 16px rgba(211, 47, 47, 0.4);
}

.btn-secondary {
    background: #e0e0e0;
    color: #546e7a;
}

.btn-secondary:hover {
    background: #bdbdbd;
    color: #546e7a;
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}

/* Table Card */
.table-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    animation: fadeInUp 0.5s ease-out 0.2s both;
}

.table-responsive {
    border-radius: 16px;
}

.table {
    margin: 0;
}

.table thead {
    background: linear-gradient(135deg, #263238 0%, #37474f 100%);
}

.table thead th {
    color: Black;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
    padding: 1rem;
    border: none;
}

.table tbody tr {
    transition: all 0.2s ease;
    border-bottom: 1px solid #e0e0e0;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
    transform: scale(1.005);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.table tbody td {
    padding: 1rem;
    vertical-align: middle;
    color: #263238;
    font-size: 0.875rem;
}

.table tbody td strong {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
    color: white;
    padding: 0.5rem 0.75rem;
    border-radius: 8px;
    font-weight: 600;
    display: inline-block;
    min-width: 50px;
    text-align: center;
}

/* Badge */
.badge {
    padding: 0.5rem 1rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.75rem;
}

.badge.bg-success {
    background: linear-gradient(135deg, var(--success-color) 0%, #388e3c 100%) !important;
}

.badge.bg-secondary {
    background: linear-gradient(135deg, #757575 0%, #616161 100%) !important;
}

/* Empty State */
.empty-state {
    padding: 3rem 2rem;
    text-align: center;
    color: #90a4ae;
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.empty-state p {
    font-size: 1.125rem;
    margin: 0;
}

/* Modal Enhancements */
.modal-content {
    border-radius: 16px;
    border: none;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
}

.modal-header {
    border-radius: 16px 16px 0 0;
    border-bottom: none;
    padding: 1.5rem;
}

.modal-header.bg-primary {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%) !important;
}

.modal-header.bg-warning {
    background: linear-gradient(135deg, var(--warning-color) 0%, #fb8c00 100%) !important;
}

.modal-header.bg-danger {
    background: linear-gradient(135deg, var(--danger-color) 0%, #c62828 100%) !important;
}

.modal-title {
    font-weight: 600;
    font-size: 1.25rem;
    color: white;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.modal-title i {
    font-size: 1.5rem;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    border-top: none;
    padding: 1rem 1.5rem 1.5rem;
}

/* Form Section Headers */
.form-section-header {
    color: #263238;
    font-weight: 600;
    font-size: 1rem;
    margin: 1.5rem 0 1rem 0;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--primary-color);
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.form-section-header i {
    color: var(--primary-color);
    font-size: 1.25rem;
}

.form-label {
    color: #546e7a;
    font-weight: 600;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

/* Loading Spinner */
.spinner-border {
    width: 3rem;
    height: 3rem;
}

/* Action Buttons Group */
.action-buttons {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
}

/* Icon Colors */
.text-primary { color: var(--primary-color) !important; }
.text-success { color: var(--success-color) !important; }
.text-warning { color: var(--warning-color) !important; }
.text-danger { color: var(--danger-color) !important; }

/* Responsive */
@media (max-width: 768px) {
    .page-header h2 {
        font-size: 1.5rem;
    }
    
    .page-header h2 i {
        font-size: 1.75rem;
    }
    
    .stat-value {
        font-size: 1.5rem;
    }
    
    .table {
        font-size: 0.75rem;
    }
    
    .table thead th,
    .table tbody td {
        padding: 0.75rem 0.5rem;
    }
    
    .btn-sm {
        padding: 0.4rem 0.8rem;
        font-size: 0.75rem;
    }
    
    .action-buttons {
        flex-direction: column;
    }
}
</style>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h2>
            <i class="bi bi-people-fill"></i>
            Customer Management
        </h2>
    </div>
</div>

<section class="container">
    <!-- Alerts -->
    <?php if (isset($success_message)) : ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill"></i>
            <span><?= htmlspecialchars($success_message) ?></span>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="stats-container">
        <?php
        // Get total customers
        $total_sql = "SELECT COUNT(*) as total FROM custumer_info";
        $total_result = $conn->query($total_sql);
        $total_count = $total_result->fetch_assoc()['total'];

        // Get active customers
        $active_sql = "SELECT COUNT(*) as active FROM custumer_info WHERE status = 'Active'";
        $active_result = $conn->query($active_sql);
        $active_count = $active_result->fetch_assoc()['active'];

        // Get inactive customers
        $inactive_count = $total_count - $active_count;
        ?>
        
        <div class="stat-card total">
            <div class="stat-icon">
                <i class="bi bi-people"></i>
            </div>
            <div class="stat-value"><?= $total_count ?></div>
            <div class="stat-label">Total Customers</div>
        </div>
        
        <div class="stat-card active">
            <div class="stat-icon">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-value"><?= $active_count ?></div>
            <div class="stat-label">Active</div>
        </div>
        
        <div class="stat-card inactive">
            <div class="stat-icon">
                <i class="bi bi-x-circle"></i>
            </div>
            <div class="stat-value"><?= $inactive_count ?></div>
            <div class="stat-label">Inactive</div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle-fill"></i>Confirm Deletion
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="bi bi-trash3" style="font-size: 4rem; color: var(--danger-color); margin-bottom: 1rem;"></i>
                    <p style="font-size: 1.125rem; color: #263238; margin-bottom: 0.5rem;">Are you sure you want to delete this customer?</p>
                    <small style="color: #757575;">This action cannot be undone.</small>
                </div>
                <div class="modal-footer justify-content-center">
                    <form method="POST">
                        <input type="hidden" name="delete_id" id="delete_id">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i>Cancel
                        </button>
                        <button type="submit" name="confirm_delete" class="btn btn-danger">
                            <i class="bi bi-trash"></i>Delete
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
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <span><?= htmlspecialchars($error_message) ?></span>
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

                            <div class="col-md-6">
                                <label for="region" class="form-label">Region</label>
                                <select id="region" name="region" class="form-select" required></select>
                            </div>

                            <div class="col-md-6">
                                <label for="province" class="form-label">Province</label>
                                <select id="province" name="province" class="form-select" required></select>
                            </div>

                            <div class="col-md-6">
                                <label for="city" class="form-label">City/Municipality</label>
                                <select id="city" name="city" class="form-select" required></select>
                            </div>

                            <div class="col-md-6">
                                <label for="barangay" class="form-label">Barangay</label>
                                <select id="barangay" name="barangay" class="form-select" required></select>
                            </div>

                            <div class="col-md-8">
                                <label for="street" class="form-label">Street</label>
                                <input type="text" id="street" class="form-control" name="street" required>
                            </div>

                            <div class="col-md-4">
                                <label for="house_number" class="form-label">House Number</label>
                                <input type="text" id="house_number" class="form-control" name="house_number" required>
                            </div>

                            <div class="col-12">
                                <h6 class="form-section-header">
                                    <i class="bi bi-card-image"></i>Upload Document
                                </h6>
                            </div>

                            <div class="col-md-12">
                                <label for="valid_id_image" class="form-label">Valid ID Image</label>
                                <input type="file" id="valid_id_image" name="valid_id_image" class="form-control">
                                <small style="color: #757575;">Accepted formats: JPG, PNG, PDF (Max: 2MB)</small>
                            </div>
                        </div>

                        <div class="modal-footer mt-4">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle"></i>Cancel
                            </button>
                            <button type="submit" class="btn btn-success" name="add_customer">
                                <i class="bi bi-check-circle"></i>Add Customer
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
                    <div class="text-center py-5" style="color: #757575;">
                        <div class="spinner-border text-warning" role="status"></div>
                        <p class="mt-3 mb-0">Loading customer data...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter Section -->
    <div class="search-card">
        <form method="GET" class="row g-3">
            <div class="col-md-5">
                <label class="search-label">
                    <i class="bi bi-search"></i>
                    Search Customer
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
                    <i class="bi bi-funnel"></i>
                    Status Filter
                </label>
                <select name="status_filter" class="form-select">
                    <option value="">All Status</option>
                    <option value="Active" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] === 'Active') ? 'selected' : ''; ?>>Active</option>
                    <option value="Inactive" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] === 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="search-label" style="visibility: hidden;">Action</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel-fill"></i>Filter
                </button>
            </div>

            <div class="col-md-2">
                <label class="search-label" style="visibility: hidden;">Action</label>
                <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                    <i class="bi bi-plus-circle"></i>Add New
                </button>
            </div>
        </form>
    </div>

    <!-- Customer Table -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr class="text-center">
                        <th>ID</th>
                        <th>Last Name</th>
                        <th>First Name</th>
                        <th>Email</th>
                        <th>Phone</th>
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
                                <td>
                                    <span class="badge <?= $row['status'] === 'Active' ? 'bg-success' : 'bg-secondary'; ?>">
                                        <i class="bi <?= $row['status'] === 'Active' ? 'bi-check-circle' : 'bi-x-circle'; ?> me-1"></i>
                                        <?= htmlspecialchars($row['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-primary btn-sm view-btn" 
                                                data-id="<?= $row['customer_id']; ?>" 
                                                data-bs-toggle="modal" data-bs-target="#viewCustomerModal">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-warning btn-sm"
                                                data-id="<?= $row['customer_id']; ?>"
                                                data-bs-toggle="modal" data-bs-target="#updateCustomerModal">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm"
                                                onclick="confirmDelete(<?= $row['customer_id']; ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                    <?php } 
                    } else { ?>
                        <tr>
                            <td colspan="7">
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
                <div class="text-center py-5" style="color: #757575;">
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