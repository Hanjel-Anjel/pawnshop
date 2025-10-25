<?php
include('config.php');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Delete category (if delete button is clicked)
if (isset($_GET['delete_category'])) {
    $category_id = $_GET['delete_category'];
    $sql = "DELETE FROM item_categories WHERE category_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $category_id);

    try {
        if ($stmt->execute()) {
            header('location: item_categories.php');
            exit();
        } else {
            $delete_error = "Error deleting category: " . $conn->error;
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1451) {
            $delete_error = "This category cannot be deleted because there are items linked to it.";
        } else {
            $delete_error = "Error deleting category: " . $e->getMessage();
        }
    }
    $stmt->close();
}

// Process form submission for adding a new category
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_category'])) {
    $category_name = $_POST['category_name'];
    $category_description = $_POST['category_description'];

    if (empty($category_name)) {
        $add_error = "Category Name is required.";
    } else {
        $sql = "INSERT INTO item_categories (category_name, category_description) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $category_name, $category_description);

        if ($stmt->execute()) {
            header('Location: item_categories.php');
            exit();
        } else {
            $add_error = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Process form submission for editing category
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_category'])) {
    $category_id = $_POST['category_id'];
    $category_name = $_POST['category_name'];
    $category_description = $_POST['category_description'];

    if (empty($category_name)) {
        $update_error = "Category Name is required.";
    } else {
        $sql = "UPDATE item_categories 
                SET category_name = ?, category_description = ? 
                WHERE category_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $category_name, $category_description, $category_id);

        if ($stmt->execute()) {
            header("Location: item_categories.php");
            exit();
        } else {
            $update_error = "Error updating category: " . $stmt->error;
        }
        $stmt->close();
    }
}

include('header.php');
?>

<title>Item Categories</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    :root {
        --primary-color: #1976d2;
        --primary-dark: #1565c0;
        --success-color: #2e7d32;
        --warning-color: #f57c00;
        --danger-color: #d32f2f;
        --surface-color: #ffffff;
        --background-color: #f5f7fa;
    }

    body {
        background-color: var(--background-color);
    }

    .page-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        color: white;
        padding: 2rem 0;
        margin-bottom: 2rem;
        border-radius: 0 0 24px 24px;
        box-shadow: 0 4px 20px rgba(25, 118, 210, 0.3);
    }

    .page-header h1 {
        font-weight: 600;
        font-size: 2rem;
        margin: 0;
        letter-spacing: 0.5px;
    }

    .card-container {
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        padding: 1.5rem;
        margin-bottom: 2rem;
    }

    .btn-add-category {
        background: linear-gradient(135deg, var(--success-color) 0%, #388e3c 100%);
        border: none;
        border-radius: 12px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 12px rgba(46, 125, 50, 0.3);
        transition: all 0.3s ease;
    }

    .btn-add-category:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(46, 125, 50, 0.4);
    }

    .table-container {
        overflow-x: auto;
        border-radius: 12px;
    }

    .table {
        margin-bottom: 0;
    }

    .table thead th {
        background: linear-gradient(135deg, #37474f 0%, #455a64 100%);
        color: white;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.875rem;
        letter-spacing: 0.5px;
        border: none;
        padding: 1rem;
    }

    .table tbody tr {
        transition: all 0.2s ease;
        border-bottom: 1px solid #e0e0e0;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
        transform: scale(1.01);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .table tbody td {
        padding: 1rem;
        vertical-align: middle;
    }

    .badge-id {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        color: white;
        padding: 0.5rem 0.75rem;
        border-radius: 8px;
        font-weight: 600;
        display: inline-block;
        min-width: 40px;
        text-align: center;
    }

    .category-name {
        font-weight: 600;
        color: #263238;
        font-size: 1rem;
    }

    .category-description {
        color: #546e7a;
        font-size: 0.875rem;
    }

    .btn-action {
        border-radius: 8px;
        padding: 0.5rem 1rem;
        font-weight: 600;
        border: none;
        transition: all 0.2s ease;
        font-size: 0.875rem;
    }

    .btn-edit {
        background: linear-gradient(135deg, var(--warning-color) 0%, #fb8c00 100%);
        color: white;
        box-shadow: 0 2px 8px rgba(245, 124, 0, 0.3);
    }

    .btn-edit:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(245, 124, 0, 0.4);
        color: white;
    }

    .btn-delete {
        background: linear-gradient(135deg, var(--danger-color) 0%, #e53935 100%);
        color: white;
        box-shadow: 0 2px 8px rgba(211, 47, 47, 0.3);
    }

    .btn-delete:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(211, 47, 47, 0.4);
        color: white;
    }

    .modal-content {
        border-radius: 16px;
        border: none;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    }

    .modal-header {
        border-radius: 16px 16px 0 0;
        border-bottom: none;
        padding: 1.5rem;
    }

    .modal-title {
        font-weight: 600;
        font-size: 1.25rem;
    }

    .modal-body {
        padding: 1.5rem;
    }

    .form-label {
        font-weight: 600;
        color: #37474f;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .form-control, .form-control:focus {
        border-radius: 12px;
        border: 2px solid #e0e0e0;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(25, 118, 210, 0.15);
    }

    .modal-footer {
        border-top: none;
        padding: 1rem 1.5rem 1.5rem;
    }

    .btn-modal-cancel {
        background: #f5f5f5;
        color: #546e7a;
        border: none;
        border-radius: 12px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .btn-modal-cancel:hover {
        background: #e0e0e0;
        transform: translateY(-2px);
    }

    .btn-modal-submit {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(25, 118, 210, 0.3);
        transition: all 0.2s ease;
    }

    .btn-modal-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(25, 118, 210, 0.4);
    }

    .alert {
        border-radius: 12px;
        border: none;
        padding: 1rem 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .alert-danger {
        background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
        color: var(--danger-color);
    }

    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: #90a4ae;
    }

    .empty-state i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .bi {
        vertical-align: middle;
    }

    @media (max-width: 768px) {
        .page-header h1 {
            font-size: 1.5rem;
        }
        
        .btn-action {
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
            margin-bottom: 0.5rem;
        }
    }
</style>

<div class="page-header">
    <div class="container">
        <h1><i class="bi bi-box-seam me-2"></i>Item Categories</h1>
    </div>
</div>

<div class="container">
    <div class="card-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="mb-0" style="color: #37474f; font-weight: 600;">Category Management</h5>
                <small class="text-muted">Organize and manage your item categories</small>
            </div>
            <button type="button" class="btn btn-add-category btn-success" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                <i class="bi bi-plus-circle me-1"></i> Add Category
            </button>
        </div>

        <?php if (isset($update_error)): ?>
        <div class="alert alert-danger mb-3">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Update Error:</strong> <?php echo htmlspecialchars($update_error); ?>
        </div>
        <?php endif; ?>

        <?php if (isset($add_error)): ?>
        <div class="alert alert-danger mb-3">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Add Error:</strong> <?php echo htmlspecialchars($add_error); ?>
        </div>
        <?php endif; ?>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th>Category Name</th>
                        <th style="width: 500px;">Description</th>
                        <th style="width: 220px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM item_categories";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        $id = 1;
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td><span class='badge-id'>" . $id++ . "</span></td>";
                            echo "<td><div class='category-name'>" . htmlspecialchars($row['category_name']) . "</div></td>";
                            echo "<td><div class='category-description'>" . htmlspecialchars($row['category_description'] ?? 'No description') . "</div></td>";
                            echo "<td>";
                            echo "<button type='button' class='btn btn-edit btn-action btn-sm me-2 edit-category-btn' 
                                    data-bs-toggle='modal' data-bs-target='#editCategoryModal' 
                                    data-category-id='" . $row['category_id'] . "' 
                                    data-category-name='" . htmlspecialchars($row['category_name']) . "' 
                                    data-category-description='" . htmlspecialchars($row['category_description'] ?? '') . "'>
                                    <i class='bi bi-pencil-square'></i> Edit
                                  </button>";
                            echo "<a href='?delete_category=" . $row['category_id'] . "' class='btn btn-delete btn-action btn-sm' onclick=\"return confirm('Are you sure you want to delete this category?');\"><i class='bi bi-trash'></i> Delete</a>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4'><div class='empty-state'><i class='bi bi-inbox'></i><p>No categories found. Start by adding your first category!</p></div></td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--success-color) 0%, #388e3c 100%); color: white;">
                <h5 class="modal-title" id="addCategoryModalLabel"><i class="bi bi-plus-circle me-2"></i>Add New Category</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="">
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="add_category_name" class="form-label">Category Name</label>
                        <input type="text" id="add_category_name" name="category_name" class="form-control" placeholder="Enter category name" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="add_category_description" class="form-label">Description (Optional)</label>
                        <textarea id="add_category_description" name="category_description" class="form-control" rows="3" placeholder="Enter category description"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-modal-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_category" class="btn btn-modal-submit">Add Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--warning-color) 0%, #fb8c00 100%); color: white;">
                <h5 class="modal-title" id="editCategoryModalLabel"><i class="bi bi-pencil-square me-2"></i>Edit Category</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="">
                <div class="modal-body">
                    <input type="hidden" id="category_id" name="category_id">
                    <div class="form-group mb-3">
                        <label for="category_name" class="form-label">Category Name</label>
                        <input type="text" id="category_name" name="category_name" class="form-control" placeholder="Enter category name" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="category_description" class="form-label">Description (Optional)</label>
                        <textarea id="category_description" name="category_description" class="form-control" rows="3" placeholder="Enter category description"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-modal-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_category" class="btn btn-modal-submit">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Error Modal -->
<div class="modal fade" id="deleteErrorModal" tabindex="-1" aria-labelledby="deleteErrorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--danger-color) 0%, #e53935 100%); color: white;">
                <h5 class="modal-title" id="deleteErrorModalLabel"><i class="bi bi-x-circle me-2"></i>Delete Error</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center" style="padding: 2rem;">
                <i class="bi bi-exclamation-triangle" style="font-size: 3rem; color: var(--danger-color); margin-bottom: 1rem;"></i>
                <p class="fs-5 mb-0"><?php if (isset($delete_error)) echo htmlspecialchars($delete_error); ?></p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-modal-cancel px-4" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const editButtons = document.querySelectorAll('.edit-category-btn');
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const categoryId = this.getAttribute('data-category-id');
                const categoryName = this.getAttribute('data-category-name');
                const categoryDescription = this.getAttribute('data-category-description');
                document.getElementById('category_id').value = categoryId;
                document.getElementById('category_name').value = categoryName;
                document.getElementById('category_description').value = categoryDescription;
            });
        });
    });
</script>

<?php if (isset($delete_error)): ?>
<script>
    var deleteErrorModal = new bootstrap.Modal(document.getElementById('deleteErrorModal'));
    deleteErrorModal.show();
</script>
<?php endif; ?>

<?php include('footer.php'); ?>