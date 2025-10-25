<?php
@include 'config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('location:login_form.php');
    exit();
}

$handled_by = $_SESSION['user_id']; // Automatically set the handler to the logged-in user ID

if (isset($_POST['add_item'])) { 

    $customer_id = mysqli_real_escape_string($conn, $_POST['customer_id']); // Get the selected customer ID

    // Capture item data
    $item_name = mysqli_real_escape_string($conn, $_POST['item_name']);
    $item_value = mysqli_real_escape_string($conn, $_POST['item_value']);
    $loan_amount = mysqli_real_escape_string($conn, $_POST['loan_amount']);
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']); // Capture the category ID
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    // Get months_to_maturity and months_to_expiry from item_categories table
    $sql_category = "SELECT months_to_maturity, months_to_expiry FROM item_categories WHERE category_id = '$category_id'";
    $result_category = mysqli_query($conn, $sql_category);
    $row_category = mysqli_fetch_assoc($result_category);
    $months_to_maturity = $row_category['months_to_maturity'];
    $months_to_expiry = $row_category['months_to_expiry'];

    // Calculate due date and expiry date
    $pawn_date = date('Y-m-d'); // Set pawn date to today
    $due_date = date('Y-m-d', strtotime("+$months_to_maturity months", strtotime($pawn_date)));
    $expiry_date = date('Y-m-d', strtotime("+$months_to_expiry months", strtotime($pawn_date)));

    // Calculate interest (using due_date, not expiry_date)
    $interest_rate = round(0.03, 2); // 3% monthly interest rate
    $pawn_date_obj = new DateTime($pawn_date); 
    $due_date_obj = new DateTime($due_date); 
    $diff = $pawn_date_obj->diff($due_date_obj);
    $months_diff = ($diff->y * 12) + $diff->m + ($diff->d / 30); // Calculate difference in months

    $principal = $loan_amount;
    $compound_interest = $principal * pow((1 + $interest_rate), $months_diff) - $principal; 
    $total_balance = $principal + $compound_interest;

    // Insert new item into the database
    $insert_item_query = "INSERT INTO items (customer_id, item_name, category_id, item_value, loan_amount, item_status, pawn_date, due_date, expiry_date, interest_rate, total_balance) 
                          VALUES ('$customer_id', '$item_name', '$category_id', '$item_value', '$loan_amount', '$status', '$pawn_date', '$due_date', '$expiry_date', '$compound_interest', '$total_balance')";

    if (mysqli_query($conn, $insert_item_query)) {
        // Automatically record the transaction
        $transaction_date = date('Y-m-d');
        $insert_transaction_query = "INSERT INTO transactions (customer_id, item_id, transaction_date, amount, payment_method, handled_by) VALUES ('$customer_id', LAST_INSERT_ID(), '$transaction_date', '$loan_amount', 'Cash', '$handled_by')";

        if (mysqli_query($conn, $insert_transaction_query)) {
            $success_message = "Item and transaction recorded successfully for the selected customer.";
            header('location: home_employee.php'); 
        } else {
            $error_message = "Error adding transaction: " . mysqli_error($conn);
        }
    } else {
        $error_message = "Error adding item: " . mysqli_error($conn);
    }

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Item for Existing Customer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Add New Item for Existing Customer</h2>

        <?php if (isset($success_message)) {
            echo "<div class='alert alert-success'>$success_message</div>";
        } ?>
        <?php if (isset($error_message)) {
            echo "<div class='alert alert-danger'>$error_message</div>";
        } ?>

        <div class="card">
            <div class="card-header">Select Customer and Add Item</div>
            <div class="card-body">
                <form action="" method="post">
                    <h5>Select Customer</h5>
                    <div class="mb-3">
                        <label for="customer_id" class="form-label">Customer:</label>
                        <select name="customer_id" id="customer_id" class="form-control" required>
                            <option value="">Select a customer</option> 
                            <?php
                            $select_customers_query = "SELECT customer_id, first_name, last_name FROM custumer_info";
                            $customers_result = mysqli_query($conn, $select_customers_query);

                            while ($customer = mysqli_fetch_assoc($customers_result)) {
                                echo "<option value='" . $customer['customer_id'] . "'>" . 
                                      htmlspecialchars($customer['first_name']) . " " . 
                                      htmlspecialchars($customer['last_name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <h5 class="mt-4">Item Information</h5>
                    <div class="mb-3">
                        <label for="item_name" class="form-label">Item Name</label>
                        <input type="text" name="item_name" id="item_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="item_value" class="form-label">Item Value</label>
                        <input type="number" step="0.01" name="item_value" id="item_value" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="loan_amount" class="form-label">Loan Amount</label>
                        <input type="number" step="0.01" name="loan_amount" id="loan_amount" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category:</label>
                        <select name="category_id" id="category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            <?php
                            $select_categories_query = "SELECT * FROM item_categories";
                            $categories_result = mysqli_query($conn, $select_categories_query);

                            while ($category = mysqli_fetch_assoc($categories_result)) {
                                echo "<option value='" . $category['category_id'] . "' data-maturity='" . $category['months_to_maturity'] . "' data-expiry='" . $category['months_to_expiry'] . "'>" . 
                                      htmlspecialchars($category['category_name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <input type="text" name="status" id="status" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="pawn_date" class="form-label">Pawn Date</label>
                        <input type="date" name="pawn_date" id="pawn_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="due_date" class="form-label">Due Date</label>
                        <input type="date" name="due_date" id="due_date" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="expiry_date" class="form-label">Expiry Date</label>
                        <input type="date" name="expiry_date" id="expiry_date" class="form-control" readonly>
                    </div>

                    <button type="submit" name="add_item" class="btn btn-primary">Add Item</button> 
                    <a href="home_employee.php" class="btn btn-danger">Cancel</a>
                </form>
            </div>
        </div>
    </div>

    <script>
        const categorySelect = document.getElementById('category_id');
        const dueDateInput = document.getElementById('due_date');
        const expiryDateInput = document.getElementById('expiry_date');

        categorySelect.addEventListener('change', () => {
            const selectedOption = categorySelect.options[categorySelect.selectedIndex];
            const monthsToMaturity = selectedOption.dataset.maturity;
            const monthsToExpiry = selectedOption.dataset.expiry;

            const pawnDate = new Date(document.getElementById('pawn_date').value);

            if (!isNaN(monthsToMaturity) && !isNaN(monthsToExpiry)) {
                const dueDate = new Date(pawnDate);
                dueDate.setMonth(dueDate.getMonth() + parseInt(monthsToMaturity));

                const expiryDate = new Date(pawnDate);
                expiryDate.setMonth(expiryDate.getMonth() + parseInt(monthsToExpiry));

                dueDateInput.value = dueDate.toISOString().split('T')[0];
                expiryDateInput.value = expiryDate.toISOString().split('T')[0];
            } else {
                dueDateInput.value = ""; 
                expiryDateInput.value = ""; 
            }
        });

        // Add event listeners to the input fields that affect the calculation
        loanAmountInput.addEventListener('input', calculateInterest);
        pawnDateInput.addEventListener('change', calculateInterest); 
        dueDateInput.addEventListener('change', calculateInterest); 

        function calculateInterest() {
            const loanAmount = parseFloat(loanAmountInput.value);
            const pawnDate = new Date(pawnDateInput.value);
            const dueDate = new Date(dueDateInput.value);

            if (!isNaN(loanAmount) && pawnDateInput.value && dueDateInput.value) { 
                const diffInMs = dueDate.getTime() - pawnDate.getTime(); 
                const monthsDiff = (diffInMs / (1000 * 60 * 60 * 24 * 30)).toFixed(2); 

                const interestRate = 0.03;
                const interest = loanAmount * interestRate * monthsDiff;
                const totalBalance = loanAmount + interest;

                interestInput.value = interest.toFixed(2);
                totalBalanceInput.value = totalBalance.toFixed(2);
            } else {
                interestInput.value = "";
                totalBalanceInput.value = "";
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>