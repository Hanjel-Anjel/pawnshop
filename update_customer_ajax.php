<?php
include('config.php');
session_start();

// Set content type to JSON
header('Content-Type: application/json');

// Check if ID is provided
if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'No customer ID provided']);
    exit;
}

$id = $_GET['id'];
// Sanitize input
$id = mysqli_real_escape_string($conn, $id);

// Check if form data is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Extract and sanitize form data
    $lname = mysqli_real_escape_string($conn, $_POST['last_name']);
    $fname = mysqli_real_escape_string($conn, $_POST['first_name']);
    $middle_initial = mysqli_real_escape_string($conn, $_POST['middle_initial']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $birthday = mysqli_real_escape_string($conn, $_POST['birthday']);
    $email = mysqli_real_escape_string($conn, $_POST['Email']);
    $phone = mysqli_real_escape_string($conn, $_POST['Phone']);
    $address = mysqli_real_escape_string($conn, $_POST['Address']);

    // Image upload handling
    $valid_id_image = null;
    if (isset($_FILES['valid_id_image']) && $_FILES['valid_id_image']['error'] === UPLOAD_ERR_OK) {
        $image_tmp_name = $_FILES['valid_id_image']['tmp_name'];
        $valid_id_image = file_get_contents($image_tmp_name);

        // Update the image in the database
        $sql = "UPDATE custumer_info SET 
                last_name = ?, 
                first_name = ?, 
                middle_initial = ?,
                gender = ?,
                birthday = ?,
                email = ?, 
                phone_no = ?,
                address = ?,
                valid_id_image = ? 
                WHERE customer_id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssss", $lname, $fname, $middle_initial, $gender, $birthday, $email, $phone, $address, $valid_id_image, $id);
    } else {
        // Update other fields without changing the image
        $sql = "UPDATE custumer_info SET 
                last_name = ?, 
                first_name = ?, 
                middle_initial = ?,
                gender = ?,
                birthday = ?,
                email = ?, 
                phone_no = ?,
                address = ?
                WHERE customer_id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssss", $lname, $fname, $middle_initial, $gender, $birthday, $email, $phone, $address, $id);
    }

    // Execute the query
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Customer updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>