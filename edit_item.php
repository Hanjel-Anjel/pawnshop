<?php
include('config.php'); // Include the database connection
session_start();

if (!isset($_SESSION['admin_name']) && !isset($_SESSION['user_name'])) {
    header('location:login_form.php');
    exit();
}

// Get the item ID from the URL
$item_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($item_id <= 0) {
    echo "<div class='alert alert-danger'>Invalid item ID!</div>";
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Fetch updated values from the form
    $item_brand = $_POST['item_brand'];
    $item_model = $_POST['item_model'];
    $item_specifications = $_POST['item_specifications'];
    $item_condition = $_POST['item_condition'];
    $item_value = $_POST['item_value'];
    $item_status = $_POST['item_status'];

    // Update the item details in the database
    $sql = "UPDATE items SET 
                brand = ?, 
                model = ?, 
                specifications = ?, 
                `condition` = ?, 
                item_value = ?, 
                item_status = ? 
            WHERE item_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssisi",
        $item_brand,
        $item_model,
        $item_specifications,
        $item_condition,
        $item_value,
        $item_status,
        $item_id
    );

    if ($stmt->execute()) {
        if ($_SESSION['user_type'] == 'admin') {
            header('Location: item_table.php');
        } else {
            header('Location: inventory_employee.php');
        }
        exit();
    } else {
        echo "<div class='alert alert-danger'>Error updating item: " . $conn->error . "</div>";
    }
}

// Fetch current item details
$sql = "SELECT 
            i.brand AS item_brand,
            i.model AS item_model,
            i.specifications AS item_specifications,
            i.condition AS item_condition,
            i.item_value,
            i.item_status
        FROM items i
        WHERE i.item_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $item = $result->fetch_assoc();
} else {
    echo "<div class='alert alert-danger'>Item not found!</div>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Item</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        input[type=text] {
            text-transform: capitalize;
        }
    </style>

</head>
<body>

<div class="container mt-5">
    <h2>Edit Item</h2>

    <form action="edit_item.php?id=<?php echo $item_id; ?>" method="post" class="row g-3">

        <div class="col-md-6">
            <label for="item_brand" class="form-label">Brand</label>
            <input type="text" id="item_brand" name="item_brand" class="form-control" value="<?php echo htmlspecialchars($item['item_brand']); ?>" required>
        </div>

        <div class="col-md-6">
            <label for="item_model" class="form-label">Model</label>
            <input type="text" id="item_model" name="item_model" class="form-control" value="<?php echo htmlspecialchars($item['item_model']); ?>" required>
        </div>

        <div class="col-md-12">
            <label for="item_specifications" class="form-label">Specifications</label>
            <textarea name="item_specifications" id="item_specifications" class="form-control" style="text-transform: capitalize;" rows="3" required><?php echo htmlspecialchars($item['item_specifications']); ?></textarea>
        </div>

        <div class="col-md-6">
            <label for="item_condition" class="form-label">Condition</label>
            <select name="item_condition" id="item_condition" class="form-control" required>
                <option value="Used" <?php echo ($item['item_condition'] == 'Used' ? 'selected' : ''); ?>>Used</option>
                <option value="Damaged" <?php echo ($item['item_condition'] == 'Damaged' ? 'selected' : ''); ?>>Damaged</option>
                <option value="Brand New" <?php echo ($item['item_condition'] == 'Brand New' ? 'selected' : ''); ?>>Brand New</option>
            </select>
        </div>

        <div class="col-md-6">
            <label for="item_value" class="form-label">Item Value</label>
            <input type="number" id="item_value" name="item_value" class="form-control" step="0.01" value="<?php echo $item['item_value']; ?>" required>
        </div>

        <div class="col-md-6">
            <label for="item_status" class="form-label">Item Status</label>
            <select id="item_status" name="item_status" class="form-control" required>
                <?php
                $status_options = ["Pawned", "Redeemed", "Forfeited"];
                foreach ($status_options as $status) {
                    $selected = ($status === $item['item_status']) ? 'selected' : '';
                    echo "<option value='" . htmlspecialchars($status) . "' $selected>" . htmlspecialchars($status) . "</option>";
                }
                ?>
            </select>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-success">Update Item</button>
            <?php
            if (isset($_SESSION['user_type'])) {
                if ($_SESSION['user_type'] == 'admin') {
                    echo '<a class="btn btn-danger" href="item_table.php" role="button">Cancel</a>';
                } elseif ($_SESSION['user_type'] == 'employee') {
                    echo '<a class="btn btn-danger" href="inventory_employee.php" role="button">Cancel</a>';
                }
            }
            ?>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
