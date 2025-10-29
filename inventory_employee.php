<?php
include('config.php');

if (isset($_GET['delete_item'])) {
    $item_id = $_GET['delete_item'];
    $sql = "DELETE FROM items WHERE item_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $item_id);

    if ($stmt->execute()) {
        header("Location: item_table.php");
        exit();
    } else {
        echo "Error deleting item: " . $conn->error;
    }
    $stmt->close();
}

include('header_employee.php');
?>

<title>Inventory Management</title>

<style>
    :root {
        --primary-color: #1976d2;
        --primary-dark: #1565c0;
        --primary-light: #42a5f5;
        --success-color: #2e7d32;
        --danger-color: #d32f2f;
        --warning-color: #f57c00;
        --surface: #ffffff;
        --background: #f5f5f5;
        --text-primary: #212121;
        --text-secondary: #757575;
        --divider: #e0e0e0;
    }

    body {
        background-color: var(--background);
        font-family: 'Roboto', 'Segoe UI', sans-serif;
    }

    .container-fluid {
        max-width: 1400px;
        margin: 0 auto;
    }

    /* Enhanced Header */
    .page-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        color: white;
        padding: 2rem 0;
        margin: -1rem -1rem 2rem -1rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .page-header h2 {
        margin: 0;
        font-weight: 500;
        font-size: 1.75rem;
        letter-spacing: 0.5px;
    }

    /* Enhanced Card Design */
    .filter-card {
        background: var(--surface);
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        margin-bottom: 1.5rem;
        border: none;
    }

    .filter-card h5 {
        color: var(--text-primary);
        font-weight: 500;
        margin-bottom: 1.25rem;
        font-size: 1rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Enhanced Form Controls */
    .form-label {
        font-weight: 500;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .form-control, .form-select {
        border: 1px solid var(--divider);
        border-radius: 8px;
        padding: 0.625rem 0.875rem;
        transition: all 0.3s ease;
        font-size: 0.95rem;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(25, 118, 210, 0.1);
        outline: none;
    }

    /* Enhanced Buttons */
    .btn {
        border-radius: 8px;
        padding: 0.625rem 1.5rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
        border: none;
        font-size: 0.875rem;
    }

    .btn-primary {
        background: var(--primary-color);
        box-shadow: 0 2px 4px rgba(25, 118, 210, 0.3);
    }

    .btn-primary:hover {
        background: var(--primary-dark);
        box-shadow: 0 4px 8px rgba(25, 118, 210, 0.4);
        transform: translateY(-1px);
    }

    .btn-success {
        background: var(--success-color);
        box-shadow: 0 2px 4px rgba(46, 125, 50, 0.3);
    }

    .btn-success:hover {
        background: #1b5e20;
        box-shadow: 0 4px 8px rgba(46, 125, 50, 0.4);
        transform: translateY(-1px);
    }

    .btn-danger {
        background: var(--danger-color);
    }

    .btn-outline-secondary {
        border: 2px solid var(--divider);
        color: var(--text-secondary);
        background: transparent;
    }

    .btn-outline-secondary:hover {
        background: var(--divider);
        border-color: var(--text-secondary);
        color: var(--text-primary);
    }

    /* Enhanced Table */
    .table-container {
        background: var(--surface);
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        overflow: hidden;
    }

    .table {
        margin-bottom: 0;
    }

    .table thead th {
        background: linear-gradient(135deg, #263238 0%, #37474f 100%);
        color: white;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.8rem;
        padding: 1rem;
        border: none;
        white-space: nowrap;
    }

    .table tbody tr {
        transition: all 0.2s ease;
        border-bottom: 1px solid var(--divider);
    }

    .table tbody tr:hover {
        background-color: rgba(25, 118, 210, 0.04);
        transform: scale(1.001);
    }

    .table tbody td {
        padding: 1rem;
        vertical-align: middle;
        color: var(--text-primary);
        font-size: 0.9rem;
    }

    /* Status Badges */
    .status-badge {
        display: inline-block;
        padding: 0.375rem 0.875rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-pawned {
        background-color: #e3f2fd;
        color: #1565c0;
    }

    .status-redeemed {
        background-color: #e8f5e9;
        color: #2e7d32;
    }

    .status-fully-paid {
        background-color: #e8f5e9;
        color: #2e7d32;
    }

    .status-on-sale {
        background-color: #fff3e0;
        color: #e65100;
    }

    .status-forfeited {
        background-color: #ffebee;
        color: #c62828;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
        flex-wrap: wrap;
    }

    .btn-sm {
        padding: 0.375rem 0.875rem;
        font-size: 0.8rem;
    }

    /* Modal Enhancements */
    .modal-content {
        border-radius: 12px;
        border: none;
        box-shadow: 0 8px 32px rgba(0,0,0,0.15);
    }

    .modal-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        color: white;
        border-radius: 12px 12px 0 0;
        padding: 1.25rem 1.5rem;
    }

    .modal-header .btn-close {
        filter: brightness(0) invert(1);
    }

    .modal-title {
        font-weight: 500;
        font-size: 1.25rem;
    }

    .modal-body {
        padding: 1.5rem;
    }

    .modal-footer {
        border-top: 1px solid var(--divider);
        padding: 1rem 1.5rem;
    }

    /* Search and Filter Section */
    .filter-row {
        display: flex;
        gap: 1rem;
        align-items: flex-end;
    }

    .filter-col {
        flex: 1;
        min-width: 0;
    }

    .filter-actions {
        display: flex;
        gap: 0.75rem;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .filter-row {
            flex-direction: column;
        }

        .filter-col {
            width: 100%;
        }

        .filter-actions {
            width: 100%;
        }

        .filter-actions button {
            flex: 1;
        }

        .action-buttons {
            flex-direction: column;
        }

        .page-header h2 {
            font-size: 1.5rem;
        }
    }

    /* Loading Spinner */
    .spinner-border {
        color: var(--primary-color);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: var(--text-secondary);
    }

    .empty-state i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.3;
    }
</style>

<!-- Add Item Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addItemModalLabel">
            <i class="bi bi-plus-circle me-2"></i>Add New Item for Existing Customer
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <?php if (isset($error_message)) { ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php } ?>

        <form action="process_add_item.php" method="post" id="addItemForm">
            <div class="mb-3">
                <label for="customer_search" class="form-label">Search Customer</label>
                <input type="text" id="customer_search" class="form-control" placeholder="Type customer name to search...">
            </div>
            
            <div class="mb-3">
                <label for="customer_id" class="form-label">Customer</label>
                <select name="customer_id" id="customer_id" class="form-control" required>
                    <option value="">Select a customer</option>
                    <?php
                    $select_customers_query = "SELECT customer_id, first_name, last_name FROM custumer_info";
                    $customers_result = mysqli_query($conn, $select_customers_query);
                    while ($customer = mysqli_fetch_assoc($customers_result)) {
                        echo "<option value='" . $customer['customer_id'] . "'>" . htmlspecialchars($customer['first_name']) . " " . htmlspecialchars($customer['last_name']) . "</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="brand" class="form-label">Brand</label>
                    <input type="text" name="brand" id="brand" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="model" class="form-label">Model</label>
                    <input type="text" name="model" id="model" class="form-control" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="specifications" class="form-label">Specifications</label>
                <textarea name="specifications" id="specifications" class="form-control" rows="3" style="text-transform: capitalize;" required></textarea>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="condition" class="form-label">Condition</label>
                    <select name="condition" id="condition" class="form-control" required>
                        <option value="Used">Used</option>
                        <option value="Damaged">Damaged</option>
                        <option value="Brand New">Brand New</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="category_id" class="form-label">Category</label>
                    <select name="category_id" id="category_id" class="form-control" required>
                        <option value="">Select a category</option>
                        <?php
                        $select_categories_query = "SELECT * FROM item_categories";
                        $categories_result = mysqli_query($conn, $select_categories_query);
                        while ($category = mysqli_fetch_assoc($categories_result)) {
                            echo "<option value='" . $category['category_id'] . "'>" . htmlspecialchars($category['category_name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="item_value" class="form-label">Item Value</label>
                    <input type="number" name="item_value" id="item_value" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="loan_amount" class="form-label">Loan Amount</label>
                    <input type="number" name="loan_amount" id="loan_amount" class="form-control" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="pawn_date" class="form-label">Pawn Date</label>
                    <input type="date" id="pawn_date" name="pawn_date" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="due_date" class="form-label">Due Date</label>
                    <input type="date" id="due_date" name="due_date" class="form-control" readonly>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="expiry_date" class="form-label">Expiry Date</label>
                    <input type="date" id="expiry_date" name="expiry_date" class="form-control" readonly>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="plan" class="form-label">Plan</label>
                    <select id="plan" name="plan" class="form-control" required>
                        <option value="" disabled selected>Select Plan</option>
                        <option value="1-month">1 Month</option>
                        <option value="3-months">3 Months</option>
                        <option value="6-months">6 Months</option>
                        <option value="1-year">1 Year</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label">Item Status</label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="Pawned">Pawned</option>
                    </select>
                </div>
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" form="addItemForm" name="add_item" class="btn btn-primary">
            <i class="bi bi-check-circle me-1"></i>Add Item
        </button>
      </div>
    </div>
  </div>
</div>

<!-- View Item Modal -->
<div class="modal fade" id="viewItemModal" tabindex="-1" aria-labelledby="viewItemModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewItemModalLabel">
            <i class="bi bi-eye me-2"></i>Item Details
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="viewItemContent">
        <div class="text-center">
          <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<div class="container-fluid mt-4">
    <div class="page-header">
        <div class="container-fluid">
            <h2><i class="bi bi-box-seam me-2"></i>Item Inventory Management</h2>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="filter-card">
        <h5><i class="bi bi-funnel me-2"></i>Search & Filter</h5>
        <form method="GET">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" name="search" id="search" class="form-control"
                        placeholder="Customer or item..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                </div>

                <div class="col-md-2">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" name="start_date" id="start_date" class="form-control"
                        value="<?= isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : '' ?>">
                </div>

                <div class="col-md-2">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" name="end_date" id="end_date" class="form-control"
                        value="<?= isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : '' ?>">
                </div>

                <div class="col-md-2">
                    <label for="item_status" class="form-label">Status</label>
                    <select name="item_status" id="item_status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="Pawned" <?= isset($_GET['item_status']) && $_GET['item_status'] == 'Pawned' ? 'selected' : '' ?>>Pawned</option>
                        <option value="Fully Paid" <?= isset($_GET['item_status']) && $_GET['item_status'] == 'Fully Paid' ? 'selected' : '' ?>>Fully Paid</option>
                        <option value="Redeemed" <?= isset($_GET['item_status']) && $_GET['item_status'] == 'Redeemed' ? 'selected' : '' ?>>Redeemed</option>
                        <option value="On Sale" <?= isset($_GET['item_status']) && $_GET['item_status'] == 'On Sale' ? 'selected' : '' ?>>On Sale</option>
                        <option value="Forfeited" <?= isset($_GET['item_status']) && $_GET['item_status'] == 'Forfeited' ? 'selected' : '' ?>>Forfeited</option>
                    </select>
                </div>
                
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="bi bi-search me-1"></i>Search
                    </button>
                    <button type="button" class="btn btn-success flex-fill" data-bs-toggle="modal" data-bs-target="#addItemModal">
                        <i class="bi bi-plus-lg me-1"></i>Add Item
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Table Container -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr class="text-center">
                        <th>ID</th>
                        <th>Customer Name</th>
                        <th>Brand</th>
                        <th>Model</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Total Balance</th>
                        <th>Pawn Date</th>
                        <th>Due Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT 
                                i.item_id,
                                c.last_name AS customer_last_name,
                                c.first_name AS customer_first_name,
                                i.brand,
                                i.model,
                                ic.category_name,
                                i.item_status,
                                i.loan_amount,
                                i.total_balance,
                                i.pawn_date,
                                i.due_date
                            FROM 
                                items i
                            INNER JOIN 
                                custumer_info c ON i.customer_id = c.customer_id
                            INNER JOIN 
                                item_categories ic ON i.category_id = ic.category_id
                            WHERE 1=1";

                    $search = isset($_GET['search']) ? $_GET['search'] : '';
                    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
                    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
                    $item_status_filter = isset($_GET['item_status']) ? $_GET['item_status'] : '';

                    if (!empty($search)) {
                        $sql .= " AND (
                            i.brand LIKE '%" . $conn->real_escape_string($search) . "%' OR
                            i.model LIKE '%" . $conn->real_escape_string($search) . "%' OR
                            c.last_name LIKE '%" . $conn->real_escape_string($search) . "%' OR
                            c.first_name LIKE '%" . $conn->real_escape_string($search) . "%'
                        )";
                    }

                    if (!empty($start_date)) {
                        $sql .= " AND i.pawn_date >= '" . $conn->real_escape_string($start_date) . "'";
                    }

                    if (!empty($end_date)) {
                        $sql .= " AND i.pawn_date <= '" . $conn->real_escape_string($end_date) . "'";
                    }

                    if (!empty($item_status_filter)) {
                        $sql .= " AND i.item_status = '" . $conn->real_escape_string($item_status_filter) . "'";
                    }

                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        $id = 1;
                        while ($row = $result->fetch_assoc()) {
                            $statusClass = 'status-' . strtolower(str_replace(' ', '-', $row['item_status']));
                            echo "<tr class='text-center' data-item-id='" . $row['item_id'] . "'>";
                            echo "<td><strong>" . $id++ . "</strong></td>";
                            echo "<td>" . htmlspecialchars($row['customer_last_name']) . ", " . htmlspecialchars($row['customer_first_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['brand']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['model']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['category_name']) . "</td>";
                            echo "<td><span class='status-badge item-status " . $statusClass . "'>" . htmlspecialchars($row['item_status']) . "</span></td>";
                            echo "<td><strong>₱" . number_format($row['total_balance'], 2) . "</strong></td>";
                            echo "<td>" . date('M d, Y', strtotime($row['pawn_date'])) . "</td>";
                            echo "<td>" . date('M d, Y', strtotime($row['due_date'])) . "</td>";
                          
                              
                            echo "<td><div class='action-buttons'>";
                            echo "<button class='btn btn-primary btn-sm view-item' data-item-id='" . $row['item_id'] . "'>
                                    <i class='bi bi-eye me-1'></i>View
                                </button>";

                            //  Only show the Pay button if item is NOT Fully Paid
                            if (strtolower(trim($row['item_status'])) !== 'fully paid') {
                                echo "<a href='payment.php?item_id=" . $row['item_id'] . "' class='btn btn-success btn-sm'>
                                        <i class='bi bi-cash-coin me-1'></i>Pay
                                    </a>";
                            }

                            echo "</div></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='10' class='empty-state'>";
                        echo "<div><i class='bi bi-inbox'></i></div>";
                        echo "<h5>No items found</h5>";
                        echo "<p>Try adjusting your search or filter criteria</p>";
                        echo "</td></tr>";
                    }

                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function updateItemStatuses() {
        fetch('update_item_status.php')
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error(data.error);
                    return;
                }

                data.forEach(item => {
                    const row = document.querySelector(`tr[data-item-id="${item.item_id}"]`);
                    if (row) {
                        const statusElement = row.querySelector('.item-status');
                        statusElement.textContent = item.item_status;
                        statusElement.className = 'status-badge item-status status-' + item.item_status.toLowerCase().replace(' ', '-');
                    }
                });
            })
            .catch(error => console.error('Error updating item statuses:', error));
    }

    setInterval(updateItemStatuses, 10000);

    document.addEventListener("DOMContentLoaded", function() {
        const pawnDateInput = document.getElementById("pawn_date");
        const dueDateInput = document.getElementById("due_date");
        const expiryDateInput = document.getElementById("expiry_date");

        pawnDateInput.value = new Date().toISOString().split("T")[0];

        const maturityMonths = {
            "1-month": 1,
            "3-months": 3,
            "6-months": 6,
            "1-year": 12
        };
        const expiryDaysAfterDue = 7;

        function updateDates() {
            const pawnDateValue = pawnDateInput.value;
            const selectedPlan = document.getElementById("plan").value;

            if (pawnDateValue && selectedPlan) {
                const pawnDate = new Date(pawnDateValue);
                const dueDate = new Date(pawnDate);
                dueDate.setMonth(dueDate.getMonth() + maturityMonths[selectedPlan]);

                const expiryDate = new Date(dueDate);
                expiryDate.setDate(expiryDate.getDate() + expiryDaysAfterDue);

                dueDateInput.value = dueDate.toISOString().split("T")[0];
                expiryDateInput.value = expiryDate.toISOString().split("T")[0];
            } else {
                dueDateInput.value = "";
                expiryDateInput.value = "";
            }
        }

        pawnDateInput.addEventListener("change", updateDates);
        document.getElementById("plan").addEventListener("change", updateDates);

        const searchInput = document.getElementById("customer_search");
        const customerSelect = document.getElementById("customer_id");

        searchInput.addEventListener("keyup", function () {
            const searchValue = searchInput.value.toLowerCase();
            for (let option of customerSelect.options) {
                if (option.text.toLowerCase().includes(searchValue) || option.value === "") {
                    option.style.display = "";
                } else {
                    option.style.display = "none";
                }
            }
        });

        const viewButtons = document.querySelectorAll('.view-item');
        const viewItemModal = document.getElementById('viewItemModal');
        const viewItemContent = document.getElementById('viewItemContent');
        
        viewButtons.forEach(button => {
            button.addEventListener('click', function() {
                const itemId = this.getAttribute('data-item-id');
                viewItemContent.innerHTML = `
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                `;
                
                const modal = new bootstrap.Modal(viewItemModal);
                modal.show();
                
                fetch(`get_item_details.php?id=${itemId}`)
                    .then(response => response.text())
                    .then(data => {
                        viewItemContent.innerHTML = data;
                    })
                    .catch(error => {
                        viewItemContent.innerHTML = `<div class="alert alert-danger">Error loading item details: ${error}</div>`;
                    });
            });
        });
    });
</script>

<?php include('footer.php'); ?>