<?php
session_start();
include("../connect.php");
include("includes/header.php");

$orderID = intval($_GET['viewid']);
$orderQuery = "SELECT * FROM orders WHERE orderID = ?";
$orderResult = executePreparedQuery($orderQuery, "i", [$orderID]);
$order = mysqli_fetch_assoc($orderResult);

$itemsQuery = "SELECT order_items.*, items.packageName, items.itemImage 
               FROM order_items 
               LEFT JOIN items ON order_items.itemID = items.itemID 
               WHERE order_items.orderID = ?";
$itemsResult = executePreparedQuery($itemsQuery, "i", [$orderID]);
?>

<h2 class="mb-4">View Order Details</h2>

<div class="card mb-4">
  <div class="card-body">
    <h5>Order Information</h5>
    <p><strong>Order Number:</strong> <?php echo $order['orderNumber']; ?></p>
    <p><strong>Full Name:</strong> <?php echo $order['fullName']; ?></p>
    <p><strong>Contact Number:</strong> <?php echo $order['contactNumber']; ?></p>
  </div>
</div>

<div class="card mb-4">
  <div class="card-body">
    <h5 class="text-primary">Delivery Address</h5>
    <div class="row">
      <div class="col-md-6">
        <p><strong>Order Date:</strong> <?php echo $order['deliveryDate']; ?></p>
        <p><strong>Flat Number:</strong> <?php echo $order['flatNumber']; ?></p>
        <p><strong>Street Name:</strong> <?php echo $order['streetName']; ?></p>
        <p><strong>Area:</strong> <?php echo $order['area']; ?></p>
        <p><strong>Landmark:</strong> <?php echo $order['landmark']; ?></p>
      </div>
      <div class="col-md-6">
        <p><strong>City:</strong> <?php echo $order['city']; ?></p>
        <p><strong>Zipcode:</strong> <?php echo $order['zipcode']; ?></p>
        <p><strong>State:</strong> <?php echo $order['state']; ?></p>
        <p><strong>Order Date:</strong> <?php echo $order['orderDate']; ?></p>
        <p><strong>Order Final Status:</strong> <?php echo $order['orderStatus']; ?></p>
      </div>
    </div>
  </div>
</div>

<div class="card mb-4">
  <div class="card-body">
    <h5 class="text-primary">Item Purchased</h5>
    <table class="table table-striped">
      <thead>
        <tr>
          <th>S.NO</th>
          <th>Product Image</th>
          <th>Product Name</th>
          <th>Order Number</th>
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
            <img src="<?php echo $item['itemImage'] ? '../uploads/'.$item['itemImage'] : 'https://via.placeholder.com/80'; ?>" 
                 style="width: 80px; height: 80px; object-fit: cover;">
          </td>
          <td><?php echo $item['packageName']; ?></td>
          <td><?php echo $order['orderNumber']; ?></td>
          <td>₱<?php echo $item['unitPrice']; ?></td>
          <td><?php echo $item['quantity']; ?></td>
          <td>₱<?php echo $item['totalPrice']; ?></td>
        </tr>
        <?php endwhile; ?>
        <tr>
          <td colspan="6" class="text-end"><strong>Total:</strong></td>
          <td><strong>₱<?php echo number_format($grandTotal, 2); ?></strong></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#actionModal">
  TAKE ACTION
</button>

<!-- Action Modal -->
<div class="modal fade" id="actionModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Take Action</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="update-order-status.php">
        <div class="modal-body">
          <input type="hidden" name="orderID" value="<?php echo $orderID; ?>">
          <div class="mb-3">
            <label class="form-label">Remark:</label>
            <textarea class="form-control" name="remark" rows="3"><?php echo $order['remark']; ?></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Status:</label>
            <select class="form-select" name="orderStatus" required>
              <option value="Still Pending" <?php echo $order['orderStatus'] == 'Still Pending' ? 'selected' : ''; ?>>Still Pending</option>
              <option value="Confirmed" <?php echo $order['orderStatus'] == 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
              <option value="On The Way" <?php echo $order['orderStatus'] == 'On The Way' ? 'selected' : ''; ?>>On The Way</option>
              <option value="Delivered" <?php echo $order['orderStatus'] == 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
              <option value="Cancelled" <?php echo $order['orderStatus'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">CLOSE</button>
          <button type="submit" class="btn btn-info">UPDATE</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include("includes/footer.php"); ?>

