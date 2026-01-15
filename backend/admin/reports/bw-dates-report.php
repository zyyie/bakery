<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
requireAdminLogin();
adminRegenerateSession();
include(dirname(__DIR__) . "/includes/header.php");

$results = array();
if(isset($_POST['fromDate']) && isset($_POST['toDate'])){
  $fromDate = trim($_POST['fromDate']);
  $toDate = trim($_POST['toDate']);
  
  // Validate date format
  if(preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromDate) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $toDate)){
    $query = "SELECT orders.*, 
              (SELECT SUM(totalPrice) FROM order_items WHERE orderID = orders.orderID) as totalAmount
              FROM orders 
              WHERE DATE(orderDate) BETWEEN ? AND ? 
              ORDER BY orderDate DESC";
    
    $result = executePreparedQuery($query, "ss", [$fromDate, $toDate]);
    
    if($result){
      while($row = mysqli_fetch_assoc($result)){
        $results[] = $row;
      }
    }
  }
}
?>

<div class="header-bar">
  <h2 class="mb-0" style="color: #333; font-weight: 600;">B/W Dates Report</h2>
</div>

<div class="card mb-4">
  <div class="card-body">
    <form method="POST">
      <div class="row">
        <div class="col-md-4 mb-3">
          <label class="form-label">From Date:</label>
          <input type="date" class="form-control" name="fromDate" value="<?php echo isset($_POST['fromDate']) ? $_POST['fromDate'] : date('Y-m-01'); ?>" required>
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">To Date:</label>
          <input type="date" class="form-control" name="toDate" value="<?php echo isset($_POST['toDate']) ? $_POST['toDate'] : date('Y-m-d'); ?>" required>
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">&nbsp;</label>
          <button type="submit" class="btn btn-warning w-100">SUBMIT</button>
        </div>
      </div>
    </form>
  </div>
</div>

<?php if(!empty($results)): ?>
<div class="card">
  <div class="card-body">
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Order Number</th>
          <th>Full Name</th>
          <th>Order Date</th>
          <th>Status</th>
          <th>Total Amount</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        $grandTotal = 0;
        foreach($results as $row): 
          $grandTotal += $row['totalAmount'];
        ?>
        <tr>
          <td><?php echo $row['orderNumber']; ?></td>
          <td><?php echo $row['fullName']; ?></td>
          <td><?php echo $row['orderDate']; ?></td>
          <td><?php echo $row['orderStatus']; ?></td>
          <td>₱<?php echo number_format($row['totalAmount'], 2); ?></td>
        </tr>
        <?php endforeach; ?>
        <tr>
          <td colspan="4" class="text-end"><strong>Grand Total:</strong></td>
          <td><strong>₱<?php echo number_format($grandTotal, 2); ?></strong></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php include(dirname(__DIR__) . "/includes/footer.php"); ?>

