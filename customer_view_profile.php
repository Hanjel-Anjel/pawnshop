<?php
    session_start();
    @include 'config.php';

    // Redirect if not logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: customer_login_page.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];

    // Fetch customer information
    $stmt = $conn->prepare("SELECT * FROM custumer_info WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $profile = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Default profile picture
    $profile_pic = !empty($profile['profile_image'])
        ? 'uploads/' . htmlspecialchars($profile['profile_image'])
        : 'assets/default_profile.png'; // fallback image
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>My Profile - ArMaTech Pawnshop</title>
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

            .navbar-brand i {
                font-size: 1.75rem;
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

            /* Profile Container */
            .profile-container {
                max-width: 1000px;
                margin: 2rem auto;
                padding: 0 1rem;
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

            /* Profile Header */
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
                margin-bottom: 1.5rem;
            }

            .profile-pic {
                width: 150px;
                height: 150px;
                object-fit: cover;
                border-radius: 50%;
                border: 5px solid white;
                box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
                position: relative;
                z-index: 1;
            }

            .avatar-badge {
                position: absolute;
                bottom: 5px;
                right: 5px;
                width: 45px;
                height: 45px;
                background: linear-gradient(135deg, var(--success-color) 0%, #388e3c 100%);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 1.5rem;
                border: 4px solid white;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            }

            .profile-name {
                font-size: 2rem;
                font-weight: 700;
                margin: 0.5rem 0;
                position: relative;
                z-index: 1;
            }

            .profile-phone {
                font-size: 1.1rem;
                opacity: 0.95;
                margin-bottom: 1.5rem;
                position: relative;
                z-index: 1;
            }

            .btn-edit-profile {
                background: white;
                color: var(--primary-color);
                border-radius: 12px;
                padding: 0.75rem 2rem;
                font-weight: 600;
                border: none;
                transition: all 0.3s ease;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                position: relative;
                z-index: 1;
            }

            .btn-edit-profile:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(255, 255, 255, 0.3);
                color: var(--primary-color);
            }

            /* Profile Body */
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

            .info-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 1.5rem;
                margin-bottom: 2rem;
            }

            .info-item {
                background: #f8f9fa;
                border-radius: 12px;
                padding: 1.25rem;
                transition: all 0.3s ease;
                border: 2px solid transparent;
            }

            .info-item:hover {
                border-color: var(--primary-color);
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(25, 118, 210, 0.15);
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
                font-size: 1rem;
                color: #263238;
                font-weight: 500;
                word-wrap: break-word;
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
                justify-content: center;
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
                background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
                color: white;
                box-shadow: 0 4px 12px rgba(25, 118, 210, 0.3);
            }

            .btn-back:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 16px rgba(25, 118, 210, 0.4);
                color: white;
            }

            /* Responsive */
            @media (max-width: 768px) {
                .profile-name {
                    font-size: 1.5rem;
                }

                .profile-body {
                    padding: 1.5rem;
                }

                .info-grid {
                    grid-template-columns: 1fr;
                }

                .action-buttons {
                    flex-direction: column;
                }

                .btn-action {
                    width: 100%;
                    justify-content: center;
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
                       <!-- <li>
                            <a class="dropdown-item" href="edit_customer_info.php">
                                <i class="fas fa-edit me-2"></i>Edit Profile
                            </a>
                        </li> -->
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

    <!-- Profile Content -->
    <div class="profile-container">
        <div class="profile-card">
            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-avatar-wrapper">
                    <img src="display_image.php?user_id=<?php echo $user_id; ?>" alt="Profile Picture" class="profile-pic">
                   
                </div>
                <h2 class="profile-name"><?php echo htmlspecialchars($profile['first_name'] . ' ' . $profile['last_name']); ?></h2>
                <p class="profile-phone">
                    <i class="bi bi-telephone-fill me-2"></i><?php echo htmlspecialchars($profile['phone_no']); ?>
                </p>
              <!--  <a href="edit_customer_info.php" class="btn-edit-profile">
                    <i class="bi bi-pencil-square"></i> Edit Profile
                </a> -->
            </div>

            <!-- Profile Body -->
            <div class="profile-body">
                <!-- Personal Information Section -->
                <h5 class="section-title">
                    <i class="bi bi-person-lines-fill"></i>
                    Personal Information
                </h5>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-person-fill"></i>
                            Full Name
                        </div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($profile['first_name'] . ' ' . $profile['middle_initial'] . ' ' . $profile['last_name'] . ' ' . $profile['suffix']); ?>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-cake2"></i>
                            Birthday
                        </div>
                        <div class="info-value">
                            <?php echo date('F j, Y', strtotime($profile['birthday'])); ?>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-telephone"></i>
                            Phone Number
                        </div>
                        <div class="info-value"><?php echo htmlspecialchars($profile['phone_no']); ?></div>
                    </div>
                </div>

                <div class="divider"></div>

                <!-- Address Information Section -->
                <h5 class="section-title">
                    <i class="bi bi-geo-alt-fill"></i>
                    Address Information
                </h5>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-map"></i>
                            Region
                        </div>
                        <div class="info-value"><?php echo htmlspecialchars($profile['region']); ?></div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-pin-map"></i>
                            Province
                        </div>
                        <div class="info-value"><?php echo htmlspecialchars($profile['province']); ?></div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-building"></i>
                            City / Municipality
                        </div>
                        <div class="info-value"><?php echo htmlspecialchars($profile['city']); ?></div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-signpost"></i>
                            Barangay
                        </div>
                        <div class="info-value"><?php echo htmlspecialchars($profile['barangay']); ?></div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-signpost-2"></i>
                            Street
                        </div>
                        <div class="info-value"><?php echo htmlspecialchars($profile['street']); ?></div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-house-door"></i>
                            House Number
                        </div>
                        <div class="info-value"><?php echo htmlspecialchars($profile['house_number']); ?></div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <a href="customer_dashboard.php" class="btn btn-back btn-action">
                        <i class="bi bi-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>