<?php
ob_start();
include('config.php'); // Database connection
include('header.php'); // Include header

if (isset($_POST['action'])) {
    $item_id = $_POST['item_id'];
    $action = $_POST['action'];

    if ($action == 'renew') {
        $new_due_date = $_POST['new_due_date'];
        $new_expiry_date = $_POST['new_expiry_date'];

        // Check if the category is renewable
        $check_sql = "
            SELECT c.renewable
            FROM items i
            JOIN item_categories c ON i.category_id = c.category_id
            WHERE i.item_id = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $category = $result->fetch_assoc();

        if (strtolower($category['renewable']) === 'yes') {
            // Update the item status and dates
            $sql = "UPDATE items SET item_status = 'Pawned', due_date = ?, expiry_date = ? WHERE item_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $new_due_date, $new_expiry_date, $item_id);
            $stmt->execute();
        } else {
            echo "<script>alert('This item belongs to a non-renewable category.');</script>";
        }
    } elseif ($action == 'on_sale') {
        $sql = "UPDATE items SET item_status = 'On Sale' WHERE item_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
    }
    header("Location: forfeited_items.php");
    exit();
}

// Fetch forfeited items along with their category details
$sql = "
    SELECT i.*, c.category_name, 
    FROM items i
    JOIN item_categories c ON i.category_id = c.category_id
    WHERE i.item_status = 'Forfeited'";
$result = $conn->query($sql);
?>

<title>Forfeited Items</title>

<div class="container mt-4">
    <h2>Forfeited Items</h2>
    <table class="table table-striped table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Item Name</th>
                <th>Category</th>
                <th>Customer ID</th>
                <th>Pawn Date</th>
                <th>Due Date</th>
                <th>Expiry Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $id = 1;
            while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?= $id++ ?></td>
                    <td><?= htmlspecialchars($row['item_name']) ?></td>
                    <td><?= htmlspecialchars($row['category_name']) ?></td>
                    <td><?= htmlspecialchars($row['customer_id']) ?></td>
                    <td><?= htmlspecialchars($row['pawn_date']) ?></td>
                    <td><?= htmlspecialchars($row['due_date']) ?></td>
                    <td><?= htmlspecialchars($row['expiry_date']) ?></td>
                    <td>
                        <form method="POST" style="display:inline-block;">
                            <input type="hidden" name="item_id" value="<?= $row['item_id'] ?>">
                            <input type="hidden" name="action" value="on_sale">
                            <button type="submit" class="btn btn-success btn-sm">Mark as On Sale</button>
                        </form>

                        <?php if (strtolower($row['renewable']) === 'yes') { ?>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#renewModal<?= $row['item_id'] ?>">Renew</button>

                            <!-- Renew Modal -->
                            <div class="modal fade" id="renewModal<?= $row['item_id'] ?>" tabindex="-1" aria-labelledby="renewModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="renewModalLabel">Renew Item</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="item_id" value="<?= $row['item_id'] ?>">
                                                <input type="hidden" name="action" value="renew">
                                                <div class="mb-3">
                                                    <label for="new_due_date" class="form-label">New Due Date</label>
                                                    <input type="date" name="new_due_date" class="form-control" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="new_expiry_date" class="form-label">New Expiry Date</label>
                                                    <input type="date" name="new_expiry_date" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Renew</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php } else { ?>
                            <button class="btn btn-secondary btn-sm" disabled>Non-Renewable</button>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<?php include('footer.php');
    ob_end_flush();
?>
