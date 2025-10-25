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

        $pawn_date = mysqli_real_escape_string($conn, $_POST['pawn_date']);
        $due_date = mysqli_real_escape_string($conn, $_POST['due_date']); 
        $expiry_date = mysqli_real_escape_string($conn, $_POST['expiry_date']);

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
            $transaction_date = date('Y-m-d');
            $insert_transaction_query = "
                INSERT INTO transactions (customer_id, item_id, transaction_date, amount, payment_method, handled_by, 
                loan_amount, interest_amount, total_amount) 
                VALUES ('$customer_id', LAST_INSERT_ID(), '$transaction_date', '$total_balance', 'Cash', '$handled_by',
                '$loan_amount', '$compound_interest', '$total_balance')";

            if (mysqli_query($conn, $insert_transaction_query)) {
                // Call the function to update customer status
                updateCustomerStatus($conn);

                // Redirect after successful operation
                header('Location: ' . ($_SESSION['user_type'] == 'admin' ? 'item_table.php' : 'inventory_employee.php'));
                exit();
            } else {
                $_SESSION['error_message'] = "Error recording transaction: " . mysqli_error($conn);
                header('Location: ' . ($_SESSION['user_type'] == 'admin' ? 'item_table.php' : 'inventory_employee.php'));
                exit();
            }
        } else {
            $_SESSION['error_message'] = "Error adding item: " . mysqli_error($conn);
            header('Location: ' . ($_SESSION['user_type'] == 'admin' ? 'item_table.php' : 'inventory_employee.php'));
            exit();
        }
    } else {
        $_SESSION['error_message'] = "Invalid category selected.";
        header('Location: ' . ($_SESSION['user_type'] == 'admin' ? 'item_table.php' : 'inventory_employee.php'));
        exit();
    }
}
?>