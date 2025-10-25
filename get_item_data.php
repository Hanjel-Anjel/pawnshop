<?php
// get_item_data.php - This file fetches item data for the edit modal
include('config.php');
session_start();

// Verify user is logged in
if (!isset($_SESSION['admin_name']) && !isset($_SESSION['user_name'])) {
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

// Get the item ID from the request
$item_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($item_id <= 0) {
    echo json_encode(['error' => 'Invalid item ID']);
    exit();
}

// Fetch item details
$sql = "SELECT 
            brand,
            model,
            specifications,
            `condition`,
            item_value,
            item_status
        FROM items
        WHERE item_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $item = $result->fetch_assoc();
    echo json_encode($item);
} else {
    echo json_encode(['error' => 'Item not found']);
}

$stmt->close();
$conn->close();
?>