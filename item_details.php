<?php 
    include ('config.php');
    include ('header.php');
?>
<div class="container mt-5">
    
    <h2>Uploaded Item Details</h2>
    <div class="d-flex justify-content-end mb-2 ">
        <a href="upload_item_details.php" class="btn btn-primary">Add</a>
    </div>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Brand</th>
                <th>Model</th>
                <th>Specifications</th>
                <th>Appraisal Price</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT * FROM item_details";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['detail_id'] . "</td>";
                    echo "<td>" . htmlspecialchars($row['brand']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['model']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['specifications']) . "</td>";
                    echo "<td>₱" . number_format($row['appraisal_price'], 2) . "</td>";
                    echo "<td>
                            <a href='edit_item_detail.php?id=" . $row['detail_id'] . "' class='btn btn-primary btn-sm'>Edit</a>
                            <a href='delete_item_detail.php?id=" . $row['detail_id'] . "' class='btn btn-danger btn-sm' onclick=\"return confirm('Are you sure you want to delete this item?');\">Delete</a>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6' class='text-center'>No item details found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
   
    
</div>
<?php include('footer.php');
    ob_end_flush();
?>

