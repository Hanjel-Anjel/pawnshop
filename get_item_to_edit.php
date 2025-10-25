<?php
include('config.php'); // Include the database connection

// Get the item ID from the URL
$item_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($item_id <= 0) {
    echo "<div class='alert alert-danger'>Invalid item ID!</div>";
    exit;
}

// Fetch current item details
$sql = "SELECT 
            i.item_id,
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
    exit;
}
?>

<form id="editItemForm" class="row g-3">
    <input type="hidden" name="item_id" value="<?php echo $item_id; ?>">

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
</form>