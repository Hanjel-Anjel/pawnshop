<?php
include('config.php');
session_start();

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Sanitize the input
    $id = mysqli_real_escape_string($conn, $id);

    // Fetch the existing customer data for pre-filling the form
    $sql = "SELECT * FROM custumer_info WHERE customer_id = '$id'";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        die("Error: " . mysqli_error($conn));
    } else {
        $row = mysqli_fetch_assoc($result);
    }
} else {
    echo "No customer ID provided";
    exit;
}
?>

<form id="customerEditForm" method="post" enctype="multipart/form-data">
    <div class="form-group mb-3">
        <label for="last_name">Last Name:</label>
        <input type="text" id="last_name" class="form-control" name="last_name" value="<?php echo htmlspecialchars($row['last_name']); ?>" required>
    </div>
    <div class="form-group mb-3">
        <label for="first_name">First Name:</label>
        <input type="text" id="first_name" class="form-control" name="first_name" value="<?php echo htmlspecialchars($row['first_name']); ?>" required>
    </div>
    <div class="form-group mb-3">
        <label for="middle_initial">Middle Initial:</label>
        <input type="text" id="middle_initial" class="form-control" name="middle_initial" maxlength="5" value="<?php echo htmlspecialchars($row['middle_initial']); ?>">
    </div>
    <div class="form-group mb-3">
        <label for="gender">Gender:</label>
        <select id="gender" class="form-control" name="gender" required>
            <option value="Male" <?php if ($row['gender'] == 'Male') echo 'selected'; ?>>Male</option>
            <option value="Female" <?php if ($row['gender'] == 'Female') echo 'selected'; ?>>Female</option>
        </select>
    </div>
    <div class="form-group mb-3">
        <label for="birthday">Birthday:</label>
        <input type="date" id="birthday" class="form-control" name="birthday" value="<?php echo htmlspecialchars($row['birthday']); ?>" required>
    </div>
    <div class="form-group mb-3">
        <label for="Email">Email:</label>
        <input type="email" id="Email" class="form-control" name="Email" value="<?php echo htmlspecialchars($row['email']); ?>">
    </div>
    <div class="form-group mb-3">
        <label for="Phone">Phone:</label>
        <input type="text" id="Phone" class="form-control" name="Phone" value="<?php echo htmlspecialchars($row['phone_no']); ?>">
    </div>
    <div class="form-group mb-3">
        <label for="Address">Address:</label>
        <input type="text" id="Address" class="form-control" name="Address" value="<?php echo htmlspecialchars($row['address']); ?>">
    </div>

    <div class="form-group mb-3">
        <label for="valid_id_image">Valid ID Image:</label>
        <?php if ($row['valid_id_image']): ?>
            <div class="mb-2">
                <img src="data:image/jpeg;base64,<?php echo base64_encode($row['valid_id_image']); ?>" alt="Valid ID" width="200" class="img-thumbnail">
            </div>
        <?php endif; ?>
        <input type="file" id="valid_id_image" class="form-control" name="valid_id_image">
        <small class="form-text text-muted">Leave empty to keep the current image</small>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-success">Update Customer</button>
    </div>
</form>

<style>
    input[type=text] {
        text-transform: capitalize;
    }
</style>