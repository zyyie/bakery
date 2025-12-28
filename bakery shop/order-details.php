<?php
session_start();
include("connect.php");

if(!isset($_SESSION['userID'])){
  header("Location: login.php");
  exit();
}

$orderID = intval($_GET['orderID']);
$orderQuery = "SELECT * FROM orders WHERE orderID = $orderID AND userID = '".$_SESSION['userID']."'";
$orderResult = executeQuery($orderQuery);
$order = mysqli_fetch_assoc($orderResult);

if(!$order){
  header("Location: my-orders.php");
  exit();
}

$itemsQuery = "SELECT order_items.*, items.packageName, items.itemImage 
               FROM order_items 
               LEFT JOIN items ON order_items.itemID = items.itemID 
               WHERE order_items.orderID = $orderID";
$itemsResult = executeQuery($itemsQuery);

include("includes/header.php");
?>


<div class="container my-5">
  <div class="card shadow mb-4">
    <div class="card-body">
      <h5>Order Information</h5>
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

  <div class="card shadow mb-4">
    <div class="card-body">
      <h5 class="text-primary">Delivery Address</h5>
      <p><?php echo $order['flatNumber']; ?>, <?php echo $order['streetName']; ?></p>
      <p><?php echo $order['area']; ?>, <?php echo $order['landmark']; ?></p>
      <p><?php echo $order['city']; ?>, <?php echo $order['state']; ?> - <?php echo $order['zipcode']; ?></p>
    </div>
  </div>

  <div class="card shadow">
    <div class="card-body">
      <h5 class="text-primary">Item Purchased</h5>
      <table class="table table-striped">
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
              <img src="<?php echo $item['itemImage'] ? 'uploads/'.$item['itemImage'] : 'https://via.placeholder.com/80'; ?>" 
                   style="width: 80px; height: 80px; object-fit: cover;">
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

<?php include("includes/footer.php"); ?>

