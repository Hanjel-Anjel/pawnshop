<?php
@include 'config.php';

// Check if the brand is provided via POST
if (isset($_POST['brand']) && !empty($_POST['brand'])) {
    $brand = mysqli_real_escape_string($conn, $_POST['brand']);

    // Fetch models for the given brand
    $sql = "SELECT model FROM item_details WHERE brand = '$brand'";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        $models = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $models[] = $row['model'];
        }
        echo json_encode($models); // Return models as a JSON array
    } else {
        // Handle query error
        echo json_encode(['error' => 'Database query failed']);
    }
} else {
    echo json_encode(['error' => 'No brand provided']);
}
?>