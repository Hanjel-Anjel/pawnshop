<?php
include('d:\xampp\htdocs\pawnshop\config.php');
ob_start();
include('header.php');

// Handle Add Employee
if (isset($_POST['add_employee'])) {
    $first = $_POST['first_name'];
    $last = $_POST['last_name'];
    $mi = $_POST['middle_initial'];
    $position = $_POST['position'];
    $salary = $_POST['salary'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $contact = $_POST['contact_number'];
    $address = str_replace(array("\r", "\n"), ' ', $_POST['address']);
    $date_hired = $_POST['date_hired'];

    $stmt = $conn->prepare("CALL AddNewEmployee(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssdsdsss", $first, $mi, $last, $position, $salary, $email, $password, $contact, $address, $date_hired);
    $stmt->execute();

    header("Location: employee_page.php");
    exit();
}


// Handle Activate/Deactivate
if (isset($_GET['toggle_id']) && isset($_GET['action'])) {
    $user_id = $_GET['toggle_id'];
    $action = $_GET['action'];

    $stmt = $conn->prepare("CALL ToggleEmployeeStatus(?, ?)");
    $stmt->bind_param("is", $user_id, $action);
    $stmt->execute();

    header("Location: employee_page.php");
    exit();
}


// Handle Update Employee
if (isset($_POST['update_employee'])) {
    $emp_id = $_POST['emp_id'];
    $user_id = $_POST['user_id'];
    $first = $_POST['first_name'];
    $last = $_POST['last_name'];
    $mi = $_POST['middle_initial'];
    $position = $_POST['position'];
    $salary = $_POST['salary'];
    $email = $_POST['email'];
    $contact_number = $_POST['contact_number'];
    $address = $_POST['address'];
    $date_hired = $_POST['date_hired'];

    $sql = "CALL UpdateEmployee(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "iissssdssss",
        $emp_id,
        $user_id,
        $first,
        $last,
        $mi,
        $position,
        $salary,
        $email,
        $contact_number,
        $address,
        $date_hired
    );

    $stmt->execute();

    header("Location: employee_page.php");
    exit();
}


// Fetch Employees 
// view statement for employee directory
$result = $conn->query("SELECT * FROM view_employee_directory");

?>


    <!doctype html>
    <html lang="en">
    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Employee Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
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

    body {
        background: linear-gradient(135deg, #00416a 0%, #1c92d2 100%);
    }

    .page-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        color: white;
        padding: 2rem 0;
        margin-bottom: 2rem;
        border-radius: 0 0 24px 24px;
        box-shadow: 0 4px 20px rgba(25, 118, 210, 0.3);
    }

    .page-header h1 {
        font-weight: 600;
        font-size: 2rem;
        margin: 0;
        letter-spacing: 0.5px;
    }

    .card-container {
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        padding: 1.5rem;
        margin-bottom: 2rem;
    }

    .btn-add-employee {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        border: none;
        border-radius: 12px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 12px rgba(25, 118, 210, 0.3);
        transition: all 0.3s ease;
        color: white;
    }

    .btn-add-employee:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(25, 118, 210, 0.4);
        color: white;
    }

    .table-container {
        overflow-x: auto;
        border-radius: 12px;
    }

    .table {
        margin-bottom: 0;
    }

    .table thead th {
        background: linear-gradient(135deg, #37474f 0%, #455a64 100%);
        color: white;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        border: none;
        padding: 1rem 0.75rem;
        white-space: nowrap;
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
        padding: 1rem 0.75rem;
        vertical-align: middle;
        font-size: 0.875rem;
    }

    .badge-id {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        color: white;
        padding: 0.5rem 0.75rem;
        border-radius: 8px;
        font-weight: 600;
        display: inline-block;
        min-width: 40px;
        text-align: center;
    }

    .employee-name {
        font-weight: 600;
        color: #263238;
    }

    .salary-amount {
        font-weight: 600;
        color: var(--success-color);
    }

    .badge {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.75rem;
    }

    .badge.bg-success {
        background: linear-gradient(135deg, var(--success-color) 0%, #388e3c 100%) !important;
    }

    .badge.bg-secondary {
        background: linear-gradient(135deg, #757575 0%, #616161 100%) !important;
    }

    .btn-action {
        border-radius: 8px;
        padding: 0.4rem 0.8rem;
        font-weight: 600;
        border: none;
        transition: all 0.2s ease;
        font-size: 0.75rem;
        margin: 0.25rem;
    }

    .btn-update {
        background: linear-gradient(135deg, var(--info-color) 0%, #0277bd 100%);
        color: white;
        box-shadow: 0 2px 8px rgba(2, 136, 209, 0.3);
    }

    .btn-update:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(2, 136, 209, 0.4);
        color: white;
    }

    .btn-deactivate {
        background: linear-gradient(135deg, var(--warning-color) 0%, #fb8c00 100%);
        color: white;
        box-shadow: 0 2px 8px rgba(245, 124, 0, 0.3);
    }

    .btn-deactivate:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(245, 124, 0, 0.4);
        color: white;
    }

    .btn-activate {
        background: linear-gradient(135deg, var(--success-color) 0%, #388e3c 100%);
        color: white;
        box-shadow: 0 2px 8px rgba(46, 125, 50, 0.3);
    }

    .btn-activate:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(46, 125, 50, 0.4);
        color: white;
    }

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

    .modal-title {
        font-weight: 600;
        font-size: 1.25rem;
    }

    .modal-body {
        padding: 1.5rem;
    }

    .form-label {
        font-weight: 600;
        color: #37474f;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }

    .form-control, .form-control:focus {
        border-radius: 12px;
        border: 2px solid #e0e0e0;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(25, 118, 210, 0.15);
    }

    .password-wrapper {
        position: relative;
    }

    .password-toggle {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #757575;
        font-size: 1.25rem;
        transition: color 0.2s ease;
    }

    .password-toggle:hover {
        color: var(--primary-color);
    }

    .modal-footer {
        border-top: none;
        padding: 1rem 1.5rem 1.5rem;
    }

    .btn-modal-cancel {
        background: #f5f5f5;
        color: #546e7a;
        border: none;
        border-radius: 12px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .btn-modal-cancel:hover {
        background: #e0e0e0;
        transform: translateY(-2px);
    }

    .btn-modal-submit {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(25, 118, 210, 0.3);
        transition: all 0.2s ease;
    }

    .btn-modal-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(25, 118, 210, 0.4);
    }

    .btn-modal-submit-success {
        background: linear-gradient(135deg, var(--success-color) 0%, #388e3c 100%);
        box-shadow: 0 4px 12px rgba(46, 125, 50, 0.3);
    }

    .btn-modal-submit-success:hover {
        box-shadow: 0 6px 16px rgba(46, 125, 50, 0.4);
    }

    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: #90a4ae;
    }

    .empty-state i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .bi {
        vertical-align: middle;
    }

    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        margin: 0;
    }

    .stat-label {
        color: #757575;
        font-size: 0.875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    @media (max-width: 768px) {
        .page-header h1 {
            font-size: 1.5rem;
        }
        
        .btn-action {
            display: block;
            width: 100%;
            margin: 0.25rem 0;
        }
        
        .table {
            font-size: 0.75rem;
        }
    }
    </style>
    </head>
    <body>

    <div class="page-header">
        <div class="container">
            <h1><i class="bi bi-people-fill me-2"></i>Employee Management</h1>
        </div>
    </div>

    <div class="container">
        <?php
        // Calculate statistics
        $total_employees = $result->num_rows;
        $active_count = 0;
        $inactive_count = 0;
        $total_salary = 0;
        
        if ($result->num_rows > 0) {
            $result->data_seek(0);
            while ($row = $result->fetch_assoc()) {
                if ($row['account_status'] === 'Active') {
                    $active_count++;
                    $total_salary += $row['salary'];
                } else {
                    $inactive_count++;
                }
            }
            $result->data_seek(0);
        }
        ?>
        
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%); color: white;">
                    <i class="bi bi-people"></i>
                </div>
                <p class="stat-value"><?php echo $total_employees; ?></p>
                <p class="stat-label">Total Employees</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, var(--success-color) 0%, #388e3c 100%); color: white;">
                    <i class="bi bi-check-circle"></i>
                </div>
                <p class="stat-value"><?php echo $active_count; ?></p>
                <p class="stat-label">Active</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #757575 0%, #616161 100%); color: white;">
                    <i class="bi bi-x-circle"></i>
                </div>
                <p class="stat-value"><?php echo $inactive_count; ?></p>
                <p class="stat-label">Inactive</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, var(--success-color) 0%, #388e3c 100%); color: white;">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <p class="stat-value" style="font-size: 1.5rem;">₱<?php echo number_format($total_salary, 0); ?></p>
                <p class="stat-label">Total Payroll</p>
            </div>
        </div>

        <div class="card-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5 class="mb-0" style="color: #37474f; font-weight: 600;">Employee Directory</h5>
                    <small class="text-muted">Manage your team members</small>
                </div>
                <button class="btn btn-add-employee" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                    <i class="bi bi-person-plus me-1"></i> Add Employee
                </button>
            </div>

            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Last Name</th>
                            <th>First Name</th>
                            <th>MI</th>
                            <th>Position</th>
                            <th>Salary</th>
                            <th>Email</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    if ($result && $result->num_rows > 0) {
                        $count = 1;
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                                <td><span class='badge-id'>{$count}</span></td>
                                <td><div class='employee-name'>{$row['last_name']}</div></td>
                                <td><div class='employee-name'>{$row['first_name']}</div></td>
                                <td>{$row['middle_initial']}</td>
                                <td>{$row['position']}</td>
                                <td><span class='salary-amount'>₱" . number_format($row['salary'], 2) . "</span></td>
                                <td>{$row['email']}</td>
                                <td><span class='badge bg-primary'>{$row['user_type']}</span></td>
                                <td>
                                    <span class='badge " . 
                                        ($row['account_status'] === 'Active' ? 'bg-success' : 'bg-secondary') . 
                                    "'>" . ($row['account_status'] === 'Active' ? '<i class="bi bi-check-circle me-1"></i>' : '<i class="bi bi-x-circle me-1"></i>') . "{$row['account_status']}</span>
                                </td>
                                <td>
                                  <button class='btn btn-update btn-action btn-sm update-btn'
                                        data-id='{$row['id']}'
                                        data-user_id='{$row['user_id']}'
                                        data-first='{$row['first_name']}'
                                        data-last='{$row['last_name']}'
                                        data-mi='{$row['middle_initial']}'
                                        data-position='{$row['position']}'
                                        data-salary='{$row['salary']}'
                                        data-email='{$row['email']}'
                                        data-contact_number='{$row['contact_number']}'
                                        data-address=\"" . htmlspecialchars($row['address'], ENT_QUOTES) . "\"
                                        data-date_hired='{$row['date_hired']}'
                                        data-bs-toggle='modal' data-bs-target='#updateEmployeeModal'>
                                        <i class='bi bi-pencil-square'></i> Update
                                    </button>";



                            if ($row['account_status'] === 'Active') {
                                echo " <a href='?toggle_id={$row['user_id']}&action=deactivate' class='btn btn-deactivate btn-action btn-sm' onclick=\"return confirm('Are you sure you want to deactivate this employee?');\"><i class='bi bi-toggle-off'></i> Deactivate</a>";
                            } else {
                                echo " <a href='?toggle_id={$row['user_id']}&action=activate' class='btn btn-activate btn-action btn-sm'><i class='bi bi-toggle-on'></i> Activate</a>";
                            }

                            echo "</td></tr>";
                            $count++;
                        }
                    } else {
                        echo "<tr><td colspan='10'><div class='empty-state'><i class='bi bi-people'></i><p>No employees found. Start by adding your first employee!</p></div></td></tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

   <!-- Add Employee Modal -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%); color: white;">
        <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Add New Employee</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <form method="POST" id="addEmployeeForm" onsubmit="return validateAddForm()">
          <input type="hidden" name="user_type" value="employee">

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label"><i class="bi bi-person me-1"></i>First Name</label>
              <input type="text" name="first_name" class="form-control" placeholder="Enter first name" required>
            </div>

            <div class="col-md-3 mb-3">
              <label class="form-label">MI</label>
              <input type="text" name="middle_initial" class="form-control" placeholder="M.I." maxlength="2">
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label"><i class="bi bi-person me-1"></i>Last Name</label>
              <input type="text" name="last_name" class="form-control" placeholder="Enter last name" required>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label"><i class="bi bi-envelope me-1"></i>Email</label>
              <input type="email" name="email" class="form-control" placeholder="employee@example.com" required>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label"><i class="bi bi-telephone me-1"></i>Contact Number</label>
              <input type="text" name="contact_number" class="form-control" placeholder="Enter contact number" required>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label"><i class="bi bi-briefcase me-1"></i>Position</label>
              <input type="text" name="position" class="form-control" placeholder="Job position" required>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label"><i class="bi bi-cash me-1"></i>Salary</label>
              <input type="number" name="salary" class="form-control" placeholder="0.00" step="0.01" required>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label"><i class="bi bi-calendar-date me-1"></i>Date Hired</label>
              <input type="date" name="date_hired" class="form-control" required>
            </div>

            <div class="col-md-12 mb-3">
              <label class="form-label"><i class="bi bi-geo-alt me-1"></i>Address</label>
              <textarea name="address" class="form-control" rows="2" placeholder="Enter complete address" required></textarea>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label"><i class="bi bi-lock me-1"></i>Password</label>
              <div class="password-wrapper position-relative">
                <input type="password" name="password" id="password" class="form-control" placeholder="Enter password" required>
                <i class="bi bi-eye password-toggle position-absolute top-50 end-0 translate-middle-y me-3"
                   onclick="togglePasswordVisibility('password', this)"></i>
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label"><i class="bi bi-lock-fill me-1"></i>Confirm Password</label>
              <div class="password-wrapper position-relative">
                <input type="password" name="cpassword" id="cpassword" class="form-control" placeholder="Confirm password" required>
                <i class="bi bi-eye password-toggle position-absolute top-50 end-0 translate-middle-y me-3"
                   onclick="togglePasswordVisibility('cpassword', this)"></i>
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-end gap-2 mt-3">
            <button type="button" class="btn btn-modal-cancel" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="add_employee" class="btn btn-modal-submit btn-modal-submit-success">
              <i class="bi bi-check-circle me-1"></i>Add Employee
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Update Employee Modal -->
<div class="modal fade" id="updateEmployeeModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, var(--info-color) 0%, #0277bd 100%); color: white;">
        <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Update Employee</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <form method="POST">
          <input type="hidden" name="emp_id" id="update_emp_id">
          <input type="hidden" name="user_id" id="update_user_id">

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label"><i class="bi bi-person me-1"></i>First Name</label>
              <input type="text" name="first_name" id="update_first_name" class="form-control" required>
            </div>

            <div class="col-md-3 mb-3">
              <label class="form-label">MI</label>
              <input type="text" name="middle_initial" id="update_middle_initial" class="form-control" maxlength="2">
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label"><i class="bi bi-person me-1"></i>Last Name</label>
              <input type="text" name="last_name" id="update_last_name" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label"><i class="bi bi-envelope me-1"></i>Email</label>
              <input type="email" name="email" id="update_email" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label"><i class="bi bi-telephone me-1"></i>Contact Number</label>
              <input type="text" name="contact_number" id="update_contact_number" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label"><i class="bi bi-briefcase me-1"></i>Position</label>
              <input type="text" name="position" id="update_position" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label"><i class="bi bi-cash me-1"></i>Salary</label>
              <input type="number" name="salary" id="update_salary" class="form-control" step="0.01" required>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label"><i class="bi bi-calendar-date me-1"></i>Date Hired</label>
              <input type="date" name="date_hired" id="update_date_hired" class="form-control" required>
            </div>

            <div class="col-md-12 mb-3">
              <label class="form-label"><i class="bi bi-geo-alt me-1"></i>Address</label>
              <textarea name="address" id="update_address" class="form-control" rows="2" required></textarea>
            </div>
          </div>

          <div class="d-flex justify-content-end gap-2 mt-3">
            <button type="button" class="btn btn-modal-cancel" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="update_employee" class="btn btn-modal-submit btn-modal-submit-success">
              <i class="bi bi-check-circle me-1"></i>Update Employee
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>



    <script>
        document.querySelectorAll('.update-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('update_emp_id').value = this.dataset.id;
            document.getElementById('update_user_id').value = this.dataset.user_id;
            document.getElementById('update_first_name').value = this.dataset.first;
            document.getElementById('update_last_name').value = this.dataset.last;
            document.getElementById('update_middle_initial').value = this.dataset.mi;
            document.getElementById('update_position').value = this.dataset.position;
            document.getElementById('update_salary').value = this.dataset.salary;
            document.getElementById('update_email').value = this.dataset.email;
            document.getElementById('update_contact_number').value = this.dataset.contact_number || '';
            document.getElementById('update_address').value = this.dataset.address || '';
            document.getElementById('update_date_hired').value = this.dataset.date_hired || '';
        });
    });


    function togglePasswordVisibility(fieldId, iconElement) {
        const field = document.getElementById(fieldId);
        if (field.type === 'password') {
            field.type = 'text';
            iconElement.classList.remove('bi-eye');
            iconElement.classList.add('bi-eye-slash');
        } else {
            field.type = 'password';
            iconElement.classList.remove('bi-eye-slash');
            iconElement.classList.add('bi-eye');
        }
    }

    function validateAddForm() {
        const pass = document.getElementById('password').value;
        const cpass = document.getElementById('cpassword').value;
        if (pass !== cpass) {
            alert('Passwords do not match!');
            return false;
        }
        return true;
    }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>

    <?php
    include('footer.php');
    ob_end_flush();
    ?>
        
