<?php
session_start();
@include 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: customer_login_page.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error_message = "";

// Function to fetch name from PSGC API based on code
function fetchNameFromAPI($url)
{
    $response = @file_get_contents($url);
    if ($response) {
        $data = json_decode($response, true);
        return $data['name'] ?? null;
    }
    return null;
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name']);
    $middle_initial = trim($_POST['middle_initial']);
    $last_name = trim($_POST['last_name']);
    $suffix = trim($_POST['suffix']);
    $birthday = $_POST['birthday'];
    $phone_no = trim($_POST['phone_no']);

    // Validate Birthday (18+)
    $dob = new DateTime($birthday);
    $today = new DateTime();
    $age = $today->diff($dob)->y;

    if ($age < 18) {
        $error_message = "You must be at least 18 years old.";
    } else {
        // Address fields
        $regionCode = $_POST['region'] ?? '';
        $provinceCode = $_POST['province'] ?? '';
        $cityCode = $_POST['city'] ?? '';
        $barangayCode = $_POST['barangay'] ?? '';
        $street = trim($_POST['street']);
        $houseNumber = trim($_POST['houseNumber']);

        // Fetch full names
        $region = fetchNameFromAPI("https://psgc.gitlab.io/api/regions/$regionCode/");
        $province = ($regionCode === "130000000") ? "Metro Manila" : fetchNameFromAPI("https://psgc.gitlab.io/api/provinces/$provinceCode/");
        $city = fetchNameFromAPI("https://psgc.gitlab.io/api/cities-municipalities/$cityCode/");
        $barangay = fetchNameFromAPI("https://psgc.gitlab.io/api/barangays/$barangayCode/");

        // Update query
        $stmt = $conn->prepare("UPDATE custumer_info SET first_name=?, middle_initial=?, last_name=?, suffix=?, birthday=?, phone_no=?, region=?, province=?, city=?, barangay=?, street=?, house_number=? WHERE user_id=?");
        $stmt->bind_param("ssssssssssssi", $first_name, $middle_initial, $last_name, $suffix, $birthday, $phone_no, $region, $province, $city, $barangay, $street, $houseNumber, $user_id);

        if ($stmt->execute()) {
            // Redirect back to dashboard after successful update
            header("Location: customer_view_profile.php?update=success");
            exit();
        } else {
            $error_message = "Update failed: " . $stmt->error;
        }

        $stmt->close();
    }
}

// Fetch customer info
$stmt = $conn->prepare("SELECT * FROM custumer_info WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - ArMaTech Pawnshop</title>
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-bottom: 3rem;
        }

        /* Navbar Styles */
        .navbar-custom {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            padding: 1rem 0;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: white !important;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: white;
            font-weight: 600;
        }

        .btn-dashboard {
            background: white;
            color: var(--primary-color);
            border-radius: 12px;
            padding: 0.5rem 1.5rem;
            font-weight: 600;
            border: none;
            transition: all 0.3s ease;
        }

        .btn-dashboard:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 255, 255, 0.3);
            color: var(--primary-color);
        }

        /* Form Container */
        .form-container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .form-card {
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

        /* Form Header */
        .form-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 2rem;
            text-align: center;
            position: relative;
        }

        .form-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="20" height="20" patternUnits="userSpaceOnUse"><path d="M 20 0 L 0 0 0 20" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }

        .form-header h2 {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
            position: relative;
            z-index: 1;
        }

        .form-header i {
            font-size: 3rem;
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
        }

        /* Form Body */
        .form-body {
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
            padding-bottom: 0.75rem;
            border-bottom: 3px solid var(--primary-color);
        }

        .section-title i {
            font-size: 1.5rem;
            color: var(--primary-color);
        }

        .form-label {
            font-weight: 600;
            color: #546e7a;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-label i {
            color: var(--primary-color);
            font-size: 1rem;
        }

        .form-control, .form-select {
            background-color: #f8f9fa;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(25, 118, 210, 0.15);
            background-color: white;
        }

        .form-control:disabled, .form-select:disabled {
            background-color: #e9ecef;
            cursor: not-allowed;
        }

        .input-group-icon {
            position: relative;
        }

        .input-group-icon .form-control {
            padding-left: 2.75rem;
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-color);
            font-size: 1.25rem;
            z-index: 10;
        }

        .alert {
            border-radius: 12px;
            border: none;
            padding: 1rem 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }

        .alert-danger {
            background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
            color: var(--danger-color);
        }

        .alert-success {
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            color: var(--success-color);
        }

        .alert i {
            font-size: 1.25rem;
            margin-right: 0.5rem;
        }

        .text-danger {
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: block;
        }

        .divider {
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--primary-color), transparent);
            margin: 2rem 0;
            opacity: 0.3;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn-action {
            flex: 1;
            padding: 0.875rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            border: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-cancel {
            background: #f5f5f5;
            color: #546e7a;
        }

        .btn-cancel:hover {
            background: #e0e0e0;
            transform: translateY(-2px);
            color: #546e7a;
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--success-color) 0%, #388e3c 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(46, 125, 50, 0.3);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(46, 125, 50, 0.4);
            color: white;
        }

        .loading-spinner {
            display: none;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .form-body {
                padding: 1.5rem;
            }

            .action-buttons {
                flex-direction: column;
            }

            .user-info {
                flex-direction: column;
                gap: 0.5rem;
            }
        }

        /* === NAVBAR DROPDOWN STYLES === */
.navbar-toggler {
    border-color: rgba(102, 126, 234, 0.5);
    padding: 0.5rem;
}

.navbar-toggler-icon {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28102, 126, 234, 1%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
}

/* User Dropdown Toggle */
.user-dropdown {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white !important;
    padding: 10px 20px;
    border-radius: 25px;
    font-weight: 600;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    box-shadow: 0 3px 10px rgba(102, 126, 234, 0.3);
}

.user-dropdown:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.5);
}

.user-dropdown i {
    font-size: 1.3rem;
}

.user-dropdown::after {
    margin-left: 0.5rem;
    border-top-color: white;
}

/* Custom Dropdown Menu */
.custom-dropdown {
    border-radius: 16px;
    border: none;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    padding: 0.5rem 0;
    min-width: 250px;
    margin-top: 0.5rem;
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(10px);
    animation: dropdownSlide 0.3s ease-out;
}

@keyframes dropdownSlide {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.custom-dropdown .dropdown-header {
    font-weight: 700;
    color: #667eea;
    padding: 0.75rem 1.25rem;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.custom-dropdown .dropdown-item {
    padding: 0.75rem 1.25rem;
    font-weight: 500;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    color: #333;
}

.custom-dropdown .dropdown-item i {
    font-size: 1.125rem;
    width: 24px;
}

.custom-dropdown .dropdown-item:hover {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    padding-left: 1.5rem;
    color: #667eea;
}

.custom-dropdown .dropdown-item.text-danger {
    color: #dc3545 !important;
}

.custom-dropdown .dropdown-item.text-danger:hover {
    background: linear-gradient(135deg, rgba(220, 53, 69, 0.1), rgba(200, 35, 51, 0.1));
    color: #dc3545 !important;
}

.custom-dropdown .dropdown-divider {
    margin: 0.5rem 0;
    opacity: 0.2;
    border-top-color: #667eea;
}

/* Modal Adjustments */
.modal-footer .btn-secondary {
    background: #e0e0e0;
    border: none;
    color: #666;
    border-radius: 25px;
    padding: 10px 25px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.modal-footer .btn-secondary:hover {
    background: #bdbdbd;
    transform: translateY(-2px);
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.15);
}

/* Responsive Adjustments */
@media (max-width: 991px) {
    .user-dropdown {
        justify-content: flex-start;
        width: auto;
        margin: 0.5rem 0;
    }
    
    .custom-dropdown {
        width: 100%;
        margin-top: 0.5rem;
    }
}

@media (max-width: 768px) {
    .user-dropdown span {
        font-size: 0.9rem;
    }
    
    .user-dropdown i {
        font-size: 1.1rem;
    }
}
    </style>
</head>

<body>
    <!-- Navbar -->
<nav class="navbar navbar-expand-lg">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="customer_dashboard.php">
            <i class="fas fa-mobile-alt"></i>
            <span>ArMaTech Pawnshop</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <!-- User Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle user-dropdown" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle me-2"></i>
                        <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end custom-dropdown" aria-labelledby="userDropdown">
                        <li class="dropdown-header">
                            <i class="fas fa-user-cog me-2"></i>
                            Account Menu
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="customer_dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="customer_view_profile.php">
                                <i class="fas fa-eye me-2"></i>View Profile
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="edit_customer_info.php">
                                <i class="fas fa-edit me-2"></i>Edit Profile
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Logout Confirmation Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logoutModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirm Logout
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-sign-out-alt" style="font-size: 3rem; color: #ffc107; margin-bottom: 1rem;"></i>
                <p style="font-size: 1.1rem; color: #666;">Are you sure you want to logout?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="logout.php" class="btn btn-logout">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </div>
</div>

    <!-- Form Container -->
    <div class="form-container">
        <div class="form-card">
            <!-- Form Header -->
            <div class="form-header">
                <i class="bi bi-person-gear"></i>
                <h2>Edit Profile Information</h2>
            </div>

            <!-- Form Body -->
            <div class="form-body">
                <form method="post" onsubmit="return validateAge()">
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <?= htmlspecialchars($error_message) ?>
                        </div>
                    <?php elseif (isset($_GET['update']) && $_GET['update'] === 'success'): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle-fill"></i>
                            Profile updated successfully!
                        </div>
                    <?php endif; ?>

                    <!-- Personal Information Section -->
                    <h5 class="section-title">
                        <i class="bi bi-person-lines-fill"></i>
                        Personal Information
                    </h5>

                    <div class="row">
                        <div class="col-md-5 mb-3">
                            <label class="form-label">
                                <i class="bi bi-person"></i>
                                First Name
                            </label>
                            <input type="text" name="first_name" class="form-control" required value="<?= htmlspecialchars($profile['first_name'] ?? '') ?>" placeholder="Enter first name">
                        </div>

                        <div class="col-md-2 mb-3">
                            <label class="form-label">
                                <i class="bi bi-justify"></i>
                                M.I.
                            </label>
                            <input type="text" name="middle_initial" class="form-control" value="<?= htmlspecialchars($profile['middle_initial'] ?? '') ?>" maxlength="2" placeholder="M.I.">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">
                                <i class="bi bi-person"></i>
                                Last Name
                            </label>
                            <input type="text" name="last_name" class="form-control" required value="<?= htmlspecialchars($profile['last_name'] ?? '') ?>" placeholder="Enter last name">
                        </div>

                        <div class="col-md-1 mb-3">
                            <label class="form-label">
                                <i class="bi bi-tags"></i>
                                Suffix
                            </label>
                            <input type="text" name="suffix" class="form-control" value="<?= htmlspecialchars($profile['suffix'] ?? '') ?>" placeholder="Jr., Sr.">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <i class="bi bi-cake2"></i>
                                Birthday
                            </label>
                            <input type="date" id="birthday" name="birthday" class="form-control" required value="<?= htmlspecialchars($profile['birthday'] ?? '') ?>">
                            <small id="ageError" class="text-danger"></small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <i class="bi bi-telephone"></i>
                                Phone Number
                            </label>
                            <input type="text" name="phone_no" class="form-control" required value="<?= htmlspecialchars($profile['phone_no'] ?? '') ?>" placeholder="09XX XXX XXXX">
                        </div>
                    </div>

                    <div class="divider"></div>

                    <!-- Address Information Section -->
                    <h5 class="section-title">
                        <i class="bi bi-geo-alt-fill"></i>
                        Address Information
                    </h5>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <i class="bi bi-map"></i>
                                Region
                            </label>
                            <select id="region" name="region" class="form-select" required>
                                <option value="">Select Region</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <i class="bi bi-pin-map"></i>
                                Province
                            </label>
                            <select id="province" name="province" class="form-select" required>
                                <option value="">Select Province</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <i class="bi bi-building"></i>
                                City/Municipality
                            </label>
                            <select id="city" name="city" class="form-select" required>
                                <option value="">Select City</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <i class="bi bi-signpost"></i>
                                Barangay
                            </label>
                            <select id="barangay" name="barangay" class="form-select" required>
                                <option value="">Select Barangay</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">
                                <i class="bi bi-signpost-2"></i>
                                Street
                            </label>
                            <input type="text" name="street" class="form-control" value="<?= htmlspecialchars($profile['street'] ?? '') ?>" required placeholder="Enter street name">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">
                                <i class="bi bi-house-door"></i>
                                House Number
                            </label>
                            <input type="text" name="houseNumber" class="form-control" value="<?= htmlspecialchars($profile['house_number'] ?? '') ?>" required placeholder="123">
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <a href="customer_view_profile.php" class="btn btn-cancel btn-action">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-submit btn-action">
                            <i class="bi bi-check-circle"></i> Update Profile
                            <span class="spinner-border spinner-border-sm loading-spinner" role="status"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        async function fetchData(url) {
            const response = await fetch(url);
            return await response.json();
        }

        async function populateSelect(url, selectId, defaultText) {
            const data = await fetchData(url);
            const select = document.getElementById(selectId);
            select.innerHTML = `<option value="">${defaultText}</option>`;
            data.forEach(item => {
                const option = document.createElement("option");
                option.value = item.code;
                option.textContent = item.name;
                select.appendChild(option);
            });
        }

        document.addEventListener("DOMContentLoaded", () => {
            populateSelect("https://psgc.gitlab.io/api/regions/", "region", "Select Region");

            document.getElementById("region").addEventListener("change", function() {
                const regionCode = this.value;
                document.getElementById("province").disabled = !regionCode;
                document.getElementById("city").disabled = true;
                document.getElementById("barangay").disabled = true;

                if (regionCode === "130000000") {
                    document.getElementById("province").innerHTML = `<option value="NCR">Metro Manila</option>`;
                    populateSelect("https://psgc.gitlab.io/api/regions/130000000/cities-municipalities/", "city", "Select City");
                    document.getElementById("city").disabled = false;
                } else if (regionCode) {
                    populateSelect(`https://psgc.gitlab.io/api/regions/${regionCode}/provinces/`, "province", "Select Province");
                }
            });

            document.getElementById("province").addEventListener("change", function() {
                const provinceCode = this.value;
                document.getElementById("city").disabled = !provinceCode;
                document.getElementById("barangay").disabled = true;
                
                if (provinceCode) {
                    populateSelect(`https://psgc.gitlab.io/api/provinces/${provinceCode}/cities-municipalities/`, "city", "Select City");
                }
            });

            document.getElementById("city").addEventListener("change", function() {
                const cityCode = this.value;
                document.getElementById("barangay").disabled = !cityCode;
                
                if (cityCode) {
                    populateSelect(`https://psgc.gitlab.io/api/cities-municipalities/${cityCode}/barangays/`, "barangay", "Select Barangay");
                }
            });
        });

        function validateAge() {
            const birthdayInput = document.getElementById("birthday").value;
            const errorElement = document.getElementById("ageError");
            errorElement.textContent = "";

            if (!birthdayInput) return true;
            const birthDate = new Date(birthdayInput);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();

            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) age--;

            if (age < 18) {
                errorElement.textContent = "You must be at least 18 years old.";
                return false;
            }
            return true;
        }

        // Show loading spinner on submit
        document.querySelector('form').addEventListener('submit', function(e) {
            if (validateAge()) {
                document.querySelector('.loading-spinner').style.display = 'inline-block';
                document.querySelector('.btn-submit').disabled = true;
            }
        });

        // Real-time age validation
        document.getElementById('birthday').addEventListener('change', validateAge);
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>