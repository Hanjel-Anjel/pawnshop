<?php
include('config.php');

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if detail_id is set and is a valid integer
    $detail_id = isset($_POST['detail_id']) ? intval($_POST['detail_id']) : 0;

    // Log the received detail_id for debugging
    error_log("Received detail_id: $detail_id");

    if ($detail_id > 0) {
        $sql = "SELECT specifications FROM item_details WHERE detail_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $detail_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            echo json_encode($row['specifications']);
        } else {
            echo json_encode('');
        }
    } else {
        echo json_encode('Invalid detail ID');
    }
} else {
    echo json_encode('Invalid request');
}
?>
