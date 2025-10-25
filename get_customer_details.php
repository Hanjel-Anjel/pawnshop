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
        echo "<div class='container'>";
        echo "<div class='row'>";
        echo "<div class='col-md-6'>";
        echo "<p><strong>Last Name:</strong> " . htmlspecialchars($customer['last_name']) . "</p>";
        echo "<p><strong>First Name:</strong> " . htmlspecialchars($customer['first_name']) . "</p>";
        echo "<p><strong>Email:</strong> " . htmlspecialchars($customer['email']) . "</p>";
        echo "<p><strong>Phone:</strong> " . htmlspecialchars($customer['phone_no']) . "</p>";
        echo "</div>";
        echo "<div class='col-md-6'>";
        echo "<p><strong>Address:</strong> " . htmlspecialchars($customer['address']) . "</p>";
        echo "<p><strong>Gender:</strong> " . htmlspecialchars($customer['gender']) . "</p>";
        echo "<p><strong>Birthday:</strong> " . htmlspecialchars($customer['birthday']) . "</p>";
        echo "<p><strong>Status:</strong> " . htmlspecialchars($customer['status']) . "</p>";
        echo "</div>";
        echo "</div>";

        if (!empty($customer['valid_id_image'])) {
            echo '<div class="row mt-3">';
            echo '<div class="col-12">';
            echo '<p><strong>Valid ID:</strong></p>';
            echo '<img src="data:image/jpeg;base64,' . base64_encode($customer['valid_id_image']) . '" class="img-fluid" alt="Valid ID">';
            echo '</div>';
            echo '</div>';
        }
        echo "</div>";
    } else {
        echo "<div class='alert alert-warning'>No customer found.</div>";
    }

    $stmt->close();
} else {
    echo "<div class='alert alert-danger'>Invalid request. Customer ID is required.</div>";
}
?>