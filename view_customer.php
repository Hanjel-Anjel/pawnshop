<?php
include('d:\xampp\htdocs\pawnshop\config.php');

if (isset($_GET['id'])) {
    $customer_id = $_GET['id'];
    $query = "SELECT * FROM custumer_info WHERE customer_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $customer = $result->fetch_assoc();

    if ($customer) {
        echo "<p><strong>Last Name:</strong> " . htmlspecialchars($customer['last_name']) . "</p>";
        echo "<p><strong>First Name:</strong> " . htmlspecialchars($customer['first_name']) . "</p>";
        echo "<p><strong>Email:</strong> " . htmlspecialchars($customer['email']) . "</p>";
        echo "<p><strong>Phone:</strong> " . htmlspecialchars($customer['phone_no']) . "</p>";

        // ✅ Combine address parts into one formatted string
        $full_address = $customer['house_number'] . ' ' .
                        $customer['street'] . ', Brgy. ' .
                        $customer['barangay'] . ', ' .
                        $customer['city'] . ', ' .
                        $customer['province'] . ', ' .
                        $customer['region'];

        echo "<p><strong>Address:</strong> " . htmlspecialchars($full_address) . "</p>";

        echo "<p><strong>Gender:</strong> " . htmlspecialchars($customer['gender']) . "</p>";
        echo "<p><strong>Birthday:</strong> " . htmlspecialchars($customer['birthday']) . "</p>";

        if (!empty($customer['valid_id_image'])) {
            echo '<p><strong>Valid ID:</strong></p>';
            echo '<img src="data:image/jpeg;base64,' . base64_encode($customer['valid_id_image']) . '" class="img-fluid" alt="Valid ID">';
        }
    } else {
        echo "<p>No customer found.</p>";
    }

    $stmt->close();
}
?>
