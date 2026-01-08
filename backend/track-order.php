<?php
require_once __DIR__ . '/includes/bootstrap.php';

if(!isset($_SESSION['userID'])){
  header("Location: login.php");
  exit();
}

$orderID = intval($_GET['orderID']);
$userID = intval($_SESSION['userID']);
$orderQuery = "SELECT * FROM orders WHERE orderID = ? AND userID = ?";
$orderResult = executePreparedQuery($orderQuery, "ii", [$orderID, $userID]);
$order = mysqli_fetch_assoc($orderResult);

if(!$order){
  header("Location: my-orders.php");
  exit();
}

include(__DIR__ . "/includes/header.php");
?>


<div class="container my-5">
  <div class="card shadow">
    <div class="card-body">
      <h4>Order Number: <?php echo $order['orderNumber']; ?></h4>
      <hr>
      <div class="row">
        <div class="col-md-12">
          <div class="progress" style="height: 30px;">
            <?php
            $statuses = ['Still Pending', 'Confirmed', 'On The Way', 'Delivered'];
            $currentIndex = array_search($order['orderStatus'], $statuses);
            if($currentIndex === false) $currentIndex = 0;
            $percentage = ($currentIndex + 1) * 25;
            ?>
            <div class="progress-bar bg-<?php 
              if($order['orderStatus'] == 'Delivered') echo 'success';
              elseif($order['orderStatus'] == 'On The Way') echo 'info';
              elseif($order['orderStatus'] == 'Confirmed') echo 'primary';
              else echo 'warning';
            ?>" role="progressbar" style="width: <?php echo $percentage; ?>%">
              <?php echo $order['orderStatus']; ?>
            </div>
          </div>
        </div>
      </div>
      <div class="mt-4">
        <p><strong>Current Status:</strong> <?php echo $order['orderStatus']; ?></p>
        <?php if($order['remark']): ?>
        <p><strong>Remark:</strong> <?php echo $order['remark']; ?></p>
        <?php endif; ?>
        <p><strong>Order Date:</strong> <?php echo $order['orderDate']; ?></p>
        <?php if(!empty($order['deliveryDate']) && $order['orderStatus'] != 'Still Pending'): ?>
        <p><strong>Delivery Date:</strong> <?php echo $order['deliveryDate']; ?></p>
        <?php endif; ?>
      </div>
      <a href="my-orders.php" class="btn btn-warning">Back to Orders</a>
    </div>
  </div>
</div>

<?php include(__DIR__ . "/includes/footer.php"); ?>

