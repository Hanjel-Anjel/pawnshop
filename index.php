<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ArMaTech Gadget Pawnshop</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

  <style>
    body, html {
      height: 100%;
      margin: 0;
      font-family: 'Poppins', sans-serif;
      color: white;
      overflow: hidden;
    }

    /* === HERO SECTION === */
    .hero {
      position: relative;
      background: url('./image/bg_pic.jpg') no-repeat center center/cover;
      height: 100%;
      display: flex;
      justify-content: center;
      align-items: center;
      text-align: center;
    }

    /* Dark overlay for readability */
    .hero::before {
      content: "";
      position: absolute;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0, 0, 0, 0.6);
      z-index: 0;
    }

    /* Glassmorphic container */
    .hero-content {
      position: relative;
      z-index: 1;
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 20px;
      padding: 40px 60px;
      backdrop-filter: blur(8px);
      animation: fadeIn 1s ease-in-out;
    }

    .hero h1 {
      font-size: 3.5rem;
      font-weight: 700;
      color: #ffc107;
      text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.7);
    }

    .hero p {
      font-size: 1.2rem;
      margin-top: 15px;
      color: #eee;
    }

    .btn-custom {
      display: inline-block;
      margin-top: 30px;
      padding: 12px 35px;
      border-radius: 30px;
      font-weight: 600;
      text-transform: uppercase;
      color: #fff;
      background: linear-gradient(135deg, #ffc107, #e0a800);
      border: none;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(255, 193, 7, 0.4);
    }

    .btn-custom:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(255, 193, 7, 0.5);
      color: #fff;
    }

    /* Admin Login button */
    .admin-btn {
      position: absolute;
      top: 20px;
      right: 20px;
      z-index: 2;
      background: rgba(255, 255, 255, 0.2);
      border: 1px solid rgba(255, 255, 255, 0.3);
      color: #ffc107;
      font-weight: 600;
      padding: 10px 25px;
      border-radius: 30px;
      transition: all 0.3s ease;
    }

    .admin-btn:hover {
      background-color: #ffc107;
      color: #000;
    }

    /* Animation */
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* Responsive */
    @media (max-width: 768px) {
      .hero h1 {
        font-size: 2.5rem;
      }
      .hero-content {
        padding: 30px 40px;
      }
    }
  </style>
</head>
<body>

  <!-- Hero Section -->
  <div class="hero">
    <a href="login_form.php" class="admin-btn"><i class="fas fa-user-shield me-2"></i>Admin Login</a>
    <div class="hero-content">
      <h1><i class="fas fa-mobile-alt me-2"></i>ArMaTech Pawnshop</h1>
      <p>Your trusted partner for gadget pawning and tech trade-ins.</p>
      <a href="customer_login_page.php" class="btn-custom text-decoration-none">Customer Login</a>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
