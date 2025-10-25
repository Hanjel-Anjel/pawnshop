<?php
include('config.php'); // Database connection

// Check and update item statuses
$sql = "UPDATE items 
        SET item_status = 'Forfeited' 
        WHERE expiry_date < NOW() AND item_status = 'Pawned'";

if ($conn->query($sql) === TRUE) {
    // Fetch updated statuses to send to the frontend
    $fetchSql = "SELECT item_id, item_status FROM items";
    $result = $conn->query($fetchSql);

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }

    echo json_encode($items);
} else {
    echo json_encode(['error' => 'Failed to update statuses']);
}

$conn->close();
?>
