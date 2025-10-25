<?php
include("config.php");
session_start();

if (isset($_GET["id"])) {
  $item_id = $_GET["id"];

  $sql = "SELECT 
                i.item_id,
                c.last_name AS customer_last_name, 
                c.first_name AS customer_first_name,
                i.brand,
                i.model,
                i.specifications,
                i.condition,
                ic.category_name,  
                i.item_value,
                i.item_status,
                i.loan_amount,
                i.pawn_date,
                i.due_date,
                i.expiry_date, 
                i.interest_rate, 
                i.total_balance 
            FROM 
                items i
            INNER JOIN 
                custumer_info c ON i.customer_id = c.customer_id
            INNER JOIN  
                item_categories ic ON i.category_id = ic.category_id
            WHERE 
                i.item_id = ?";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $item_id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $item = $result->fetch_assoc();
    ?>
    <div class="card">
      <div class="card-body">
        <p><strong>Customer Name:</strong> <?php echo $item["customer_last_name"] . ", " . $item["customer_first_name"]; ?></p>
        <p><strong>Brand:</strong> <?php echo $item["brand"]; ?></p>
        <p><strong>Model:</strong> <?php echo $item["model"]; ?></p>
        <p><strong>Specifications:</strong> <?php echo $item["specifications"]; ?></p>
        <p><strong>condition:</strong> <?php echo $item["condition"]; ?></p>
        <p><strong>Category:</strong> <?php echo $item["category_name"]; ?></p>
        <p><strong>Value:</strong> ₱<?php echo number_format($item["item_value"], 2); ?></p>
        <p><strong>Status:</strong> <?php echo $item["item_status"]; ?></p>
        <p><strong>Loan Amount:</strong> ₱<?php echo number_format($item["loan_amount"], 2); ?></p>
        <p><strong>Interest Amount:</strong> ₱<?php echo number_format($item["interest_rate"], 2); ?></p>
        <p><strong>Total Balance:</strong> ₱<?php echo number_format($item["total_balance"], 2); ?></p>
        <p><strong>Pawn Date:</strong> <?php echo $item["pawn_date"]; ?></p>
        <p><strong>Due Date:</strong> <?php echo $item["due_date"]; ?></p>
        <p><strong>Expiry Date:</strong> <?php echo $item["expiry_date"]; ?></p>
      </div>
    </div>
    <?php
  } else {
    echo "<div class='alert alert-danger'>Item not found!</div>";
  }
  $stmt->close();
} else {
  echo "<div class='alert alert-danger'>Invalid request!</div>";
}
$conn->close();
?>