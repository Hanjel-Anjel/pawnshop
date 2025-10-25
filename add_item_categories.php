<?php
include('config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category_name = $_POST['category_name'];
    $category_description = $_POST['category_description'];

    // Basic input validation
    if (empty($category_name)) {
        echo "Category Name is required.";
    } else {
        // Prepare and execute the SQL query
        $sql = "INSERT INTO item_categories (category_name, category_description) 
                VALUES (?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $category_name, $category_description);

        if ($stmt->execute()) {
            header('Location: item_categories.php'); // Redirect to the categories page
            exit();
        } else {
            echo "Error: " . $stmt->error;
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
    <title>Add Item Category</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        input[type=text]{
        text-transform: capitalize;
    }
    </style>
<<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Item Category</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        input[type=text] {
            text-transform: capitalize;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card mt-5">
            <div class="card-body">
                <h1 class="mb-4">Add Item Category</h1>
                <form action="" method="post">
                    <div class="form-group mb-3">
                        <label for="category_name" class="form-label">Category Name:</label>
                        <input type="text" id="category_name" class="form-control" name="category_name" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="category_description" class="form-label">Description (Optional):</label>
                        <textarea id="category_description" class="form-control" name="category_description"></textarea>
                    </div>

                    <button type="submit" class="btn btn-success">Add</button>
                    <a href="item_categories.php" class="btn btn-danger">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
