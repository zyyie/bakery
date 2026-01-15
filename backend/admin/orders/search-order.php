<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
requireAdminLogin();
adminRegenerateSession();
include(dirname(__DIR__) . "/includes/header.php");

$orders = array();
if(isset($_POST['orderNumber'])){
  $orderNumber = trim($_POST['orderNumber']);
  
  if(!empty($orderNumber)){
    $query = "SELECT * FROM orders WHERE orderNumber = ?";
    $result = executePreparedQuery($query, "s", [$orderNumber]);
    
    if($result){
      while($row = mysqli_fetch_assoc($result)){
        $orders[] = $row;
      }
    }
  }
}
?>

<div class="header-bar">
  <h2 class="mb-0" style="color: #333; font-weight: 600;">Search Order</h2>
</div>

<div class="card mb-4">
  <div class="card-body">
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Search by Order No</label>
        <input type="text" class="form-control" name="orderNumber" placeholder="Enter Order Number">
      </div>
      <button type="submit" class="btn btn-warning">SEARCH</button>
    </form>
  </div>
</div>

<?php if(!empty($orders)): ?>
<div class="card">
  <div class="card-body">
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Order Number</th>
          <th>Full Name</th>
          <th>Contact Number</th>
          <th>Order Date</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($orders as $order): ?>
        <tr>
          <td><?php echo $order['orderNumber']; ?></td>
          <td><?php echo $order['fullName']; ?></td>
          <td><?php echo $order['contactNumber']; ?></td>
          <td><?php echo $order['orderDate']; ?></td>
          <td><?php echo $order['orderStatus']; ?></td>
          <td>
            <a href="view-order-detail.php?viewid=<?php echo $order['orderID']; ?>" class="btn btn-primary btn-sm">
              <i class="fas fa-eye"></i>
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php include(dirname(__DIR__) . "/includes/footer.php"); ?>

