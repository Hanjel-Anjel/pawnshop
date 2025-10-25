<?php
include('d:\xampp\htdocs\pawnshop\config.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM employee_details WHERE id = '$id'";
    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        echo json_encode(mysqli_fetch_assoc($result));
    } else {
        echo json_encode([]);
    }
}