<?php
include('config.php'); // Include database connection
session_start();
// Check if user is logged in
if (!isset($_SESSION['admin_name']) && !isset($_SESSION['user_name'])) {
    header('location:login_form.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $brand = $_POST['brand'];
    $model = $_POST['model'];
    $specifications = $_POST['specifications'];
    $appraisal_price = $_POST['appraisal_price'];

    $sql = "INSERT INTO item_details (brand, model, specifications, appraisal_price) 
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssd", $brand, $model, $specifications, $appraisal_price);

    if ($stmt->execute()) {
        if ($_SESSION['user_type'] == 'admin') {
            header('Location: item_details.php');
        } else {
            header('Location: item_details.php');
        }
        exit();
    } else {
        echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Item Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Upload Item Details</h2>
    <form action="" method="post" class="row g-3">
        <div class="col-md-6">
            <label for="brand" class="form-label">Brand</label>
            <input type="text" id="brand" name="brand" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label for="model" class="form-label">Model</label>
            <input type="text" id="model" name="model" class="form-control" required>
        </div>
        <div class="col-12">
            <label for="specifications" class="form-label">Specifications</label>
            <textarea id="specifications" name="specifications" class="form-control" rows="3"></textarea>
        </div>
        <div class="col-md-6">
            <label for="appraisal_price" class="form-label">Appraisal Price</label>
            <input type="number" id="appraisal_price" name="appraisal_price" class="form-control" step="0.01" required>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-success">Upload</button>
            <a href="item_details.php" class="btn btn-danger">Cancel</a>
        </div>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
