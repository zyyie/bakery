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

$itemsQuery = "SELECT order_items.*, items.packageName, items.itemImage 
               FROM order_items 
               LEFT JOIN items ON order_items.itemID = items.itemID 
               WHERE order_items.orderID = ?";
$itemsResult = executePreparedQuery($itemsQuery, "i", [$orderID]);

include(__DIR__ . "/includes/header.php");
?>


<div class="checkout-container">
  <div class="checkout-header">
    <h1>Order Details</h1>
    <p>Order #<?php echo $order['orderNumber']; ?></p>
  </div>

  <div class="checkout-form-card mb-4">
    <div class="checkout-form-header">
      <h4><i class="fas fa-info-circle me-2"></i>Order Information</h4>
    </div>
    <div class="checkout-form-body">
      <p><strong>Order Number:</strong> <?php echo $order['orderNumber']; ?></p>
      <p><strong>Full Name:</strong> <?php echo $order['fullName']; ?></p>
      <p><strong>Contact Number:</strong> <?php echo $order['contactNumber']; ?></p>
      <p><strong>Order Status:</strong> 
        <span class="badge 
          <?php 
          if($order['orderStatus'] == 'Delivered') echo 'bg-success';
          elseif($order['orderStatus'] == 'On The Way') echo 'bg-info';
          elseif($order['orderStatus'] == 'Confirmed') echo 'bg-primary';
          else echo 'bg-warning';
          ?>">
          <?php echo $order['orderStatus']; ?>
        </span>
      </p>
    </div>
  </div>

  <div class="checkout-form-card mb-4">
    <div class="checkout-form-header">
      <h4><i class="fas fa-map-marker-alt me-2"></i>Delivery Address</h4>
    </div>
    <div class="checkout-form-body">
      <p><?php echo $order['flatNumber']; ?>, <?php echo $order['streetName']; ?></p>
      <p><?php echo $order['area']; ?>, <?php echo $order['landmark']; ?></p>
      <p><?php echo $order['city']; ?>, <?php echo $order['state']; ?> - <?php echo $order['zipcode']; ?></p>
    </div>
  </div>

  <div class="checkout-form-card">
    <div class="checkout-form-header">
      <h4><i class="fas fa-box me-2"></i>Items Purchased</h4>
    </div>
    <div class="checkout-form-body">
      <div class="table-responsive">
      <table class="table table-striped align-middle">
        <thead>
          <tr>
            <th>S.NO</th>
            <th>Product Image</th>
            <th>Product Name</th>
            <th>Unit Price</th>
            <th>Quantity</th>
            <th>Total Price</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $count = 1;
          $grandTotal = 0;
          while($item = mysqli_fetch_assoc($itemsResult)):
            $grandTotal += $item['totalPrice'];
          ?>
          <tr>
            <td><?php echo $count++; ?></td>
            <td>
              <img src="<?php echo product_image_url($item, 1); ?>" alt="<?php echo e($item['packageName']); ?>" style="width: 80px; height: 80px; object-fit: cover;">
            </td>
            <td><?php echo $item['packageName']; ?></td>
            <td>₱<?php echo $item['unitPrice']; ?></td>
            <td><?php echo $item['quantity']; ?></td>
            <td>₱<?php echo $item['totalPrice']; ?></td>
          </tr>
          <?php endwhile; ?>
          <tr>
            <td colspan="5" class="text-end"><strong>Total:</strong></td>
            <td><strong>₱<?php echo number_format($grandTotal, 2); ?></strong></td>
          </tr>
        </tbody>
      </table>
      </div>
    </div>
  </div>
</div>

<?php include(__DIR__ . "/includes/footer.php"); ?>

