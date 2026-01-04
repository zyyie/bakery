<?php
require_once __DIR__ . '/includes/bootstrap.php';
requireAdminLogin();
adminRegenerateSession();
include(__DIR__ . "/includes/header.php");

$results = array();
if(isset($_POST['fromDate']) && isset($_POST['toDate'])){
  $fromDate = trim($_POST['fromDate']);
  $toDate = trim($_POST['toDate']);
  $requestType = isset($_POST['requestType']) ? trim($_POST['requestType']) : 'Month wise';
  
  // Validate date format
  if(preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromDate) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $toDate)){
    if($requestType == 'Month wise'){
      $query = "SELECT DATE_FORMAT(orderDate, '%Y-%m') as period, COUNT(*) as totalOrders, SUM((SELECT SUM(totalPrice) FROM order_items WHERE orderID = orders.orderID)) as totalSales 
                FROM orders 
                WHERE DATE(orderDate) BETWEEN ? AND ? 
                GROUP BY DATE_FORMAT(orderDate, '%Y-%m')";
    } else {
      $query = "SELECT YEAR(orderDate) as period, COUNT(*) as totalOrders, SUM((SELECT SUM(totalPrice) FROM order_items WHERE orderID = orders.orderID)) as totalSales 
                FROM orders 
                WHERE DATE(orderDate) BETWEEN ? AND ? 
                GROUP BY YEAR(orderDate)";
    }
    
    $result = executePreparedQuery($query, "ss", [$fromDate, $toDate]);
    
    if($result){
      while($row = mysqli_fetch_assoc($result)){
        $results[] = $row;
      }
    }
  }
}
?>

<h2 class="mb-4">Sales Reports</h2>

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
          <label class="form-label">Request Type:</label>
          <div>
            <input type="radio" name="requestType" value="Month wise" <?php echo (!isset($_POST['requestType']) || $_POST['requestType'] == 'Month wise') ? 'checked' : ''; ?>> Month wise
            <input type="radio" name="requestType" value="Year wise" <?php echo (isset($_POST['requestType']) && $_POST['requestType'] == 'Year wise') ? 'checked' : ''; ?>> Year wise
          </div>
        </div>
      </div>
      <button type="submit" class="btn btn-warning">SUBMIT</button>
    </form>
  </div>
</div>

<?php if(!empty($results)): ?>
<div class="card">
  <div class="card-body">
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Period</th>
          <th>Total Orders</th>
          <th>Total Sales</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($results as $row): ?>
        <tr>
          <td><?php echo $row['period']; ?></td>
          <td><?php echo $row['totalOrders']; ?></td>
          <td>₱<?php echo number_format($row['totalSales'], 2); ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php include(__DIR__ . "/includes/footer.php"); ?>

