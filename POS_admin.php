<?php
@include 'config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('location:login_form.php');
    exit();
}

$handled_by = $_SESSION['user_id']; // Automatically set handler to logged-in user

// Function to update customer status
function updateCustomerStatus($conn) {
    $sql = "
        SELECT c.customer_id, 
               COUNT(i.item_id) AS active_items
        FROM custumer_info c
        LEFT JOIN items i 
        ON c.customer_id = i.customer_id 
           AND i.item_status NOT IN ('Redeemed', 'Forfeited')
        GROUP BY c.customer_id";
        
    $result = $conn->query($sql);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $status = $row['active_items'] > 0 ? 'Active' : 'Inactive';

            // Update the customer's status
            $update_sql = "UPDATE custumer_info SET status = ? WHERE customer_id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("si", $status, $row['customer_id']);
            $stmt->execute();
        }
    } else {
        echo "Error: " . $conn->error;
    }
}

if (isset($_POST['add_item'])) {
    $customer_id = mysqli_real_escape_string($conn, $_POST['customer_id']);
    $brand = mysqli_real_escape_string($conn, $_POST['brand']);
    $model = mysqli_real_escape_string($conn, $_POST['model']);
    $condition = mysqli_real_escape_string($conn, $_POST['condition']);
    $specifications = mysqli_real_escape_string($conn, $_POST['specifications']);
    $item_value = mysqli_real_escape_string($conn, $_POST['item_value']);
    $loan_amount = mysqli_real_escape_string($conn, $_POST['loan_amount']);
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    // Get category details
    $category_query = "SELECT * FROM item_categories WHERE category_id = '$category_id'";
    $result_category = mysqli_query($conn, $category_query);

    // Validate category result
    if ($result_category && mysqli_num_rows($result_category) > 0) {
        $row_category = mysqli_fetch_assoc($result_category);

        $pawn_date = date('Y-m-d');
        $due_date = $_POST['due_date']; // From client-side calculation
        $expiry_date = $_POST['expiry_date']; // From client-side calculation

        // Calculate interest
        $interest_rate = 0.03; // 3% interest
        $principal = $loan_amount;
        $months_diff = (strtotime($due_date) - strtotime($pawn_date)) / (30 * 24 * 60 * 60); // Calculate months difference
        $compound_interest = $principal * pow((1 + $interest_rate), $months_diff) - $principal;
        $total_balance = $principal + $compound_interest;

        // Insert new item
        $insert_item_query = "
            INSERT INTO items (customer_id, brand, model, specifications, category_id, item_value, loan_amount, item_status, pawn_date, due_date, expiry_date, interest_rate, total_balance) 
            VALUES ('$customer_id', '$brand', '$model', '$specifications', '$category_id', '$item_value', '$loan_amount', '$status', '$pawn_date', '$due_date', '$expiry_date', '$compound_interest', '$total_balance')";

            if (mysqli_query($conn, $insert_item_query)) {
            // Record date and time (not just date)
            $transaction_date = date('Y-m-d H:i:s');

            $insert_transaction_query = "
                INSERT INTO transactions (customer_id, item_id, transaction_date, amount, payment_method, handled_by) 
                VALUES ('$customer_id', LAST_INSERT_ID(), '$transaction_date', '$loan_amount', 'Cash', '$handled_by')";


            if (mysqli_query($conn, $insert_transaction_query)) {
                // Call the function to update customer status
                updateCustomerStatus($conn);

                // Redirect after successful operation
                header('Location: ' . ($_SESSION['user_type'] == 'admin' ? 'item_table.php' : 'inventory_employee.php'));
                exit();
            } else {
                $error_message = "Error recording transaction: " . mysqli_error($conn);
            }
        } else {
            $error_message = "Error adding item: " . mysqli_error($conn);
        }
    } else {
        $error_message = "Invalid category selected.";
    }
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Item</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        input[type=text] {
            text-transform: capitalize;
        }
    </style>

</head>

<body>
<div class="container mt-5">
    <h2>Add New Item for Existing Customer</h2>

    <?php if (isset($error_message)) { ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php } ?>

    <form action="" method="post" class="mt-4">
        <div class="mb-3">
            <label for="customer_search" class="form-label">Search Customer:</label>
            <input type="text" id="customer_search" class="form-control" placeholder="Type customer name to search...">
        </div>
        
        <div class="mb-3">
            <label for="customer_id" class="form-label">Customer:</label>
            <select name="customer_id" id="customer_id" class="form-control" required>
                <option value="">Select a customer</option>
                <?php
                $select_customers_query = "SELECT customer_id, first_name, last_name FROM custumer_info";
                $customers_result = mysqli_query($conn, $select_customers_query);

                while ($customer = mysqli_fetch_assoc($customers_result)) {
                    echo "<option value='" . $customer['customer_id'] . "'>" . htmlspecialchars($customer['first_name']) . " " . htmlspecialchars($customer['last_name']) . "</option>";
                }
                ?>
            </select>
        </div>

            <div class="mb-3">
                <label for="brand" class="form-label">Brand:</label>
                <input type="text" name="brand" id="brand" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="model" class="form-label">Model:</label>
                <input type="text" name="model" id="model" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="specifications" class="form-label">Specifications:</label>
                <textarea name="specifications" id="specifications" class="form-control" style="text-transform: capitalize;" required></textarea>
            </div>

            <div class="mb-3">
                <label for="condition" class="form-label">Condition:</label>
                <select name="condition" id="condition" class="form-control" required>
                    <option value="Used">Used</option>
                    <option value="Damaged">Damaged</option>
                    <option value="Brand New">Brand New</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="item_value" class="form-label">Item Value:</label>
                <input type="number" name="item_value" id="item_value" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="loan_amount" class="form-label">Loan Amount:</label>
                <input type="number" name="loan_amount" id="loan_amount" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="category_id" class="form-label">Category:</label>
                <select name="category_id" id="category_id" class="form-control" required>
                    <option value="">Select a category</option>
                    <?php
                    $select_categories_query = "SELECT * FROM item_categories";
                    $categories_result = mysqli_query($conn, $select_categories_query);

                    while ($category = mysqli_fetch_assoc($categories_result)) {
                        echo "<option value='" . $category['category_id'] . "'>" . htmlspecialchars($category['category_name']) . "</option>";
                    }
                    ?>
                </select>
            </div>


            <div class="row">
                <!-- Pawn Date -->
                <div class="col-md-4">
                    <label for="pawn_date" class="form-label">Pawn Date</label>
                    <input type="date" id="pawn_date" name="pawn_date" class="form-control" required>
                </div>

                <!-- Due Date -->
                <div class="col-md-4">
                    <label for="due_date" class="form-label">Due Date</label>
                    <input type="date" id="due_date" name="due_date" class="form-control" readonly>
                </div>

                <!-- Expiry Date -->
                <div class="col-md-4">
                    <label for="expiry_date" class="form-label">Expiry Date</label>
                    <input type="date" id="expiry_date" name="expiry_date" class="form-control" readonly>
                </div>
            </div>

            <div class="row mt-3">
                <!-- Plan Dropdown -->
                <div class="col-md-4">
                    <label for="plan" class="form-label">Plan</label>
                    <select id="plan" name="plan" class="form-control" required>
                        <option value="" disabled selected>Select Plan</option>
                        <option value="1-month">1 Month</option>
                        <option value="3-months">3 Months</option>
                        <option value="6-months">6 Months</option>
                        <option value="1-year">1 Year</option>
                    </select>
                </div>
            </div>


            <div class="mb-3">
                <label for="status" class="form-label">Item Status:</label>
                <select name="status" id="status" class="form-control" required>
                    <option value="Pawned">Pawned</option>
                </select>
            </div>

            <button type="submit" name="add_item" class="btn btn-primary">Add Item</button>
            <a href="<?php echo $_SESSION['user_type'] == 'admin' ? 'item_table.php' : 'inventory_employee.php'; ?>" class="btn btn-danger">Cancel</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const pawnDateInput = document.getElementById("pawn_date");
        const dueDateInput = document.getElementById("due_date");
        const expiryDateInput = document.getElementById("expiry_date");

        // Predefined due and expiry duration in months
        const maturityMonths = {
            "1-month": 1,
            "3-months": 3,
            "6-months": 6,
            "1-year": 12
        };
        const expiryDaysAfterDue = 7; // Expiry date is 7 days after the due date

        // Update Due Date and Expiry Date based on the pawn date and selected plan
        function updateDates() {
            const pawnDateValue = pawnDateInput.value;
            const selectedPlan = document.getElementById("plan").value;

            if (pawnDateValue && selectedPlan) {
                const pawnDate = new Date(pawnDateValue);

                // Calculate due date
                const dueDate = new Date(pawnDate);
                dueDate.setMonth(dueDate.getMonth() + maturityMonths[selectedPlan]);

                // Calculate expiry date
                const expiryDate = new Date(dueDate);
                expiryDate.setDate(expiryDate.getDate() + expiryDaysAfterDue);

                // Update input fields
                dueDateInput.value = dueDate.toISOString().split("T")[0];
                expiryDateInput.value = expiryDate.toISOString().split("T")[0];
            } else {
                dueDateInput.value = "";
                expiryDateInput.value = "";
            }
        }

        // Event listeners for pawn date and plan selection
        pawnDateInput.addEventListener("change", updateDates);
        document.getElementById("plan").addEventListener("change", updateDates);
    });

    document.addEventListener("DOMContentLoaded", function () {
        const searchInput = document.getElementById("customer_search");
        const customerSelect = document.getElementById("customer_id");

        searchInput.addEventListener("keyup", function () {
            const searchValue = searchInput.value.toLowerCase();

            for (let option of customerSelect.options) {
                if (option.text.toLowerCase().includes(searchValue) || option.value === "") {
                    option.style.display = ""; // Show matching options
                } else {
                    option.style.display = "none"; // Hide non-matching options
                }
            }
        });
    });
</script>