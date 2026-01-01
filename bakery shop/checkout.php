<?php
require_once __DIR__ . '/includes/bootstrap.php';

if(!isset($_SESSION['userID'])){
  header("Location: login.php");
  exit();
}

if(!isset($_SESSION['cart']) || empty($_SESSION['cart'])){
  header("Location: cart.php");
  exit();
}

$error = "";
$success = "";

// Save payment method
if(isset($_POST['savePayment'])){
  $paymentMethod = trim($_POST['paymentMethod']);
  if(in_array($paymentMethod, ['cod', 'paypal'])){
    $_SESSION['checkout_payment'] = $paymentMethod;
    header("Location: checkout.php");
    exit();
  }
}

// Save address information first
if(isset($_POST['saveAddress'])){
  $fullName = trim($_POST['fullName']);
  $contactNumber = trim($_POST['contactNumber']);
  $deliveryDate = trim($_POST['deliveryDate']);
  $flatNumber = trim($_POST['flatNumber']);
  $streetName = trim($_POST['streetName']);
  $landmark = trim($_POST['landmark']);
  $city = trim($_POST['city']);
  $zipcode = trim($_POST['zipcode']);
  
  // Input validation
  if(empty($fullName) || empty($contactNumber) || empty($deliveryDate) || 
     empty($flatNumber) || empty($streetName) || 
     empty($city) || empty($zipcode)){
    $error = "All fields are required!";
  } else {
    // Save address to session
    $_SESSION['checkout_address'] = [
      'fullName' => $fullName,
      'contactNumber' => $contactNumber,
      'deliveryDate' => $deliveryDate,
      'flatNumber' => $flatNumber,
      'streetName' => $streetName,
      'landmark' => $landmark,
      'city' => $city,
      'zipcode' => $zipcode
    ];
    // Redirect to show cart summary
    header("Location: checkout.php");
    exit();
  }
}

// Handle edit address
if(isset($_GET['edit']) && $_GET['edit'] == 1){
  unset($_SESSION['checkout_address']);
  header("Location: checkout.php");
  exit();
}

// Place order
if(isset($_POST['placeOrder'])){
  // Get address from session or POST
  if(isset($_SESSION['checkout_address'])){
    $address = $_SESSION['checkout_address'];
    $fullName = $address['fullName'];
    $contactNumber = $address['contactNumber'];
    $deliveryDate = $address['deliveryDate'];
    $flatNumber = $address['flatNumber'];
    $streetName = $address['streetName'];
    $landmark = $address['landmark'];
    $city = $address['city'];
    $zipcode = $address['zipcode'];
    $area = '';
    $state = '';
  } else {
    // Fallback to POST data
    $fullName = trim($_POST['fullName']);
    $contactNumber = trim($_POST['contactNumber']);
    $deliveryDate = trim($_POST['deliveryDate']);
    $flatNumber = trim($_POST['flatNumber']);
    $streetName = trim($_POST['streetName']);
    $landmark = trim($_POST['landmark']);
    $city = trim($_POST['city']);
    $zipcode = trim($_POST['zipcode']);
    $area = '';
    $state = '';
  }
  
  $userID = intval($_SESSION['userID']);
  
  // Input validation
  if(empty($fullName) || empty($contactNumber) || empty($deliveryDate) || 
     empty($flatNumber) || empty($streetName) || 
     empty($city) || empty($zipcode)){
    $error = "All fields are required!";
  } else {
    // Generate order number
    $orderNumber = rand(100000000, 999999999);
    
    // Insert order using prepared statement
    $orderQuery = "INSERT INTO orders (orderNumber, userID, fullName, contactNumber, deliveryDate, 
                  flatNumber, streetName, area, landmark, city, zipcode, state, orderStatus) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Still Pending')";
    
    $orderResult = executePreparedUpdate($orderQuery, "sissssssssss", [
      $orderNumber, $userID, $fullName, $contactNumber, $deliveryDate,
      $flatNumber, $streetName, $area, $landmark, $city, $zipcode, $state
    ]);
    
    if($orderResult !== false){
      $orderID = mysqli_insert_id($GLOBALS['conn']);
      
      // Insert order items using prepared statements
      foreach($_SESSION['cart'] as $itemID => $quantity){
        $itemID = intval($itemID);
        $quantity = intval($quantity);
        
        // Get item price using prepared statement
        $itemQuery = "SELECT price FROM items WHERE itemID = ?";
        $itemResult = executePreparedQuery($itemQuery, "i", [$itemID]);
        
        if($itemResult && mysqli_num_rows($itemResult) > 0){
          $item = mysqli_fetch_assoc($itemResult);
          $unitPrice = floatval($item['price']);
          $totalPrice = $unitPrice * $quantity;
          
          // Insert order item using prepared statement
          $orderItemQuery = "INSERT INTO order_items (orderID, itemID, quantity, unitPrice, totalPrice) 
                            VALUES (?, ?, ?, ?, ?)";
          executePreparedUpdate($orderItemQuery, "iiidd", [$orderID, $itemID, $quantity, $unitPrice, $totalPrice]);
        }
      }
      
      // Clear cart and address
      unset($_SESSION['cart']);
      unset($_SESSION['checkout_address']);
      $success = "Order placed successfully! Order Number: $orderNumber";
    } else {
      $error = "Failed to place order!";
    }
  }
}

// Get user info using prepared statement
$userID = intval($_SESSION['userID']);
$userQuery = "SELECT * FROM users WHERE userID = ?";
$userResult = executePreparedQuery($userQuery, "i", [$userID]);
$user = mysqli_fetch_assoc($userResult);

// Calculate cart total using prepared statements
$cartTotal = 0;
foreach($_SESSION['cart'] as $itemID => $quantity){
  $itemID = intval($itemID);
  $itemQuery = "SELECT price FROM items WHERE itemID = ?";
  $itemResult = executePreparedQuery($itemQuery, "i", [$itemID]);
  
  if($itemResult && mysqli_num_rows($itemResult) > 0){
    $item = mysqli_fetch_assoc($itemResult);
    $cartTotal += floatval($item['price']) * intval($quantity);
  }
}

include("includes/header.php");
?>


<div class="container my-5">
  <?php if($error): ?>
  <div class="alert alert-danger"><?php echo $error; ?></div>
  <?php endif; ?>
  
  <?php if($success): ?>
  <div class="alert alert-success"><?php echo $success; ?> <a href="my-orders.php">View Orders</a></div>
  <?php else: ?>
  
  <?php 
  // Check if address is already saved in session
  $hasAddress = isset($_SESSION['checkout_address']);
  $address = $hasAddress ? $_SESSION['checkout_address'] : [];
  ?>
  
  <?php if(!$hasAddress): ?>
  <!-- Address Form - Show First -->
  <div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">
      <div class="card shadow mb-4">
        <div class="card-body">
          <h4 class="text-danger mb-4">Fill The Following Detail</h4>
          <form method="POST">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Delivery Date</label>
                <input type="date" class="form-control" name="deliveryDate" required>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" class="form-control" name="fullName" value="<?php echo $user['fullName']; ?>" required>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Contact Number</label>
                <input type="text" class="form-control" name="contactNumber" value="<?php echo $user['mobileNumber']; ?>" required>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Flat or Building Number</label>
                <input type="text" class="form-control" name="flatNumber" required>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Street Name</label>
                <input type="text" class="form-control" name="streetName" required>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Landmark</label>
                <input type="text" class="form-control" name="landmark" required>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">City</label>
                <input type="text" class="form-control" name="city" required>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Zip Code</label>
                <input type="text" class="form-control" name="zipcode" required>
              </div>
            </div>
            <button type="submit" name="saveAddress" class="btn btn-warning w-100 mt-3">Continue to Order Summary</button>
          </form>
        </div>
      </div>
    </div>
  </div>
  
  <?php else: ?>
  <!-- Cart Summary - TikTok Style Format -->
  <div class="checkout-container">
    <!-- Delivery Address Section - TikTok Style -->
    <div class="checkout-section">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 fw-bold">Delivery Address</h5>
        <a href="checkout.php?edit=1" class="text-primary text-decoration-none">
          <i class="fas fa-chevron-right"></i>
        </a>
      </div>
      <div class="delivery-info">
        <div class="d-flex align-items-start mb-2">
          <i class="fas fa-map-marker-alt text-danger me-2 mt-1"></i>
          <div class="flex-grow-1">
            <div class="fw-bold"><?php echo e($address['fullName']); ?></div>
            <div class="text-muted small"><?php echo e($address['contactNumber']); ?></div>
            <div class="mt-2">
              <?php echo e($address['flatNumber'] . ' ' . $address['streetName']); ?><br>
              <?php echo e($address['landmark']); ?><br>
              <?php echo e($address['city'] . ', ' . $address['zipcode']); ?>
            </div>
            <div class="text-muted small mt-2">
              <i class="far fa-calendar-alt me-1"></i>Delivery Date: <?php echo date('M d, Y', strtotime($address['deliveryDate'])); ?>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Store/Order Items Section - TikTok Style -->
    <div class="checkout-section">
      <div class="store-header mb-3">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h5 class="mb-0 fw-bold">KARNEEK Bakery</h5>
            <div class="d-flex align-items-center mt-1">
              <i class="fas fa-star text-warning me-1"></i>
              <span class="small text-muted">Highly rated!</span>
            </div>
          </div>
        </div>
      </div>
      
      <?php
      // Load product images mapping
      $imagesMap = [];
      $imagesJsonPath = __DIR__ . '/product-images.json';
      if (file_exists($imagesJsonPath)) {
        $imagesJson = file_get_contents($imagesJsonPath);
        $imagesMap = json_decode($imagesJson, true) ?: [];
      }
      
      foreach($_SESSION['cart'] as $itemID => $quantity):
        $itemID = intval($itemID);
        $quantity = intval($quantity);
        $itemQuery = "SELECT * FROM items WHERE itemID = ?";
        $itemResult = executePreparedQuery($itemQuery, "i", [$itemID]);
        if($itemResult && ($item = mysqli_fetch_assoc($itemResult))):
          $subtotal = floatval($item['price']) * intval($quantity);
          
          // Get product image
          $productImage = 'https://via.placeholder.com/100';
          $packageName = $item['packageName'];
          if (isset($imagesMap[$packageName])) {
            $productImage = $imagesMap[$packageName];
          } elseif (!empty($item['itemImage'])) {
            $productImage = 'bakery bread image/' . $item['itemImage'];
          }
          
          // Resolve the actual image path
          $productImage = resolveImagePath($productImage);
      ?>
      <div class="order-item">
        <div class="d-flex">
          <img src="<?php echo imageUrl($productImage); ?>" alt="<?php echo e($item['packageName']); ?>" 
               class="order-item-image">
          <div class="flex-grow-1 ms-3">
            <h6 class="mb-1 fw-bold"><?php echo e($item['packageName']); ?></h6>
            <p class="text-muted small mb-2"><?php echo e(substr($item['foodDescription'], 0, 60)); ?>...</p>
            <div class="d-flex justify-content-between align-items-center">
              <div class="quantity-display">
                <span class="text-muted small">Qty: <?php echo $quantity; ?></span>
              </div>
              <div class="item-price fw-bold">₱<?php echo number_format($subtotal, 2); ?></div>
            </div>
          </div>
        </div>
      </div>
      <?php 
        endif;
      endforeach; 
      ?>
    </div>
    
    <!-- Payment Method Section -->
    <div class="checkout-section">
      <h5 class="mb-3 fw-bold">Payment method</h5>
      <?php 
      $selectedPayment = isset($_SESSION['checkout_payment']) ? $_SESSION['checkout_payment'] : 'cod';
      ?>
      <div class="payment-option">
        <label class="payment-label">
          <input type="radio" name="paymentMethod" value="cod" <?php echo ($selectedPayment == 'cod') ? 'checked' : ''; ?> class="payment-radio">
          <div class="payment-content">
            <div class="payment-icon cod-icon">
              <span class="cod-badge">COD</span>
            </div>
            <span class="payment-text">Cash on delivery</span>
            <i class="fas fa-info-circle text-muted ms-2" data-bs-toggle="tooltip" title="Pay when you receive your order"></i>
          </div>
        </label>
      </div>
      <div class="payment-option">
        <label class="payment-label">
          <input type="radio" name="paymentMethod" value="paypal" <?php echo ($selectedPayment == 'paypal') ? 'checked' : ''; ?> class="payment-radio">
          <div class="payment-content">
            <div class="payment-icon paypal-icon">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944.6C5.026.289 5.325.048 5.64.048H16.81c1.811 0 3.025.957 3.288 2.729.12.82.12 1.536-.024 2.12-.12.6-.36 1.12-.72 1.56-.36.44-.84.8-1.44 1.08-.48.24-1.04.4-1.68.48.24.24.48.52.72.84.48.64.84 1.4 1.08 2.28.24.88.36 1.84.36 2.88 0 .88-.12 1.72-.36 2.52-.24.8-.6 1.52-1.08 2.16-.48.64-1.08 1.16-1.8 1.56-.72.4-1.56.6-2.52.6H8.872a.641.641 0 0 0-.633.74l.024.12.12.72.024.12c.048.24.048.48.048.72 0 .24-.024.48-.048.72l-.024.12-.12.72-.024.12a.641.641 0 0 1-.633.74z" fill="#003087"/>
                <path d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944.6C5.026.289 5.325.048 5.64.048H16.81c1.811 0 3.025.957 3.288 2.729.12.82.12 1.536-.024 2.12-.12.6-.36 1.12-.72 1.56-.36.44-.84.8-1.44 1.08-.48.24-1.04.4-1.68.48.24.24.48.52.72.84.48.64.84 1.4 1.08 2.28.24.88.36 1.84.36 2.88 0 .88-.12 1.72-.36 2.52-.24.8-.6 1.52-1.08 2.16-.48.64-1.08 1.16-1.8 1.56-.72.4-1.56.6-2.52.6H8.872a.641.641 0 0 0-.633.74l.024.12.12.72.024.12c.048.24.048.48.048.72 0 .24-.024.48-.048.72l-.024.12-.12.72-.024.12a.641.641 0 0 1-.633.74z" fill="#009CDE"/>
                <path d="M9.64 7.048c-.24-.88-.6-1.64-1.08-2.28-.24-.32-.48-.6-.72-.84.64-.08 1.2-.24 1.68-.48.6-.28 1.08-.64 1.44-1.08.36-.44.6-.96.72-1.56.144-.584.144-1.3.024-2.12C10.835.957 9.621.048 7.81.048H5.64c-.315 0-.614.241-.696.552L2.47 20.597a.641.641 0 0 0 .633.74h4.606a.641.641 0 0 0 .633-.74l-.024-.12-.12-.72-.024-.12c-.024-.24-.048-.48-.048-.72 0-.24.024-.48.048-.72l.024-.12.12-.72.024-.12a.641.641 0 0 1 .633-.74h.204c.96 0 1.8-.2 2.52-.6.72-.4 1.32-.92 1.8-1.56.48-.64.84-1.36 1.08-2.16.24-.8.36-1.64.36-2.52 0-1.04-.12-2-.36-2.88z" fill="#012169"/>
              </svg>
            </div>
            <span class="payment-text">PayPal</span>
          </div>
        </label>
      </div>
    </div>
    
    <!-- Shipping & Order Summary - TikTok Style -->
    <?php
    // Calculate shipping
    $shippingFee = 50.00; // Base shipping fee
    $shippingDiscount = 50.00; // Free shipping discount
    $hasFreeShipping = true; // Always free shipping
    
    $subtotal = $cartTotal;
    $shippingSubtotal = $hasFreeShipping ? 0 : $shippingFee;
    $total = $subtotal + $shippingSubtotal;

    // Estimated shipping / delivery dates (vary based on user's location)
    $shipDate = new DateTime(); // Ship out today
    $estimateStart = (clone $shipDate)->modify('+3 days');
    $estimateEnd   = (clone $shipDate)->modify('+7 days');

    if(isset($user)) {
      $userCity = strtolower(trim($user['city'] ?? ''));
      $userZip  = trim($user['zipcode'] ?? '');

      // Assume store is in Alaminos, Laguna 4001 (near area)
      $isSameCity = ($userCity === 'alaminos' || $userCity === 'laguna') && $userZip === '4001';
      $isSameProvince = (strpos($userCity, 'laguna') !== false || strpos($userCity, 'batangas') !== false);

      if($isSameCity) {
        // Very near: 1–3 days
        $estimateStart = (clone $shipDate)->modify('+1 day');
        $estimateEnd   = (clone $shipDate)->modify('+3 days');
      } elseif($isSameProvince) {
        // Same province / nearby: 2–5 days
        $estimateStart = (clone $shipDate)->modify('+2 days');
        $estimateEnd   = (clone $shipDate)->modify('+5 days');
      } else {
        // Far provinces: 4–9 days
        $estimateStart = (clone $shipDate)->modify('+4 days');
        $estimateEnd   = (clone $shipDate)->modify('+9 days');
      }
    }
    ?>
    <div class="checkout-section">
      <!-- Shipping Subtotal (Collapsible) -->
      <div class="shipping-section">
        <div class="shipping-header" onclick="toggleShipping()">
          <span class="fw-bold">Shipping subtotal</span>
          <i class="fas fa-chevron-up" id="shipping-chevron"></i>
        </div>
        <div class="shipping-details" id="shipping-details">
          <div class="summary-row">
            <span>Shipping fee</span>
            <span>₱<?php echo number_format($shippingFee, 2); ?></span>
          </div>
          <div class="summary-row">
            <span>Shipping discount</span>
            <span class="text-danger">-₱<?php echo number_format($shippingDiscount, 2); ?></span>
          </div>
          <div class="summary-row">
            <span class="fw-bold">Shipping subtotal</span>
            <span class="fw-bold">₱<?php echo number_format($shippingSubtotal, 2); ?></span>
          </div>
        </div>
      </div>
      <div class="mt-2 small text-muted">
        <i class="fas fa-shipping-fast me-1"></i>
        Ships out <strong><?php echo $shipDate->format('M d'); ?></strong>
        · Estimated delivery
        <strong><?php echo $estimateStart->format('M d'); ?></strong>
        - <strong><?php echo $estimateEnd->format('M d'); ?></strong>
      </div>
      
      <!-- Total -->
      <div class="summary-row total-row">
        <span class="fw-bold">Total</span>
        <span class="fw-bold text-danger fs-4">₱<?php echo number_format($total, 2); ?></span>
      </div>
      
      <?php if($hasFreeShipping): ?>
      <div class="free-shipping-badge mt-3">
        <i class="fas fa-truck text-success me-2"></i>
        <span class="text-success fw-bold">Free Shipping</span>
      </div>
      <?php endif; ?>
    </div>
    
    <!-- Place Order Button - TikTok Style -->
    <div class="checkout-footer">
      <form method="POST" class="w-100" id="checkout-form">
        <?php foreach($address as $key => $value): ?>
        <input type="hidden" name="<?php echo $key; ?>" value="<?php echo e($value); ?>">
        <?php endforeach; ?>
        <input type="hidden" name="paymentMethod" id="payment-method-input" value="<?php echo e($selectedPayment); ?>">
        <button type="submit" name="placeOrder" class="btn-place-order">
          PLACE ORDER
        </button>
      </form>
    </div>
  </div>
  <?php endif; ?>
  
  <?php endif; ?>
</div>

<style>
/* TikTok Style Checkout - Desktop/Laptop Optimized */
.checkout-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 30px 20px;
}

.checkout-section {
  background: #fff;
  border-radius: 12px;
  padding: 24px;
  margin-bottom: 20px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.delivery-info {
  background: #f8f9fa;
  border-radius: 8px;
  padding: 20px;
}

.order-item {
  padding: 20px 0;
  border-bottom: 1px solid #e9ecef;
}

.order-item:last-child {
  border-bottom: none;
}

.order-item-image {
  width: 100px;
  height: 100px;
  object-fit: cover;
  border-radius: 8px;
  flex-shrink: 0;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  padding: 14px 0;
  border-bottom: 1px dashed #dee2e6;
  font-size: 15px;
}

.summary-row.total-row {
  border-bottom: none;
  border-top: 2px solid #000;
  margin-top: 12px;
  padding-top: 20px;
  font-size: 18px;
}

.checkout-footer {
  background: #fff;
  padding: 24px;
  margin-top: 20px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.btn-place-order {
  width: 100%;
  background: #ffc107;
  color: #000;
  border: none;
  padding: 18px;
  border-radius: 8px;
  font-weight: 700;
  font-size: 18px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  transition: all 0.3s ease;
}

.btn-place-order:hover {
  background: #ffb300;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(255, 193, 7, 0.4);
}

.store-header h5 {
  font-size: 20px;
}

.order-item h6 {
  font-size: 16px;
}

.delivery-info .fw-bold {
  font-size: 18px;
}

/* Payment Method Styles */
.payment-option {
  margin-bottom: 12px;
  border: 1px solid #e9ecef;
  border-radius: 8px;
  padding: 12px;
  transition: all 0.3s ease;
}

.payment-option:hover {
  border-color: #ffc107;
  background-color: #fffbf0;
}

.payment-label {
  display: flex;
  align-items: center;
  cursor: pointer;
  margin: 0;
  width: 100%;
}

.payment-radio {
  margin-right: 12px;
  width: 20px;
  height: 20px;
  cursor: pointer;
}

.payment-content {
  display: flex;
  align-items: center;
  flex-grow: 1;
}

.payment-icon {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 12px;
  border-radius: 8px;
}

.cod-icon {
  background-color: #28a745;
}

.cod-badge {
  color: white;
  font-weight: bold;
  font-size: 12px;
  padding: 4px 8px;
  border-radius: 4px;
}

.paypal-icon {
  background-color: #f8f9fa;
  padding: 8px;
}

.payment-text {
  flex-grow: 1;
  font-size: 15px;
}

.payment-radio:checked + .payment-content {
  color: #ffc107;
}

.payment-radio:checked ~ .payment-content .payment-icon {
  border: 2px solid #ffc107;
}

/* Shipping Section Styles */
.shipping-section {
  margin-bottom: 20px;
}

.shipping-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  padding: 12px 0;
  border-bottom: 1px solid #e9ecef;
}

.shipping-header:hover {
  color: #ffc107;
}

.shipping-details {
  padding: 12px 0;
  display: block;
}

.shipping-details.hidden {
  display: none;
}

.free-shipping-badge {
  background-color: #d4edda;
  border: 1px solid #c3e6cb;
  border-radius: 8px;
  padding: 12px;
  text-align: center;
}
</style>

<script>
// Toggle shipping details
function toggleShipping() {
  const details = document.getElementById('shipping-details');
  const chevron = document.getElementById('shipping-chevron');
  
  if (details.classList.contains('hidden')) {
    details.classList.remove('hidden');
    chevron.classList.remove('fa-chevron-down');
    chevron.classList.add('fa-chevron-up');
  } else {
    details.classList.add('hidden');
    chevron.classList.remove('fa-chevron-up');
    chevron.classList.add('fa-chevron-down');
  }
}

// Handle payment method selection
document.addEventListener('DOMContentLoaded', function() {
  const paymentRadios = document.querySelectorAll('input[name="paymentMethod"]');
  const paymentInput = document.getElementById('payment-method-input');
  
  // Update hidden input when payment method changes
  paymentRadios.forEach(radio => {
    radio.addEventListener('change', function() {
      if(paymentInput) {
        paymentInput.value = this.value;
      }
    });
  });
  
  // Ensure payment method is set when form is submitted
  const checkoutForm = document.getElementById('checkout-form');
  if(checkoutForm) {
    checkoutForm.addEventListener('submit', function(e) {
      const selectedPayment = document.querySelector('input[name="paymentMethod"]:checked');
      if(selectedPayment && paymentInput) {
        paymentInput.value = selectedPayment.value;
      }
    });
  }
});
</script>

<?php include("includes/footer.php"); ?>

