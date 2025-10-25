<?php
include('config.php');

session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_name']) && !isset($_SESSION['user_name'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

$response = ['success' => false, 'message' => ''];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $last_name = $_POST['last_name'];
    $first_name = $_POST['first_name'];
    $middle_initial = $_POST['middle_initial'];
    $gender = $_POST['gender'];
    $birthday = $_POST['birthday'];
    $email = $_POST['email'];
    $phone_no = $_POST['phone_no'];
    $address = $_POST['address'];

    // Validate email to ensure it's a Gmail address
    if (!preg_match('/^[a-zA-Z0-9._%+-]+@gmail\\.com$/', $email)) {
        $response['message'] = "Only Gmail addresses are allowed.";
    } elseif (!preg_match('/^(09\\d{9}|\\+639\\d{9})$/', $phone_no)) {
        // Validate Philippine mobile number
        $response['message'] = "Please enter a valid Philippine mobile number (e.g., 09171234567 or +639171234567).";
    } else {
        // Image upload handling
        $valid_id_image = null;
        if (isset($_FILES['valid_id_image']) && $_FILES['valid_id_image']['error'] === UPLOAD_ERR_OK) {
            $image_tmp_name = $_FILES['valid_id_image']['tmp_name'];
            $valid_id_image = file_get_contents($image_tmp_name);
        }

        // Basic input validation
        if (empty($last_name) || empty($first_name) || empty($email) || empty($phone_no) || empty($address) || empty($gender) || empty($birthday)) {
            $response['message'] = "All fields are required.";
        } else {
            // Prepare and execute the SQL query
            $sql = "INSERT INTO custumer_info (last_name, first_name, middle_initial, gender, birthday, email, phone_no, address, valid_id_image, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active')";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssss", $last_name, $first_name, $middle_initial, $gender, $birthday, $email, $phone_no, $address, $valid_id_image);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = "Customer added successfully!";
            } else {
                $response['message'] = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Return response as JSON
header('Content-Type: application/json');
echo json_encode($response);
?>