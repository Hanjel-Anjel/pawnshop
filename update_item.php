<?php
include('config.php'); // Include the database connection
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_name']) && !isset($_SESSION['user_name'])) {
    echo json_encode(['success' => false, 'error' => 'Not authorized']);
    exit();
}

// Check if it's an AJAX request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the item ID from the URL
    $item_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($item_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid item ID']);
        exit();
    }

    // Fetch updated values from the form
    $item_brand = $_POST['item_brand'];
    $item_model = $_POST['item_model'];
    $item_specifications = $_POST['item_specifications'];
    $item_condition = $_POST['item_condition'];
    $item_value = $_POST['item_value'];
    $item_status = $_POST['item_status'];

    // Update the item details in the database
    $sql = "UPDATE items SET 
                brand = ?, 
                model = ?, 
                specifications = ?, 
                `condition` = ?, 
                item_value = ?, 
                item_status = ? 
            WHERE item_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssisi",
        $item_brand,
        $item_model,
        $item_specifications,
        $item_condition,
        $item_value,
        $item_status,
        $item_id
    );

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>