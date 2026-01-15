<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';

// Auto-deliver orders: Check and update orders with delivery date today or past
$today = date('Y-m-d');
$autoDeliverQuery = "UPDATE orders 
                     SET orderStatus = 'Delivered' 
                     WHERE deliveryDate IS NOT NULL 
                     AND DATE(deliveryDate) <= ? 
                     AND orderStatus = 'On The Way'";
executePreparedUpdate($autoDeliverQuery, "s", [$today]);

include(dirname(__DIR__) . "/includes/header.php");

$orderID = intval($_GET['viewid']);
$orderQuery = "SELECT * FROM orders WHERE orderID = ?";
$orderResult = executePreparedQuery($orderQuery, "i", [$orderID]);
$order = mysqli_fetch_assoc($orderResult);

if (!$order) {
  echo '<div class="alert alert-danger">Order not found.</div>';
  include(dirname(__DIR__) . "/includes/footer.php");
  exit;
}

$addr = [
  'flatNumber' => null,
  'streetName' => null,
  'area' => null,
  'landmark' => null,
  'city' => null,
  'state' => null,
  'zipcode' => null,
];
$addressLabel = null;

if (!empty($order['shippingAddressID'])) {
  $addrQuery = "SELECT label, flatNumber, streetName, area, landmark, city, state, zipcode FROM addresses WHERE addressID = ?";
  $addrResult = executePreparedQuery($addrQuery, "i", [intval($order['shippingAddressID'])]);
  $addrRow = mysqli_fetch_assoc($addrResult);
  if ($addrRow) {
    $addressLabel = $addrRow['label'] ?? null;
    unset($addrRow['label']);
    $addr = array_merge($addr, $addrRow);
  }
}

if ($addr['flatNumber'] === null && $addr['streetName'] === null && $addr['area'] === null && $addr['landmark'] === null && $addr['city'] === null && $addr['state'] === null && $addr['zipcode'] === null) {
  $defAddrQuery = "SELECT label, flatNumber, streetName, area, landmark, city, state, zipcode FROM addresses WHERE userID = ? AND is_default = 1 LIMIT 1";
  $defAddrResult = executePreparedQuery($defAddrQuery, "i", [intval($order['userID'] ?? 0)]);
  $def = mysqli_fetch_assoc($defAddrResult);
  if ($def) {
    $addressLabel = $def['label'] ?? $addressLabel;
    unset($def['label']);
    $addr = array_merge($addr, $def);
  }
}

if ($addr['flatNumber'] === null && $addr['streetName'] === null && $addr['area'] === null && $addr['landmark'] === null && $addr['city'] === null && $addr['state'] === null && $addr['zipcode'] === null) {
  $addr['flatNumber'] = $order['flatNumber'] ?? null;
  $addr['streetName'] = $order['streetName'] ?? null;
  $addr['area'] = $order['area'] ?? null;
  $addr['landmark'] = $order['landmark'] ?? null;
  $addr['city'] = $order['city'] ?? null;
  $addr['state'] = $order['state'] ?? null;
  $addr['zipcode'] = $order['zipcode'] ?? null;
}

$flatNumber = (string)($addr['flatNumber'] ?? '');
$streetName = (string)($addr['streetName'] ?? '');
$area = (string)($addr['area'] ?? '');
$landmark = (string)($addr['landmark'] ?? '');
$city = (string)($addr['city'] ?? '');
$state = (string)($addr['state'] ?? '');
$zipcode = (string)($addr['zipcode'] ?? '');

$legacyAddress = $order['deliveryAddress'] ?? ($order['address'] ?? ($order['fullAddress'] ?? ''));

$itemsQuery = "SELECT order_items.*, items.* 
               FROM order_items 
               LEFT JOIN items ON order_items.itemID = items.itemID 
               WHERE order_items.orderID = ?";
$itemsResult = executePreparedQuery($itemsQuery, "i", [$orderID]);
?>

<div class="header-bar">
  <h2 class="mb-0" style="color: #333; font-weight: 600;">View Order Details</h2>
</div>

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
        <p><strong>Shipping Address ID:</strong> <?php echo htmlspecialchars((string)($order['shippingAddressID'] ?? '')); ?></p>
        <?php if (!empty($addressLabel)): ?>
          <p><strong>Label:</strong> <?php echo htmlspecialchars($addressLabel); ?></p>
        <?php endif; ?>
        <p><strong>Flat Number:</strong> <?php echo htmlspecialchars($flatNumber !== '' ? $flatNumber : 'N/A'); ?></p>
        <p><strong>Street Name:</strong> <?php echo htmlspecialchars($streetName !== '' ? $streetName : 'N/A'); ?></p>
        <p><strong>Area:</strong> <?php echo htmlspecialchars($area !== '' ? $area : 'N/A'); ?></p>
        <p><strong>Landmark:</strong> <?php echo htmlspecialchars($landmark !== '' ? $landmark : 'N/A'); ?></p>
        <p><strong>City:</strong> <?php echo htmlspecialchars($city !== '' ? $city : 'N/A'); ?></p>
        <p><strong>State:</strong> <?php echo htmlspecialchars($state !== '' ? $state : 'N/A'); ?></p>
        <p><strong>Zipcode:</strong> <?php echo htmlspecialchars($zipcode !== '' ? $zipcode : 'N/A'); ?></p>

        <?php if ($legacyAddress !== '' && $flatNumber === '' && $streetName === '' && $area === '' && $city === '' && $state === '' && $zipcode === ''): ?>
          <p class="mb-0"><strong>Address:</strong> <?php echo htmlspecialchars($legacyAddress); ?></p>
        <?php endif; ?>
      </div>
      <div class="col-md-6">
        <p><strong>Order Date:</strong> <?php echo $order['orderDate']; ?></p>
        <p>
          <strong>Delivery Date:</strong> 
          <input type="date" 
                 class="form-control form-control-sm d-inline-block" 
                 id="deliveryDateInput" 
                 value="<?php echo !empty($order['deliveryDate']) ? date('Y-m-d', strtotime($order['deliveryDate'])) : ''; ?>" 
                 min="<?php echo date('Y-m-d'); ?>"
                 style="width: auto; display: inline-block; margin-left: 10px;"
                 data-orderid="<?php echo $orderID; ?>">
          <button type="button" class="btn btn-sm btn-primary ms-2" id="saveDeliveryDateBtn">
            <i class="fas fa-save"></i> Save
          </button>
        </p>
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
            <?php 
            // Use product_image_url function if available, otherwise fallback
            if (function_exists('product_image_url')) {
              $imageUrl = product_image_url($item, 2); // depth 2 for admin (admin is 2 levels deep)
            } else {
              $imageUrl = $item['itemImage'] ? '../../uploads/'.$item['itemImage'] : '../../frontend/images/placeholder.jpg';
            }
            ?>
            <img src="<?php echo $imageUrl; ?>" 
                 alt="<?php echo htmlspecialchars($item['packageName']); ?>"
                 style="width: 80px; height: 80px; object-fit: cover; border-radius: 4px;">
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
          <div class="mb-3">
            <label class="form-label">Estimated Delivery Date:</label>
            <input type="date" class="form-control" name="deliveryDate" value="<?php echo !empty($order['deliveryDate']) ? date('Y-m-d', strtotime($order['deliveryDate'])) : ''; ?>" min="<?php echo date('Y-m-d'); ?>">
            <small class="text-muted">Set the estimated delivery date for this order</small>
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

<?php include(dirname(__DIR__) . "/includes/footer.php"); ?>

<script>
// Save delivery date via AJAX
document.getElementById('saveDeliveryDateBtn')?.addEventListener('click', function() {
  const deliveryDateInput = document.getElementById('deliveryDateInput');
  const orderID = deliveryDateInput.getAttribute('data-orderid');
  const deliveryDate = deliveryDateInput.value;
  
  if(!deliveryDate) {
    alert('Please select a delivery date');
    return;
  }
  
  fetch('update-delivery-date.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: `orderID=${orderID}&deliveryDate=${encodeURIComponent(deliveryDate)}`
  })
  .then(response => response.json())
  .then(data => {
    if(data.success) {
      alert('Delivery date updated successfully!');
      location.reload();
    } else {
      alert('Error updating delivery date: ' + (data.message || 'Unknown error'));
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Error updating delivery date. Please try again.');
  });
});
</script>

