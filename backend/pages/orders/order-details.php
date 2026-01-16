<?php
require_once __DIR__ . '/../../includes/bootstrap.php';

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
  } else {
    // Fallback to legacy order columns if address record not found
    $addr['flatNumber'] = $order['flatNumber'] ?? null;
    $addr['streetName'] = $order['streetName'] ?? null;
    $addr['area'] = $order['area'] ?? null;
    $addr['landmark'] = $order['landmark'] ?? null;
    $addr['city'] = $order['city'] ?? null;
    $addr['state'] = $order['state'] ?? null;
    $addr['zipcode'] = $order['zipcode'] ?? null;
  }
} else {
  $addr['flatNumber'] = $order['flatNumber'] ?? null;
  $addr['streetName'] = $order['streetName'] ?? null;
  $addr['area'] = $order['area'] ?? null;
  $addr['landmark'] = $order['landmark'] ?? null;
  $addr['city'] = $order['city'] ?? null;
  $addr['state'] = $order['state'] ?? null;
  $addr['zipcode'] = $order['zipcode'] ?? null;
}

$line1Parts = array_values(array_filter([
  $addr['flatNumber'],
  $addr['streetName']
], function($v){ return !is_null($v) && $v !== ''; }));
$line2Parts = array_values(array_filter([
  $addr['area'],
  $addr['landmark']
], function($v){ return !is_null($v) && $v !== ''; }));
$line3LeftParts = array_values(array_filter([
  $addr['city'],
  $addr['state']
], function($v){ return !is_null($v) && $v !== ''; }));
// De-duplicate if city and state are the same (case-insensitive)
if (count($line3LeftParts) === 2 && strcasecmp($line3LeftParts[0], $line3LeftParts[1]) === 0) {
  $line3LeftParts = [$line3LeftParts[0]];
}
$line3 = implode(', ', $line3LeftParts);
$zip = '';
if (isset($addr['zipcode'])) {
  $z = trim((string)$addr['zipcode']);
  if ($z !== '' && $z !== '0000') { $zip = $z; }
}
if ($zip !== '') {
  $line3 .= ($line3 !== '' ? ' - ' : '') . $zip;
}

// If all lines are empty, try final fallback to legacy values (in case of partial data)
if (empty($line1Parts) && empty($line2Parts) && $line3 === '') {
  $legacy = [
    'flatNumber' => $order['flatNumber'] ?? null,
    'streetName' => $order['streetName'] ?? null,
    'area' => $order['area'] ?? null,
    'landmark' => $order['landmark'] ?? null,
    'city' => $order['city'] ?? null,
    'state' => $order['state'] ?? null,
    'zipcode' => $order['zipcode'] ?? null,
  ];
  $line1Parts = array_values(array_filter([$legacy['flatNumber'], $legacy['streetName']], function($v){ return !is_null($v) && $v !== ''; }));
  $line2Parts = array_values(array_filter([$legacy['area'], $legacy['landmark']], function($v){ return !is_null($v) && $v !== ''; }));
  $line3LeftParts = array_values(array_filter([$legacy['city'], $legacy['state']], function($v){ return !is_null($v) && $v !== ''; }));
  $line3 = implode(', ', $line3LeftParts);
  $zip = '';
  if (isset($legacy['zipcode'])) {
    $z = trim((string)$legacy['zipcode']);
    if ($z !== '' && $z !== '0000') { $zip = $z; }
  }
  if ($zip !== '') {
    $line3 .= ($line3 !== '' ? ' - ' : '') . $zip;
  }
}

// If still empty (e.g., legacy columns dropped), try user's default address as a final fallback
if (empty($line1Parts) && empty($line2Parts) && $line3 === '') {
  $defAddrQuery = "SELECT label, flatNumber, streetName, area, landmark, city, state, zipcode FROM addresses WHERE userID = ? AND is_default = 1 LIMIT 1";
  $defAddrResult = executePreparedQuery($defAddrQuery, "i", [$userID]);
  $def = mysqli_fetch_assoc($defAddrResult);
  if ($def) {
    $addressLabel = $def['label'] ?? $addressLabel;
    $line1Parts = array_values(array_filter([$def['flatNumber'] ?? null, $def['streetName'] ?? null], function($v){ return !is_null($v) && $v !== ''; }));
    $line2Parts = array_values(array_filter([$def['area'] ?? null, $def['landmark'] ?? null], function($v){ return !is_null($v) && $v !== ''; }));
    $line3LeftParts = array_values(array_filter([$def['city'] ?? null, $def['state'] ?? null], function($v){ return !is_null($v) && $v !== ''; }));
    $line3 = implode(', ', $line3LeftParts);
    $zip = '';
    if (isset($def['zipcode'])) {
      $z = trim((string)$def['zipcode']);
      if ($z !== '' && $z !== '0000') { $zip = $z; }
    }
    if ($zip !== '') {
      $line3 .= ($line3 !== '' ? ' - ' : '') . $zip;
    }
  }
}

$itemsQuery = "SELECT order_items.*, items.packageName, items.itemImage 
               FROM order_items 
               LEFT JOIN items ON order_items.itemID = items.itemID 
               WHERE order_items.orderID = ?";
$itemsResult = executePreparedQuery($itemsQuery, "i", [$orderID]);

include(__DIR__ . "/../../includes/header.php");
?>


<div class="checkout-container">
  <div class="mb-3">
    <a href="my-orders.php" class="btn btn-outline-secondary btn-sm">
      <i class="fas fa-arrow-left me-2"></i>Back to My Orders
    </a>
  </div>
  
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
      <h4 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Delivery Address</h4>
    </div>
    <div class="checkout-form-body">
      <?php
        $addrLine1 = !empty($line1Parts) ? implode(', ', $line1Parts) : '';
        $addrLine2 = !empty($line2Parts) ? implode(', ', $line2Parts) : '';
        $addrLine3 = $line3;
        $hasAnyAddr = ($addrLine1 !== '' || $addrLine2 !== '' || $addrLine3 !== '');
      ?>

      <p><strong>Full Name:</strong> <?php echo e($order['fullName']); ?></p>
      <?php if (!empty($order['contactNumber'])) : ?>
        <p><strong>Contact Number:</strong> <?php echo e($order['contactNumber']); ?></p>
      <?php endif; ?>

      <?php if ($hasAnyAddr): ?>
        <p><strong>Address:</strong> <?php echo e($addrLine1); ?></p>
        <?php if ($addrLine2 !== ''): ?>
          <p><strong>Additional:</strong> <?php echo e($addrLine2); ?></p>
        <?php endif; ?>
        <?php if ($addrLine3 !== ''): ?>
          <p><strong>City/State/ZIP:</strong> <?php echo e($addrLine3); ?></p>
        <?php endif; ?>
      <?php else: ?>
        <p class="text-muted mb-0">No delivery address on file.</p>
      <?php endif; ?>
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
            <th>Order No.</th>
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

<?php include(__DIR__ . "/../../includes/footer.php"); ?>
