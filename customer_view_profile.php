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
        <title>My Profile</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <style>
            body {
                background-color: #f7f9fc;
            }
            .profile-container {
                max-width: 850px;
                margin: 60px auto;
                background: white;
                border-radius: 10px;
                box-shadow: 0px 0px 12px rgba(0,0,0,0.1);
                padding: 40px;
            }
            .profile-pic {
                width: 160px;
                height: 160px;
                object-fit: cover;
                border-radius: 50%;
                border: 4px solid #007bff;
            }
            .info-label {
                font-weight: bold;
                color: #007bff;
            }
            .info-value {
                color: #333;
            }
            .back-btn {
                background-color: #007bff;
                border: none;
            }
            .back-btn:hover {
                background-color: #0056b3;
            }
        </style>
    </head>
    <body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg bg-primary navbar-dark">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="#">
                <i class="fas fa-mobile-alt"></i>
                <span>ArMaTech Pawnshop</span>
            </a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3">
                    <i class="fas fa-user-circle me-2"></i><?php echo htmlspecialchars($_SESSION['user_name']); ?>
                </span>
                <a href="customer_dashboard.php" class="btn btn-light btn-sm">Dashboard</a>
            </div>
        </div>
    </nav>

    <!-- Profile Content -->
    <div class="profile-container">
        <div class="text-center mb-5">
        <div class="position-relative d-inline-block mb-3">
        <img src="display_image.php?user_id=<?php echo $user_id; ?>" alt="Profile Picture" class="profile-pic shadow">
            
        </div>
        <h3 class="fw-bold mt-3 mb-1"><?php echo htmlspecialchars($profile['first_name'] . ' ' . $profile['last_name']); ?></h3>
        <p class="text-muted mb-2"><i class="fas fa-phone-alt me-2"></i><?php echo htmlspecialchars($profile['phone_no']); ?></p>
        <a href="edit_customer_info.php" class="btn btn-outline-primary mt-2">
            <i class="fas fa-edit me-1"></i> Edit Profile
        </a>
    </div>


        <hr>

        <div class="row">
            <div class="col-md-6 mb-3">
                <p class="info-label">Full Name:</p>
                <p class="info-value">
                    <?php echo htmlspecialchars($profile['first_name'] . ' ' . $profile['middle_initial'] . ' ' . $profile['last_name'] . ' ' . $profile['suffix']); ?>
                </p>
            </div>

            <div class="col-md-6 mb-3">
                <p class="info-label">Birthday:</p>
                <p class="info-value"><?php echo htmlspecialchars($profile['birthday']); ?></p>
            </div>

            <div class="col-md-6 mb-3">
                <p class="info-label">Phone Number:</p>
                <p class="info-value"><?php echo htmlspecialchars($profile['phone_no']); ?></p>
            </div>

            <div class="col-md-6 mb-3">
                <p class="info-label">Region:</p>
                <p class="info-value"><?php echo htmlspecialchars($profile['region']); ?></p>
            </div>

            <div class="col-md-6 mb-3">
                <p class="info-label">Province:</p>
                <p class="info-value"><?php echo htmlspecialchars($profile['province']); ?></p>
            </div>

            <div class="col-md-6 mb-3">
                <p class="info-label">City / Municipality:</p>
                <p class="info-value"><?php echo htmlspecialchars($profile['city']); ?></p>
            </div>

            <div class="col-md-6 mb-3">
                <p class="info-label">Barangay:</p>
                <p class="info-value"><?php echo htmlspecialchars($profile['barangay']); ?></p>
            </div>

            <div class="col-md-6 mb-3">
                <p class="info-label">Street:</p>
                <p class="info-value"><?php echo htmlspecialchars($profile['street']); ?></p>
            </div>

            <div class="col-md-6 mb-3">
                <p class="info-label">House Number:</p>
                <p class="info-value"><?php echo htmlspecialchars($profile['house_number']); ?></p>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="customer_dashboard.php" class="btn back-btn text-white px-4"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
    </div>

    </body>
    </html>
