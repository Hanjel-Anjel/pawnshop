<?php
@include 'config.php';

if (!isset($_GET['user_id'])) {
    http_response_code(400);
    exit('Missing user ID');
}

$user_id = intval($_GET['user_id']);

$stmt = $conn->prepare("SELECT valid_id_image FROM custumer_info WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($image_data);
    $stmt->fetch();

    // Set appropriate headers
    header("Content-Type: image/jpeg"); // Change to image/png if needed
    echo $image_data;
} else {
    // Fallback image if not found
    header("Content-Type: image/png");
    readfile("assets/default_profile.png");
}

$stmt->close();
$conn->close();
?>