<?php
session_start();
include('d:\xampp\htdocs\pawnshop\config.php');

// Ensure employee is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'employee') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch employee info (joined with users)
$query = $conn->prepare("
    SELECT 
        e.*, 
        u.email, 
        u.created_at AS account_created 
    FROM employee_details e
    JOIN users u ON e.user_id = u.user_id
    WHERE e.user_id = ?
");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$employee = $result->fetch_assoc();

if (!$employee) {
    echo "<script>alert('Employee record not found.'); window.location.href='home_employee.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Employee Profile</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
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
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      padding: 2rem 0;
    }

    .profile-container {
      max-width: 1000px;
      margin: 0 auto;
    }

    .profile-card {
      background: white;
      border-radius: 24px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
      overflow: hidden;
      animation: fadeInUp 0.6s ease;
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

    .profile-header {
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
      color: white;
      padding: 3rem 2rem 2rem;
      position: relative;
      text-align: center;
    }

    .profile-header::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="20" height="20" patternUnits="userSpaceOnUse"><path d="M 20 0 L 0 0 0 20" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
      opacity: 0.3;
    }

    .profile-avatar-wrapper {
      position: relative;
      display: inline-block;
      margin-bottom: 1rem;
    }

    .profile-avatar {
      width: 140px;
      height: 140px;
      border-radius: 50%;
      object-fit: cover;
      border: 5px solid white;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
      position: relative;
      z-index: 1;
    }

    .avatar-badge {
      position: absolute;
      bottom: 5px;
      right: 5px;
      width: 40px;
      height: 40px;
      background: linear-gradient(135deg, var(--success-color) 0%, #388e3c 100%);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 1.2rem;
      border: 4px solid white;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }

    .profile-name {
      font-size: 2rem;
      font-weight: 700;
      margin: 1rem 0 0.5rem;
      position: relative;
      z-index: 1;
    }

    .profile-position {
      font-size: 1.1rem;
      opacity: 0.95;
      margin-bottom: 1rem;
      position: relative;
      z-index: 1;
    }

    .badge-status {
      display: inline-flex;
      align-items: center;
      padding: 0.5rem 1.5rem;
      border-radius: 50px;
      font-weight: 600;
      font-size: 0.875rem;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      position: relative;
      z-index: 1;
    }

    .badge-status.bg-success {
      background: linear-gradient(135deg, var(--success-color) 0%, #388e3c 100%) !important;
    }

    .badge-status.bg-danger {
      background: linear-gradient(135deg, var(--danger-color) 0%, #c62828 100%) !important;
    }

    .profile-body {
      padding: 2.5rem;
    }

    .section-title {
      font-size: 1.25rem;
      font-weight: 700;
      color: #263238;
      margin-bottom: 1.5rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .section-title i {
      font-size: 1.5rem;
      color: var(--primary-color);
    }

    .info-card {
      background: #f8f9fa;
      border-radius: 12px;
      padding: 1.5rem;
      margin-bottom: 1rem;
      transition: all 0.3s ease;
      border: 2px solid transparent;
    }

    .info-card:hover {
      border-color: var(--primary-color);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(25, 118, 210, 0.15);
    }

    .info-item {
      margin-bottom: 1.5rem;
    }

    .info-item:last-child {
      margin-bottom: 0;
    }

    .info-label {
      font-weight: 600;
      color: #546e7a;
      font-size: 0.875rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 0.5rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .info-label i {
      color: var(--primary-color);
      font-size: 1rem;
    }

    .info-value {
      font-size: 1.125rem;
      color: #263238;
      font-weight: 500;
    }

    .salary-highlight {
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--success-color);
    }

    .divider {
      height: 2px;
      background: linear-gradient(90deg, transparent, var(--primary-color), transparent);
      margin: 2rem 0;
      opacity: 0.3;
    }

    .action-buttons {
      display: flex;
      gap: 1rem;
      justify-content: flex-end;
      margin-top: 2rem;
    }

    .btn-action {
      padding: 0.75rem 2rem;
      border-radius: 12px;
      font-weight: 600;
      border: none;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
    }

    .btn-back {
      background: #f5f5f5;
      color: #546e7a;
    }

    .btn-back:hover {
      background: #e0e0e0;
      transform: translateY(-2px);
      color: #546e7a;
    }

    .btn-edit {
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
      color: white;
      box-shadow: 0 4px 12px rgba(25, 118, 210, 0.3);
    }

    .btn-edit:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(25, 118, 210, 0.4);
      color: white;
    }

    .stat-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
      margin-bottom: 2rem;
    }

    .stat-item {
      background: white;
      border-radius: 12px;
      padding: 1.5rem;
      text-align: center;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
      transition: all 0.3s ease;
    }

    .stat-item:hover {
      transform: translateY(-4px);
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
    }

    .stat-icon {
      width: 60px;
      height: 60px;
      margin: 0 auto 1rem;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.75rem;
      color: white;
    }

    .stat-label {
      font-size: 0.875rem;
      color: #757575;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 0.5rem;
    }

    .stat-value {
      font-size: 1.5rem;
      font-weight: 700;
      color: #263238;
    }

    @media (max-width: 768px) {
      .profile-name {
        font-size: 1.5rem;
      }

      .profile-body {
        padding: 1.5rem;
      }

      .action-buttons {
        flex-direction: column;
      }

      .btn-action {
        width: 100%;
        justify-content: center;
      }

      .stat-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>

<body>
  <div class="container profile-container">
    <div class="profile-card">
      <div class="profile-header">
        <div class="profile-avatar-wrapper">
          <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png" alt="Profile Picture" class="profile-avatar">
          
        </div>
        <h2 class="profile-name"><?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?></h2>
        <p class="profile-position"><i class="bi bi-briefcase-fill me-2"></i><?= htmlspecialchars($employee['position']) ?></p>
        <span class="badge-status <?= ($employee['status'] == 'Active') ? 'bg-success' : 'bg-danger' ?>">
          <i class="bi <?= ($employee['status'] == 'Active') ? 'bi-check-circle-fill' : 'bi-x-circle-fill' ?> me-2"></i>
          <?= htmlspecialchars($employee['status']) ?>
        </span>
      </div>

      <div class="profile-body">
        <!-- Quick Stats -->
        <div class="stat-grid">
          <div class="stat-item">
            <div class="stat-icon" style="background: linear-gradient(135deg, var(--success-color) 0%, #388e3c 100%);">
              <i class="bi bi-cash-stack"></i>
            </div>
            <div class="stat-label">Monthly Salary</div>
            <div class="stat-value">₱<?= number_format($employee['salary'], 0) ?></div>
          </div>
          
          <div class="stat-item">
            <div class="stat-icon" style="background: linear-gradient(135deg, var(--info-color) 0%, #0277bd 100%);">
              <i class="bi bi-calendar-check"></i>
            </div>
            <div class="stat-label">Date Hired</div>
            <div class="stat-value" style="font-size: 1rem;">
              <?= $employee['date_hired'] ? date('M j, Y', strtotime($employee['date_hired'])) : 'Not set' ?>
            </div>
          </div>
          
          <div class="stat-item">
            <div class="stat-icon" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);">
              <i class="bi bi-person-badge"></i>
            </div>
            <div class="stat-label">Employee ID</div>
            <div class="stat-value">#<?= str_pad($employee['id'], 5, '0', STR_PAD_LEFT) ?></div>
          </div>
        </div>

        <!-- Personal Information -->
        <h5 class="section-title">
          <i class="bi bi-person-lines-fill"></i>
          Personal Information
        </h5>
        <div class="info-card">
          <div class="row">
            <div class="col-md-6">
              <div class="info-item">
                <div class="info-label">
                  <i class="bi bi-person-fill"></i>
                  Full Name
                </div>
                <div class="info-value">
                  <?= htmlspecialchars($employee['first_name'] . ' ' . $employee['middle_initial'] . ' ' . $employee['last_name']) ?>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="info-item">
                <div class="info-label">
                  <i class="bi bi-briefcase-fill"></i>
                  Position
                </div>
                <div class="info-value"><?= htmlspecialchars($employee['position']) ?></div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="info-item">
                <div class="info-label">
                  <i class="bi bi-telephone-fill"></i>
                  Contact Number
                </div>
                <div class="info-value"><?= htmlspecialchars($employee['contact_number'] ?: 'Not set') ?></div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="info-item">
                <div class="info-label">
                  <i class="bi bi-geo-alt-fill"></i>
                  Address
                </div>
                <div class="info-value"><?= htmlspecialchars($employee['address'] ?: 'Not set') ?></div>
              </div>
            </div>
          </div>
        </div>

        <div class="divider"></div>

        <!-- Employment Information -->
        <h5 class="section-title">
          <i class="bi bi-building"></i>
          Employment Details
        </h5>
        <div class="info-card">
          <div class="row">
            <div class="col-md-6">
              <div class="info-item">
                <div class="info-label">
                  <i class="bi bi-calendar-event"></i>
                  Date Hired
                </div>
                <div class="info-value">
                  <?= $employee['date_hired'] ? date('F j, Y', strtotime($employee['date_hired'])) : 'Not set' ?>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="info-item">
                <div class="info-label">
                  <i class="bi bi-cash-coin"></i>
                  Monthly Salary
                </div>
                <div class="info-value salary-highlight">₱<?= number_format($employee['salary'], 2) ?></div>
              </div>
            </div>
          </div>
        </div>

        <div class="divider"></div>

        <!-- Account Information -->
        <h5 class="section-title">
          <i class="bi bi-person-circle"></i>
          Account Information
        </h5>
        <div class="info-card">
          <div class="row">
            <div class="col-md-6">
              <div class="info-item">
                <div class="info-label">
                  <i class="bi bi-envelope-fill"></i>
                  Email Address
                </div>
                <div class="info-value"><?= htmlspecialchars($employee['email']) ?></div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="info-item">
                <div class="info-label">
                  <i class="bi bi-calendar-plus"></i>
                  Account Created
                </div>
                <div class="info-value"><?= date('F j, Y', strtotime($employee['account_created'])) ?></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
          <a href="home_employee.php" class="btn btn-back btn-action">
            <i class="bi bi-arrow-left"></i> Back to Home
          </a>
          <!-- <a href="employee_edit_profile.php" class="btn btn-edit btn-action">
            <i class="bi bi-pencil-square"></i> Edit Profile
          </a> -->
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>