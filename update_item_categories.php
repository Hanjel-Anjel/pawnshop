<?php
include('config.php');

// Get the category ID from the URL
if (isset($_GET['id'])) {
    $category_id = $_GET['id'];

    // Fetch the existing category data for pre-filling the form
    $sql = "SELECT * FROM item_categories WHERE category_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $category = $result->fetch_assoc();
    } else {
        echo "<div class='alert alert-danger'>Category not found!</div>";
        exit();
    }
    $stmt->close();
} else {
    echo "<div class='alert alert-danger'>Invalid request!</div>";
    exit();
}

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get updated values from the form
    $category_name = $_POST['category_name'];
    $category_description = $_POST['category_description'];

    // Basic input validation
    if (empty($category_name)) {
        echo "<div class='alert alert-danger'>Category Name is required.</div>";
    } else {
        // Prepare the SQL update query
        $sql = "UPDATE item_categories 
                SET 
                    category_name = ?, 
                    category_description = ? 
                WHERE category_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $category_name, $category_description, $category_id);

        // Execute the query and check if the update was successful
        if ($stmt->execute()) {
            // Redirect to the item categories page after successful update
            header("Location: item_categories.php");
            exit();
        } else {
            echo "<div class='alert alert-danger'>Error updating category: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Item Category</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        input[type=text] {
            text-transform: capitalize;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-body">
                <h1 class="mb-4">Edit Item Category</h1>
                <form action="" method="post">
                    <div class="form-group mb-3">
                        <label for="category_name" class="form-label">Category Name:</label>
                        <input type="text" id="category_name" name="category_name" class="form-control" 
                               value="<?php echo htmlspecialchars($category['category_name']); ?>" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="category_description" class="form-label">Description (Optional):</label>
                        <textarea id="category_description" name="category_description" class="form-control"><?php echo htmlspecialchars($category['category_description']); ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-success">Update</button>
                    <a href="item_categories.php" class="btn btn-danger">Cancel</a>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
