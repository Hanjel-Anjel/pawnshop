<?php
include('d:\xampp\htdocs\pawnshop\config.php');
include('header_employee.php');

// Move the Add Customer form processing logic here
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_customer'])) {
    $last_name = $_POST['last_name'];
    $first_name = $_POST['first_name'];
    $middle_initial = $_POST['middle_initial'];
    $gender = $_POST['gender'];
    $birthday = $_POST['birthday'];
    $email = $_POST['email'];
    $phone_no = $_POST['phone_no'];
    $region = $_POST['region'];
    $province = $_POST['province'];
    $city = $_POST['city'];
    $barangay = $_POST['barangay'];
    $street = $_POST['street'];
    $house_number = $_POST['house_number'];


    // Validate email to ensure it's a Gmail address
    if (!preg_match('/^[a-zA-Z0-9._%+-]+@gmail\\.com$/', $email)) {
        $error_message = "Only Gmail addresses are allowed.";
    } elseif (!preg_match('/^(09\\d{9}|\\+639\\d{9})$/', $phone_no)) {
        // Validate Philippine mobile number
        $error_message = "Please enter a valid Philippine mobile number (e.g., 09171234567 or +639171234567).";
    } else {
        // Image upload handling
        $valid_id_image = null;
        if (isset($_FILES['valid_id_image']) && $_FILES['valid_id_image']['error'] === UPLOAD_ERR_OK) {
            $image_tmp_name = $_FILES['valid_id_image']['tmp_name'];
            $valid_id_image = file_get_contents($image_tmp_name);
        }

        // Basic input validation
        if (empty($last_name) || empty($first_name) || empty($email) || empty($phone_no) || empty($address) || empty($gender) || empty($birthday)) {
            $error_message = "All fields are required.";
        } else {
            // Prepare and execute the SQL query
           $sql = "INSERT INTO custumer_info (
                last_name, first_name, middle_initial, gender, birthday, email, phone_no,
                region, province, city, barangay, street, house_number, valid_id_image
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "ssssssssssssss",
    $last_name, $first_name, $middle_initial, $gender, $birthday, $email, $phone_no,
    $region, $province, $city, $barangay, $street, $house_number, $valid_id_image
);


            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssss", $last_name, $first_name, $middle_initial, $gender, $birthday, $email, $phone_no, $address, $valid_id_image);

            if ($stmt->execute()) {
                $success_message = "Customer added successfully!";
                // Don't redirect, let AJAX handle the feedback
            } else {
                $error_message = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management</title>
    
    <!-- Material Design Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <style>
    /* === MATERIAL DESIGN SYSTEM === */
    :root {
        --md-primary: #6200EE;
        --md-primary-variant: #3700B3;
        --md-secondary: #03DAC6;
        --md-secondary-variant: #018786;
        --md-background: #F5F5F5;
        --md-surface: #FFFFFF;
        --md-error: #B00020;
        --md-success: #4CAF50;
        --md-warning: #FF9800;
        --md-on-primary: #FFFFFF;
        --md-on-secondary: #000000;
        --md-on-background: #000000;
        --md-on-surface: #000000;
        --md-elevation-1: 0 2px 4px rgba(0,0,0,0.14);
        --md-elevation-2: 0 4px 8px rgba(0,0,0,0.16);
        --md-elevation-3: 0 6px 16px rgba(0,0,0,0.18);
        --md-elevation-4: 0 8px 24px rgba(0,0,0,0.20);
        --md-elevation-6: 0 12px 32px rgba(0,0,0,0.24);
    }

    body {
        font-family: 'Roboto', sans-serif;
        background: var(--md-background);
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    /* === MATERIAL CARD === */
    .md-card {
        background: var(--md-surface);
        border-radius: 8px;
        box-shadow: var(--md-elevation-2);
        transition: box-shadow 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
    }

    .md-card:hover {
        box-shadow: var(--md-elevation-4);
    }

    /* === PAGE HEADER === */
    .page-header {
        background: var(--md-surface);
        border-radius: 8px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: var(--md-elevation-2);
    }

    .page-header h2 {
        font-size: 32px;
        font-weight: 400;
        letter-spacing: 0;
        margin: 0;
        color: var(--md-on-surface);
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .page-header .material-icons {
        color: var(--md-primary);
        font-size: 36px;
    }

    /* === SEARCH & FILTER BAR === */
    .filter-bar {
        background: var(--md-surface);
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 24px;
        box-shadow: var(--md-elevation-2);
        display: flex;
        gap: 16px;
        align-items: center;
        flex-wrap: wrap;
    }

    .filter-bar .search-container {
        flex: 1;
        min-width: 300px;
        position: relative;
    }

    /* Material Input */
    .md-input-group {
        position: relative;
    }

    .md-input {
        width: 100%;
        height: 56px;
        padding: 16px 48px 16px 16px;
        border: 1px solid rgba(0, 0, 0, 0.23);
        border-radius: 4px;
        font-size: 16px;
        font-family: 'Roboto', sans-serif;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        background: var(--md-surface);
    }

    .md-input:focus {
        outline: none;
        border-color: var(--md-primary);
        border-width: 2px;
        padding: 16px 47px 16px 15px;
    }

    .md-input::placeholder {
        color: rgba(0, 0, 0, 0.38);
    }

    .search-icon {
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: rgba(0, 0, 0, 0.54);
        pointer-events: none;
    }

    /* Material Select */
    .md-select {
        height: 56px;
        padding: 16px;
        border: 1px solid rgba(0, 0, 0, 0.23);
        border-radius: 4px;
        font-size: 16px;
        font-family: 'Roboto', sans-serif;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        background: var(--md-surface);
        min-width: 200px;
    }

    .md-select:focus {
        outline: none;
        border-color: var(--md-primary);
        border-width: 2px;
        padding: 16px 15px;
    }

    /* Material Button */
    .md-button {
        height: 48px;
        padding: 0 24px;
        border: none;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 500;
        letter-spacing: 1.25px;
        text-transform: uppercase;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
    }

    .md-button::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }

    .md-button:hover::before {
        width: 300px;
        height: 300px;
    }

    .md-button-contained {
        background: var(--md-primary);
        color: var(--md-on-primary);
        box-shadow: var(--md-elevation-2);
    }

    .md-button-contained:hover {
        box-shadow: var(--md-elevation-4);
        background: #7722FF;
    }

    .md-button-success {
        background: var(--md-success);
        color: white;
        box-shadow: var(--md-elevation-2);
    }

    .md-button-success:hover {
        box-shadow: var(--md-elevation-4);
        background: #45a049;
    }

    .md-button-outlined {
        background: transparent;
        color: var(--md-primary);
        border: 1px solid rgba(98, 0, 238, 0.5);
    }

    .md-button-outlined:hover {
        background: rgba(98, 0, 238, 0.04);
    }

    .md-button-text {
        background: transparent;
        color: var(--md-primary);
        box-shadow: none;
    }

    .md-button-text:hover {
        background: rgba(98, 0, 238, 0.04);
    }

    /* === TABLE CONTAINER === */
    .table-container {
        background: var(--md-surface);
        border-radius: 8px;
        box-shadow: var(--md-elevation-2);
        overflow: hidden;
    }

    /* Material Table */
    .md-table {
        width: 100%;
        border-collapse: collapse;
    }

    .md-table thead {
        background: rgba(98, 0, 238, 0.04);
    }

    .md-table thead th {
        padding: 16px 24px;
        text-align: left;
        font-size: 12px;
        font-weight: 500;
        letter-spacing: 1px;
        text-transform: uppercase;
        color: var(--md-primary);
        border-bottom: 1px solid rgba(0, 0, 0, 0.12);
    }

    .md-table thead th:first-child {
        padding-left: 24px;
    }

    .md-table tbody tr {
        transition: background 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        border-bottom: 1px solid rgba(0, 0, 0, 0.08);
    }

    .md-table tbody tr:hover {
        background: rgba(98, 0, 238, 0.04);
    }

    .md-table tbody tr:last-child {
        border-bottom: none;
    }

    .md-table tbody td {
        padding: 16px 24px;
        font-size: 14px;
        letter-spacing: 0.25px;
        color: rgba(0, 0, 0, 0.87);
    }

    .md-table tbody td:first-child {
        padding-left: 24px;
    }

    /* Material Chip */
    .md-chip {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 6px 12px;
        border-radius: 16px;
        font-size: 13px;
        font-weight: 500;
        letter-spacing: 0.15px;
    }

    .md-chip.active {
        background: rgba(76, 175, 80, 0.12);
        color: #2E7D32;
    }

    .md-chip.inactive {
        background: rgba(158, 158, 158, 0.12);
        color: #616161;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 8px;
    }

    .md-icon-button {
        width: 40px;
        height: 40px;
        border: none;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .md-icon-button:hover {
        background: rgba(0, 0, 0, 0.04);
    }

    .md-icon-button.primary {
        color: var(--md-primary);
    }

    .md-icon-button.success {
        color: var(--md-success);
    }

    /* === MODAL STYLES === */
    .modal-content {
        border-radius: 8px;
        border: none;
        box-shadow: var(--md-elevation-6);
    }

    .modal-header {
        background: var(--md-surface);
        border-bottom: 1px solid rgba(0, 0, 0, 0.12);
        padding: 20px 24px;
    }

    .modal-title {
        font-size: 20px;
        font-weight: 500;
        letter-spacing: 0.15px;
        color: var(--md-on-surface);
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .modal-title .material-icons {
        color: var(--md-primary);
    }

    .modal-body {
        padding: 24px;
    }

    .modal-footer {
        border-top: 1px solid rgba(0, 0, 0, 0.12);
        padding: 16px 24px;
    }

    /* Form Groups */
    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-size: 12px;
        font-weight: 500;
        letter-spacing: 0.15px;
        color: rgba(0, 0, 0, 0.6);
        margin-bottom: 8px;
        text-transform: uppercase;
    }

    .form-control {
        width: 100%;
        height: 56px;
        padding: 16px;
        border: 1px solid rgba(0, 0, 0, 0.23);
        border-radius: 4px;
        font-size: 16px;
        font-family: 'Roboto', sans-serif;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .form-control:focus {
        outline: none;
        border-color: var(--md-primary);
        border-width: 2px;
        padding: 16px 15px;
    }

    /* Alert Styles */
    .alert {
        padding: 16px;
        border-radius: 4px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .alert-success {
        background: rgba(76, 175, 80, 0.12);
        color: #2E7D32;
        border-left: 4px solid #4CAF50;
    }

    .alert-danger {
        background: rgba(176, 0, 32, 0.12);
        color: #B00020;
        border-left: 4px solid #B00020;
    }

    /* Empty State */
    .empty-state {
        padding: 64px 24px;
        text-align: center;
    }

    .empty-state .icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 24px;
        background: rgba(0, 0, 0, 0.04);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .empty-state .icon .material-icons {
        font-size: 48px;
        color: rgba(0, 0, 0, 0.26);
    }

    /* Loading Spinner */
    .spinner-container {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 40px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .filter-bar {
            flex-direction: column;
        }

        .filter-bar .search-container {
            width: 100%;
        }

        .md-select {
            width: 100%;
        }

        .md-table {
            font-size: 12px;
        }

        .md-table thead th,
        .md-table tbody td {
            padding: 12px 16px;
        }

        .action-buttons {
            flex-direction: column;
        }
    }
    </style>
</head>
<body>

<section class="container mt-4">
    <!-- Page Header -->
    <div class="page-header">
        <h2>
            <span class="material-icons">groups</span>
            Customer Management
        </h2>
    </div>

    <!-- Search and Filter Bar -->
    <form method="GET" class="filter-bar">
        <div class="search-container">
            <div class="md-input-group">
                <input type="text" name="search" class="md-input" placeholder="Search by customer name..." 
                       value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                <span class="material-icons search-icon">search</span>
            </div>
        </div>
        
        <select name="status_filter" class="md-select">
            <option value="">All Status</option>
            <option value="Active" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] === 'Active') ? 'selected' : ''; ?>>Active</option>
            <option value="Inactive" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] === 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
        </select>

        <button type="submit" class="md-button md-button-contained">
            <span class="material-icons" style="font-size: 18px;">filter_list</span>
            Search
        </button>

       <!--  <button type="button" class="md-button md-button-success" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
            <span class="material-icons" style="font-size: 18px;">person_add</span>
            Add Customer
        </button> -->
    </form>

    <!-- Table Container -->
    <div class="table-container">
        <table class="md-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Last Name</th>
                    <th>First Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Retrieve search and filter parameters
                $search = $_GET['search'] ?? '';
                $status_filter = $_GET['status_filter'] ?? '';

                // Build the SQL query with search and filter conditions
                $sql = "SELECT * FROM custumer_info WHERE 1";

                if (!empty($search)) {
                    $sql .= " AND (first_name LIKE ? OR last_name LIKE ?)";
                }

                if (!empty($status_filter)) {
                    $sql .= " AND status = ?";
                }

                $stmt = $conn->prepare($sql);

                // Bind parameters based on conditions
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
                    $id = 1;
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td><strong>" . $id++ . "</strong></td>";
                        echo "<td>" . htmlspecialchars($row['last_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['first_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['phone_no']) . "</td>";
                        echo "<td>";
                        $status_class = strtolower($row['status']);
                        echo "<span class='md-chip $status_class'>" . htmlspecialchars($row['status']) . "</span>";
                        echo "</td>";
                        echo "<td>";
                        echo "<div class='action-buttons' style='justify-content: center;'>";
                        echo "<button type='button' class='md-icon-button primary view-customer' data-bs-toggle='modal' data-bs-target='#customerModal' data-id='" . $row['customer_id'] . "' title='View Details'>";
                        echo "<span class='material-icons'>visibility</span>";
                        echo "</button>";
                       /* echo "<button type='button' class='md-icon-button success edit-customer' data-bs-toggle='modal' data-bs-target='#editCustomerModal' data-id='" . $row['customer_id'] . "' title='Edit Customer'>"; 
                        echo "<span class='material-icons'>edit</span>";
                        echo "</button>"; */
                        echo "</div>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>";
                    echo "<div class='empty-state'>";
                    echo "<div class='icon'><span class='material-icons'>person_off</span></div>";
                    echo "<div style='font-size: 18px; color: rgba(0,0,0,0.6); margin-bottom: 8px;'>No customers found</div>";
                    echo "<div style='font-size: 14px; color: rgba(0,0,0,0.38);'>Try adjusting your search or filter criteria</div>";
                    echo "</div>";
                    echo "</td></tr>";
                }
                $stmt->close();
                ?>
            </tbody>
        </table>
    </div>
</section>

<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCustomerModalLabel">
                    <span class="material-icons">person_add</span>
                    Add New Customer
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if (isset($error_message)) : ?>
                    <div class="alert alert-danger">
                        <span class="material-icons">error_outline</span>
                        <?= htmlspecialchars($error_message) ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($success_message)) : ?>
                    <div class="alert alert-success">
                        <span class="material-icons">check_circle_outline</span>
                        <?= htmlspecialchars($success_message) ?>
                    </div>
                <?php endif; ?>

                <form id="addCustomerForm" action="" method="post" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="last_name">Last Name *</label>
                                <input type="text" id="last_name" class="form-control" name="last_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="first_name">First Name *</label>
                                <input type="text" id="first_name" class="form-control" name="first_name" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="middle_initial">Middle Initial</label>
                                <input type="text" id="middle_initial" class="form-control" name="middle_initial" maxlength="5">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="gender">Gender *</label>
                                <select id="gender" class="form-control" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="birthday">Birthday *</label>
                        <input type="date" id="birthday" class="form-control" name="birthday" required>
                    </div>


                    <div class="form-group">
                        <label for="phone_no">Phone Number *</label>
                        <input type="tel" id="phone_no" class="form-control" name="phone_no"
                            pattern="^(09\d{9}|\+639\d{9})$"
                            title="Enter a valid Philippine mobile number (e.g., 09171234567 or +639171234567)"
                            maxlength="13" required
                            oninput="this.value = this.value.replace(/[^0-9+]/g, '').slice(0, 13);">
                    </div>

                    <div class="form-group">
    <label>Address *</label>
    <div class="row">
        <div class="col-md-6 mb-2">
            <input type="text" class="form-control" name="region" placeholder="Region" required>
        </div>
        <div class="col-md-6 mb-2">
            <input type="text" class="form-control" name="province" placeholder="Province" required>
        </div>
        <div class="col-md-6 mb-2">
            <input type="text" class="form-control" name="city" placeholder="City" required>
        </div>
        <div class="col-md-6 mb-2">
            <input type="text" class="form-control" name="barangay" placeholder="Barangay" required>
        </div>
        <div class="col-md-6 mb-2">
            <input type="text" class="form-control" name="street" placeholder="Street" required>
        </div>
        <div class="col-md-6 mb-2">
            <input type="text" class="form-control" name="house_number" placeholder="House Number" required>
        </div>
    </div>
</div>


                    <div class="form-group">
                        <label for="valid_id_image">Valid ID Image</label>
                        <input type="file" id="valid_id_image" class="form-control" name="valid_id_image" accept="image/*">
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="md-button md-button-text" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="md-button md-button-success" name="add_customer">
                            <span class="material-icons" style="font-size: 18px;">add</span>
                            Add Customer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Customer View Modal -->
<div class="modal fade" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customerModalLabel">
                    <span class="material-icons">account_circle</span>
                    Customer Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="customerDetails">
                <div class="spinner-container">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="md-button md-button-text" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Customer Edit Modal -->
<div class="modal fade" id="editCustomerModal" tabindex="-1" aria-labelledby="editCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCustomerModalLabel">
                    <span class="material-icons">edit</span>
                    Update Customer
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="editCustomerForm">
                <div class="spinner-container">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add event listener to all view customer buttons
    const viewButtons = document.querySelectorAll('.view-customer');
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const customerId = this.getAttribute('data-id');
            loadCustomerDetails(customerId);
        });
    });

    // Add event listener to all edit customer buttons
    const editButtons = document.querySelectorAll('.edit-customer');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const customerId = this.getAttribute('data-id');
            loadEditForm(customerId);
        });
    });

    // Add Customer form submission
    const addCustomerForm = document.getElementById('addCustomerForm');
    if (addCustomerForm) {
        addCustomerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...`;
            
            fetch('add_customer_ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    const formContent = document.querySelector('#addCustomerModal .modal-body');
                    formContent.innerHTML = `
                        <div class="alert alert-success">
                            <span class="material-icons">check_circle</span>
                            Customer added successfully!
                        </div>
                        <div class="text-center mt-3">
                            <button type="button" class="md-button md-button-contained" onclick="window.location.reload()">
                                <span class="material-icons" style="font-size: 18px;">refresh</span>
                                Refresh Page
                            </button>
                        </div>
                    `;
                } else {
                    // Show error
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-danger';
                    alertDiv.innerHTML = `<span class="material-icons">error_outline</span>${data.message || 'An error occurred while adding the customer.'}`;
                    
                    // Insert at the top of the form
                    const modalBody = document.querySelector('#addCustomerModal .modal-body');
                    modalBody.insertBefore(alertDiv, modalBody.firstChild);
                    
                    // Reset button
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            })
            .catch(error => {
                // Show error
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-danger';
                alertDiv.innerHTML = '<span class="material-icons">error_outline</span>An error occurred while processing your request.';
                
                // Insert at the top of the form
                const modalBody = document.querySelector('#addCustomerModal .modal-body');
                modalBody.insertBefore(alertDiv, modalBody.firstChild);
                
                // Reset button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            });
        });
    }

    // Function to load customer details via AJAX
    function loadCustomerDetails(customerId) {
        // Show loading spinner
        document.getElementById('customerDetails').innerHTML = `
            <div class="spinner-container">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `;

        // Fetch customer data
        fetch('get_customer_details.php?id=' + customerId)
            .then(response => response.text())
            .then(data => {
                document.getElementById('customerDetails').innerHTML = data;
            })
            .catch(error => {
                document.getElementById('customerDetails').innerHTML = `
                    <div class="alert alert-danger">
                        <span class="material-icons">error_outline</span>
                        Error loading customer details: ${error.message}
                    </div>
                `;
            });
    }

    // Function to load edit form via AJAX
    function loadEditForm(customerId) {
        // Show loading spinner
        document.getElementById('editCustomerForm').innerHTML = `
            <div class="spinner-container">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `;

        // Fetch edit form
        fetch('get_edit_form.php?id=' + customerId)
            .then(response => response.text())
            .then(data => {
                document.getElementById('editCustomerForm').innerHTML = data;
                
                // Set up the form submission
                const editForm = document.getElementById('customerEditForm');
                if (editForm) {
                    editForm.addEventListener('submit', function(e) {
                        e.preventDefault();
                        updateCustomer(this, customerId);
                    });
                }
            })
            .catch(error => {
                document.getElementById('editCustomerForm').innerHTML = `
                    <div class="alert alert-danger">
                        <span class="material-icons">error_outline</span>
                        Error loading edit form: ${error.message}
                    </div>
                `;
            });
    }

    // Function to handle form submission for updating customer
    function updateCustomer(form, customerId) {
        const formData = new FormData(form);
        
        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...`;
        
        fetch('update_customer_ajax.php?id=' + customerId, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-success';
                alertDiv.innerHTML = '<span class="material-icons">check_circle</span>Customer updated successfully!';
                form.prepend(alertDiv);
                
                // Close modal and refresh page after delay
                setTimeout(() => {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editCustomerModal'));
                    modal.hide();
                    window.location.reload();
                }, 1500);
            } else {
                // Show error
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-danger';
                alertDiv.innerHTML = `<span class="material-icons">error_outline</span>${data.message || 'An error occurred while updating the customer.'}`;
                form.prepend(alertDiv);
                
                // Reset button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        })
        .catch(error => {
            // Show error
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger';
            alertDiv.innerHTML = '<span class="material-icons">error_outline</span>An error occurred while updating the customer.';
            form.prepend(alertDiv);
            
            // Reset button
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        });
    }
});
</script>

<?php include('footer.php'); ?>