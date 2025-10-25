<?php
include('config.php');
session_start();

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Fetch the existing customer data for pre-filling the form
    $sql = "SELECT * FROM custumer_info WHERE customer_id = '$id'";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        die("Error: " . mysqli_error($conn));
    } else {
        $row = mysqli_fetch_assoc($result);
    }
}

if (isset($_POST['update_customer'])) {
    $idnew = $_GET['id_new'];
    $lname = $_POST['last_name'];
    $fname = $_POST['first_name'];
    $middle_initial = $_POST['middle_initial'];
    $gender = $_POST['gender'];
    $birthday = $_POST['birthday'];
    $email = $_POST['Email'];
    $phone = $_POST['Phone'];
    $address = $_POST['Address'];

    // Image upload handling
    $valid_id_image = null;
    if (isset($_FILES['valid_id_image']) && $_FILES['valid_id_image']['error'] === UPLOAD_ERR_OK) {
        $image_tmp_name = $_FILES['valid_id_image']['tmp_name'];
        $valid_id_image = file_get_contents($image_tmp_name);

        // Update the image in the database
        $sql = "UPDATE custumer_info SET 
                last_name = '$lname', 
                first_name = '$fname', 
                middle_initial = '$middle_initial',
                gender = '$gender',
                birthday = '$birthday',
                email = '$email', 
                phone_no = '$phone',
                address = '$address',
                valid_id_image = ? 
                WHERE customer_id = '$idnew'";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $valid_id_image); 

    } else {
        // Update other fields without changing the image
        $sql = "UPDATE custumer_info SET 
                last_name = '$lname', 
                first_name = '$fname', 
                middle_initial = '$middle_initial',
                gender = '$gender',
                birthday = '$birthday',
                email = '$email', 
                phone_no = '$phone',
                address = '$address'
                WHERE customer_id = '$idnew'";

        $stmt = $conn->prepare($sql); 
    }

    if ($stmt->execute()) {
        header('location:customer_page.php');
    } else {
        echo "Error: " . $stmt->error; 
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Update Customer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            padding-top: 2%;
        }

        input[type=text]{
        text-transform: capitalize;
    }
    </style>
</head>

<body>
    <div class="container">
        <div class="card">
            <div class="card-body">
                <h1>Update Customer</h1>
                <form action="update_customer.php?id_new=<?php echo $id; ?>" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="Name">Last Name:</label>
                        <input type="text" id="Name" class="form-control" name="last_name" value="<?php echo $row['last_name'] ?>"><br>
                    </div>
                    <div class="form-group">
                        <label for="Name">First Name:</label>
                        <input type="text" id="Name" class="form-control" name="first_name" value="<?php echo $row['first_name'] ?>"><br>
                    </div>
                    <div class="form-group">
                        <label for="middle_initial">Middle Initial:</label>
                        <input type="text" id="middle_initial" class="form-control" name="middle_initial" maxlength="5" value="<?php echo $row['middle_initial']; ?>"><br>
                    </div>
                    <div class="form-group">
                        <label for="gender">Gender:</label>
                        <select id="gender" class="form-control" name="gender" required>
                            <option value="Male" <?php if ($row['gender'] == 'Male') echo 'selected'; ?>>Male</option>
                            <option value="Female" <?php if ($row['gender'] == 'Female') echo 'selected'; ?>>Female</option>
                        </select><br>
                    </div>
                    <div class="form-group">
                        <label for="birthday">Birthday:</label>
                        <input type="date" id="birthday" class="form-control" name="birthday" value="<?php echo $row['birthday']; ?>" required><br>
                    </div>
                    <div class="form-group">
                        <label for="Email">Email:</label>
                        <input type="text" id="Email" class="form-control" name="Email" value="<?php echo $row['email'] ?>"><br>
                    </div>
                    <div class="form-group">
                        <label for="Phone">Phone:</label>
                        <input type="text" id="Phone" class="form-control" name="Phone" value="<?php echo $row['phone_no'] ?>"><br>
                    </div>
                    <div class="form-group">
                        <label for="Address">Address:</label>
                        <input type="text" id="Address" class="form-control" name="Address" value="<?php echo $row['address'] ?>"><br>
                    </div>

                    <div class="form-group">
                        <label for="valid_id_image">Valid ID Image:</label>
                        <?php if ($row['valid_id_image']): ?>
                            <img src="data:image/jpeg;base64,<?php echo base64_encode($row['valid_id_image']); ?>" alt="Valid ID" width="200"><br>
                        <?php endif; ?>
                        <input type="file" id="valid_id_image" class="form-control-file" name="valid_id_image"><br>
                    </div>

                    <input type="submit" class="btn btn-success" name="update_customer" value="Update">
                    <?php
                    if (isset($_SESSION['user_type'])) {
                    
                        if ($_SESSION['user_type'] == 'admin') {
                            echo '<a class="btn btn-danger" href="Customer_page.php" role="button">Cancel</a>';
                        } elseif ($_SESSION['user_type'] == 'employee') {
                            echo '<a class="btn btn-danger" href="customer_employee.php" role="button">Cancel</a>';
                        }
                    } else {
                        echo "<p>No role type set in session.</p>"; // Debugging line
                    }
                    
                    ?>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>