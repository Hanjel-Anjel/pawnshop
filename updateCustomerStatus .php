<?php
// Include your database connection
include 'db_connection.php';

function updateCustomerStatus($conn) {
    $sql = "
        SELECT c.customer_id, 
               COUNT(i.item_id) AS active_items
        FROM customers c
        LEFT JOIN items i 
        ON c.customer_id = i.customer_id 
           AND i.item_status NOT IN ('Redeemed', 'Forfeited')
        GROUP BY c.customer_id";
        
    $result = $conn->query($sql);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $status = $row['active_items'] > 0 ? 'Active' : 'Inactive';

            $update_sql = "UPDATE customers SET status = ? WHERE customer_id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("si", $status, $row['customer_id']);
            $stmt->execute();
        }
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
