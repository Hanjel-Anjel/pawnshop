<?php
include('c:\xampp\htdocs\pawnshop\config.php'); // Include the database connection

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get values from the form
    $item_name = $_POST['item_name'];
    $item_category = $_POST['item_category'];
    $item_value = $_POST['item_value'];
    $item_status = $_POST['item_status'];
    $loan_amount = $_POST['loan_amount'];
    $pawn_date = $_POST['pawn_date'];
    $due_date = $_POST['due_date'];
    $customer_id = $_POST['customer_id']; // Assuming you're associating an item with a customer

    // Prepare the SQL insert query
    $sql = "INSERT INTO items (item_name, item_category, item_value, item_status, loan_amount, pawn_date, due_date, customer_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdsdssi", $item_name, $item_category, $item_value, $item_status, $loan_amount, $pawn_date, $due_date, $customer_id);

    // Execute the query
    if ($stmt->execute()) {
        
        header("Location: item_table.php");
        exit();
    } else {
        echo "<div class='alert alert-danger'>Error adding item: " . $conn->error . "</div>";
    }
    
}

    // After inserting a pawned item
$sql = "INSERT INTO items (customer_id, item_name, item_status) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $customer_id, $item_name, $item_status);
$stmt->execute();

// Update customer status
updateCustomerStatus($conn);


$conn->close(); // Close the database connection
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Item</title>
    <!-- Include Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        input[type=text] {
            text-transform: capitalize;
        }
    </style>

</head>

<body>

    <div class="container mt-5">
        <h2>Add New Item</h2>

        <form action="add_item.php" method="post" class="row g-3">
            <!-- Item Name -->
            <div class="col-md-6">
                <label for="item_name" class="form-label">Item Name</label>
                <input type="text" id="item_name" name="item_name" class="form-control" required>
            </div>

            <!-- Item Category -->
            <div class="col-md-6">
                <label for="item_category" class="form-label">Category</label>
                <input type="text" id="item_category" name="item_category" class="form-control">
            </div>

            <!-- Item Value -->
            <div class="col-md-6">
                <label for="item_value" class="form-label">Item Value</label>
                <input type="number" id="item_value" name="item_value" class="form-control" step="0.01" required>
            </div>

            <!-- Item Status -->
            <div class="col-md-6">
                <label for="item_status" class="form-label">Status</label>
                <input type="text" id="item_status" name="item_status" class="form-control">
            </div>

            <!-- Loan Amount -->
            <div class="col-md-6">
                <label for="loan_amount" class="form-label">Loan Amount</label>
                <input type="number" id="loan_amount" name="loan_amount" class="form-control" step="0.01" required>
            </div>

            <!-- Pawn Date -->
            <div class="col-md-6">
                <label for="pawn_date" class="form-label">Pawn Date</label>
                <input type="date" id="pawn_date" name="pawn_date" class="form-control" required>
            </div>

            <!-- Due Date -->
            <div class="col-md-6">
                <label for="due_date" class="form-label">Due Date</label>
                <input type="date" id="due_date" name="due_date" class="form-control" required>
            </div>

            <!-- Customer ID (Foreign Key) -->
            <div class="col-md-6">
                <?php 
                    include('c:\xampp\htdocs\pawnshop\config.php');
                ?>
                <label for="customer_id" class="form-label">Customer</label>
                <select id="customer_id" name="customer_id" class="form-select" required>
                    <option value="">Select Customer</option>
                    <?php
                    $customer_sql = "SELECT customer_id, last_name, first_name FROM custumer_info";
                    $customer_result = $conn->query($customer_sql);
                    if ($customer_result->num_rows > 0) {
                        while ($customer = $customer_result->fetch_assoc()) {
                            echo "<option value='" . $customer['customer_id'] . "'>" . htmlspecialchars($customer['last_name']) . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>

            <!-- Submit Button -->
            <div class="col-12">
                <button type="submit" class="btn btn-success">Add Item</button>
                <a href="item_table.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <!-- Include Bootstrap JS (optional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>