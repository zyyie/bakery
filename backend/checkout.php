<?php
require_once __DIR__ . '/includes/bootstrap.php';

if(!isset($_SESSION['userID'])){
  header("Location: login.php");
  exit();
}

// Check if user has valid mobile number
$userID = intval($_SESSION['userID']);
if(!userHasValidMobileNumber($userID)){
  $_SESSION['redirect_after_mobile_update'] = 'checkout.php';
  header("Location: update-mobile.php");
  exit();
}

if(!isset($_SESSION['cart']) || empty($_SESSION['cart'])){
  header("Location: cart.php");
  exit();
}

$error = "";
$success = "";

// Handle coupon selection (AJAX endpoint)
if(isset($_POST['applyCoupon']) && isset($_POST['ajax'])){
  header('Content-Type: application/json');
  $couponCode = trim($_POST['couponCode'] ?? '');
  if(!empty($couponCode)){
    $_SESSION['selected_coupon'] = $couponCode;
    echo json_encode(['success' => true, 'message' => 'Coupon applied successfully']);
  } else {
    unset($_SESSION['selected_coupon']);
    echo json_encode(['success' => true, 'message' => 'Coupon removed']);
  }
  exit();
}

// Handle coupon selection (legacy POST redirect - for non-AJAX)
if(isset($_POST['applyCoupon']) && !isset($_POST['ajax'])){
  $couponCode = trim($_POST['couponCode'] ?? '');
  if(!empty($couponCode)){
    $_SESSION['selected_coupon'] = $couponCode;
    // Redirect to refresh and validate coupon
    header("Location: checkout.php");
    exit();
  } else {
    unset($_SESSION['selected_coupon']);
    header("Location: checkout.php");
    exit();
  }
}

// Handle coupon removal
if(isset($_GET['removeCoupon'])){
  unset($_SESSION['selected_coupon']);
  header("Location: checkout.php");
  exit();
}

// Define available coupons
$availableCoupons = [
  'FREESHIP50' => ['name' => 'Free Shipping', 'type' => 'free_shipping', 'min_order' => 50, 'description' => 'Free shipping on orders over ₱50'],
  'FREESHIP100' => ['name' => 'Free Shipping', 'type' => 'free_shipping', 'min_order' => 100, 'description' => 'Free shipping on orders over ₱100'],
  'FREESHIP200' => ['name' => 'Free Shipping', 'type' => 'free_shipping', 'min_order' => 200, 'description' => 'Free shipping on orders over ₱200'],
];

// Get selected coupon
$selectedCouponCode = $_SESSION['selected_coupon'] ?? null;
$selectedCoupon = null;
$baseShippingFee = 50.00; // Default shipping fee before coupons
$shippingFee = $baseShippingFee;

if($selectedCouponCode && isset($availableCoupons[$selectedCouponCode])){
  $selectedCoupon = $availableCoupons[$selectedCouponCode];
  // Check if order meets minimum requirement
  $cart = $_SESSION['cart'] ?? [];
  $cartSubtotalCheck = 0;
  $totalQuantity = 0;
  foreach($cart as $itemID => $quantity){
    $itemID = intval($itemID);
    $quantity = intval($quantity);
    $itemQuery = "SELECT price FROM items WHERE itemID = ?";
    $itemResult = executePreparedQuery($itemQuery, "i", [$itemID]);
    if($itemResult && mysqli_num_rows($itemResult) > 0){
      $item = mysqli_fetch_assoc($itemResult);
      $cartSubtotalCheck += floatval($item['price']) * $quantity;
      $totalQuantity += $quantity;
    }
  }

  // Rule 1: free shipping coupons require at least 3 items in cart (any coupon)
  if($totalQuantity <= 2){
    $error = "Free shipping coupons are only available when you purchase at least 3 items.";
    $selectedCoupon = null;
    unset($_SESSION['selected_coupon']);
  }
  // Rule 2: bigger coupons (FREESHIP100, FREESHIP200) only for very large orders (e.g. 100+ items)
  elseif(in_array($selectedCouponCode, ['FREESHIP100','FREESHIP200'], true) && $totalQuantity < 100){
    $error = "This shipping coupon is only available for bulk orders of at least 100 packs/boxes. For smaller orders, please use the ₱50 free-shipping coupon.";
    $selectedCoupon = null;
    unset($_SESSION['selected_coupon']);
  }
  // Rule 3: check minimum amount for any remaining valid coupon
  elseif($cartSubtotalCheck >= $selectedCoupon['min_order']){
    $shippingFee = 0; // Free shipping
  } else {
    // Coupon doesn't apply if minimum not met - show message
    $error = "Coupon {$selectedCouponCode} requires a minimum order of ₱" . number_format($selectedCoupon['min_order'], 2) . ". Your current subtotal is ₱" . number_format($cartSubtotalCheck, 2) . ".";
    $selectedCoupon = null; // Coupon doesn't apply if minimum not met
    unset($_SESSION['selected_coupon']);
  }
}

// Place order
if(isset($_POST['placeOrder'])){
  $fullName = trim($_POST['fullName']);
  $contactNumber = trim($_POST['contactNumber']);
  // Delivery date will be set by admin, not by user
  $deliveryDate = null;
  $flatNumber = trim($_POST['flatNumber']);
  $streetName = trim($_POST['streetName']);
  $landmark = trim($_POST['landmark']);
  $city = trim($_POST['city']);
  $zipcode = trim($_POST['zipcode']);
  $paymentMethod = trim($_POST['paymentMethod'] ?? '');
  $paypalOrderID = trim($_POST['paypalOrderID'] ?? '');
  $userID = intval($_SESSION['userID']);
  
  // Enhanced input validation
  $errors = [];
  
  if(empty($fullName)) $errors[] = "Full name is required";
  if(empty($contactNumber)) $errors[] = "Contact number is required";
  if(!isValidMobileNumber($contactNumber)) $errors[] = "Please enter a valid mobile number (7-15 digits). It should not be an email address.";
  if(empty($flatNumber)) $errors[] = "Flat/Building number is required";
  if(empty($streetName)) $errors[] = "Street name is required";
  if(empty($city)) $errors[] = "City is required";
  if(empty($zipcode)) $errors[] = "Zip code is required";
  if(!preg_match('/^\d{4,6}$/', preg_replace('/\s/', '', $zipcode))) $errors[] = "Invalid zip code format (4-6 digits)";
  if(empty($paymentMethod)) $errors[] = "Payment method is required";
  if($paymentMethod === 'paypal' && empty($paypalOrderID)) $errors[] = "PayPal payment is required";
  
  if(!empty($errors)) {
    $error = implode('<br>', $errors);
  } else {
    $cart = $_SESSION['cart'] ?? [];

    $hasInventory = false;
    // Check if inventory table exists
    try {
        $invRes = executePreparedQuery("SHOW TABLES LIKE 'inventory'", "", []);
        if ($invRes && mysqli_num_rows($invRes) > 0) {
            $hasInventory = true;
        }
    } catch (Exception $e) {
        error_log("Inventory check error: " . $e->getMessage());
        $hasInventory = false;
    }

    if ($hasInventory) {
      foreach ($cart as $itemID => $quantity) {
        $itemID = intval($itemID);
        $quantity = intval($quantity);

        $stockRes = executePreparedQuery("SELECT stock_qty FROM inventory WHERE itemID = ?", "i", [$itemID]);
        $stock = null;
        if ($stockRes && mysqli_num_rows($stockRes) > 0) {
            $r = mysqli_fetch_assoc($stockRes);
            $stock = intval($r['stock_qty']);
        }

        if ($quantity <= 0) {
          continue;
        }

        if ($stock !== null && $stock < $quantity) {
          $error = "Some items are out of stock or do not have enough stock to fulfill your order.";
          break;
        }
      }
    }

    if ($error) {
      // Do not place order if stock is insufficient
    } else {
    // Generate order number
    $orderNumber = rand(100000000, 999999999);

    $conn = $GLOBALS['conn'];
    mysqli_begin_transaction($conn);
    
    // Fetch actual columns in orders table and build INSERT accordingly
    $ordersColsRes = executePreparedQuery("SHOW COLUMNS FROM orders", "", []);
    $existingCols = [];
    if ($ordersColsRes) {
      while ($col = mysqli_fetch_assoc($ordersColsRes)) { $existingCols[] = $col['Field']; }
    }
    // Candidate columns in the order we want to insert
    $candidates = [
      ['name'=>'orderNumber','type'=>'s','value'=>$orderNumber],
      ['name'=>'userID','type'=>'i','value'=>$userID],
      ['name'=>'fullName','type'=>'s','value'=>$fullName],
      ['name'=>'contactNumber','type'=>'s','value'=>$contactNumber],
      ['name'=>'deliveryDate','type'=>'s','value'=>$deliveryDate], // Will be set by admin later
      ['name'=>'flatNumber','type'=>'s','value'=>$flatNumber],
      ['name'=>'streetName','type'=>'s','value'=>$streetName],
      ['name'=>'landmark','type'=>'s','value'=>$landmark],
      ['name'=>'city','type'=>'s','value'=>$city],
      ['name'=>'zipcode','type'=>'s','value'=>$zipcode],
      ['name'=>'paymentMethod','type'=>'s','value'=>$paymentMethod],
      ['name'=>'paypalOrderID','type'=>'s','value'=>$paypalOrderID],
      ['name'=>'orderStatus','type'=>'s','value'=>'Still Pending'],
    ];
    $orderCols = [];
    $types = '';
    $params = [];
    foreach ($candidates as $c) {
      if (in_array($c['name'], $existingCols, true)) {
        $orderCols[] = $c['name'];
        $types .= $c['type'];
        $params[] = $c['value'];
      }
    }
    $placeholders = rtrim(str_repeat('?, ', count($orderCols)), ', ');
    $orderQuery = 'INSERT INTO orders (' . implode(',', $orderCols) . ') VALUES (' . $placeholders . ')';
    $orderResult = executePreparedUpdate($orderQuery, $types, $params);
    
    if($orderResult !== false){
      $orderID = mysqli_insert_id($GLOBALS['conn']);
      
      // Insert order items using prepared statements
      // Detect optional columns on order_items
      $oiHasUnit = false; $oiHasTotal = false;
      $colUnit = executePreparedQuery("SHOW COLUMNS FROM order_items LIKE 'unitPrice'", "", []);
      if ($colUnit && mysqli_num_rows($colUnit) > 0) { $oiHasUnit = true; }
      $colTotal = executePreparedQuery("SHOW COLUMNS FROM order_items LIKE 'totalPrice'", "", []);
      if ($colTotal && mysqli_num_rows($colTotal) > 0) { $oiHasTotal = true; }

      $insertedCount = 0;
      foreach($cart as $itemID => $quantity){
        $itemID = intval($itemID);
        $quantity = intval($quantity);
        
        // Get item price using prepared statement
        $itemQuery = "SELECT price FROM items WHERE itemID = ?";
        $itemResult = executePreparedQuery($itemQuery, "i", [$itemID]);
        
        if($itemResult && mysqli_num_rows($itemResult) > 0){
          $item = mysqli_fetch_assoc($itemResult);
          $unitPrice = floatval($item['price']);
          $totalPrice = $unitPrice * $quantity;
          
          // Build order_items insert based on available columns
          $oiCols = ['orderID','itemID','quantity'];
          $oiTypes = 'iii';
          $oiParams = [$orderID, $itemID, $quantity];
          if ($oiHasUnit) { $oiCols[] = 'unitPrice'; $oiTypes .= 'd'; $oiParams[] = $unitPrice; }
          if ($oiHasTotal) { $oiCols[] = 'totalPrice'; $oiTypes .= 'd'; $oiParams[] = $totalPrice; }
          $oiPlaceholders = rtrim(str_repeat('?, ', count($oiCols)), ', ');
          $orderItemQuery = 'INSERT INTO order_items (' . implode(',', $oiCols) . ') VALUES (' . $oiPlaceholders . ')';
          $okItem = executePreparedUpdate($orderItemQuery, $oiTypes, $oiParams);
          if ($okItem === false) {
            $dbErr = $GLOBALS['db_last_error'] ?? mysqli_error($GLOBALS['conn']);
            $error = "Failed to place order!" . ($dbErr ? " (" . $dbErr . ")" : "");
            break;
          }
          $insertedCount++;

          if ($hasInventory && $quantity > 0) {
            try {
              $deductSql = "UPDATE inventory SET stock_qty = stock_qty - ? WHERE itemID = ? AND stock_qty >= ?";
              $deductOk = executePreparedUpdate($deductSql, "iii", [$quantity, $itemID, $quantity]);
              
              if ($deductOk === false) {
                $dbErr = $GLOBALS['db_last_error'] ?? mysqli_error($conn);
                $error = "Failed to update inventory!" . ($dbErr ? " (" . $dbErr . ")" : "");
                break;
              }
              // If no rows affected, inventory row may not exist yet; do not fail the order.
            } catch (Exception $e) {
              error_log("Inventory deduction error: " . $e->getMessage());
              $error = "An error occurred while processing your order. Please try again.";
              break;
            }
          }
        }
      }

      if ($error) {
        mysqli_rollback($conn);
      } else {
        mysqli_commit($conn);
        // Clear cart
        unset($_SESSION['cart']);
        $success = "Order placed successfully! Order Number: $orderNumber";
      }
    } else {
      mysqli_rollback($conn);
      $dbErr = $GLOBALS['db_last_error'] ?? mysqli_error($GLOBALS['conn']);
      $error = "Failed to place order!" . ($dbErr ? " (" . $dbErr . ")" : "");
    }
    }
  }
}

// Get user info using prepared statement
$userID = intval($_SESSION['userID']);
$userQuery = "SELECT * FROM users WHERE userID = ?";
$userResult = executePreparedQuery($userQuery, "i", [$userID]);
$user = mysqli_fetch_assoc($userResult);

// Calculate cart total using prepared statements - ensure we use the actual cart
$cartTotal = 0;
$cart = $_SESSION['cart'] ?? [];

foreach($cart as $itemID => $quantity){
  $itemID = intval($itemID);
  $quantity = intval($quantity);
  
  // Skip invalid entries
  if($itemID <= 0 || $quantity <= 0) continue;
  
  $itemQuery = "SELECT price FROM items WHERE itemID = ? AND status = 'Active'";
  $itemResult = executePreparedQuery($itemQuery, "i", [$itemID]);
  
  if($itemResult && mysqli_num_rows($itemResult) > 0){
    $item = mysqli_fetch_assoc($itemResult);
    $unitPrice = floatval($item['price']);
    $cartTotal += $unitPrice * $quantity;
  }
}

include("includes/header.php");
?>


<div class="checkout-container">
  <?php if($error): ?>
  <div class="alert alert-danger"><?php echo $error; ?></div>
  <?php endif; ?>
  
  <?php if($success): ?>
  <div class="alert alert-success"><?php echo $success; ?> <a href="my-orders.php">View Orders</a></div>
  <?php else: ?>
  
  <div class="checkout-header">
    <h1>Checkout</h1>
    <p>Complete your order details below</p>
  </div>
  
  <div class="row g-4">
    <div class="col-lg-8">
      <div class="checkout-form-card">
        <div class="checkout-form-header">
          <h4><i class="fas fa-shopping-cart me-2"></i>Order Details</h4>
        </div>
        <div class="checkout-form-body">
          <form method="POST">
            <div class="form-section">
              <div class="form-section-title">
                <i class="fas fa-calendar-alt me-2"></i>Delivery Information
              </div>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Full Name</label>
                  <input type="text" class="form-control" name="fullName" value="<?php echo $user['fullName']; ?>" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Contact Number</label>
                  <input type="text" class="form-control" name="contactNumber" value="<?php echo $user['mobileNumber']; ?>" required>
                </div>
              </div>
            </div>

            <div class="form-section">
              <div class="form-section-title">
                <i class="fas fa-map-marker-alt me-2"></i>Delivery Address
              </div>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Flat or Building Number</label>
                  <input type="text" class="form-control" name="flatNumber" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Street Name</label>
                  <input type="text" class="form-control" name="streetName" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Landmark</label>
                  <input type="text" class="form-control" name="landmark" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">City</label>
                  <input type="text" class="form-control" name="city" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Zip Code</label>
                  <input type="text" class="form-control" name="zipcode" required>
                </div>
              </div>
            </div>

            <!-- Shipping Coupons Section -->
            <div class="form-section">
              <div class="form-section-title">
                <i class="fas fa-ticket-alt me-2"></i>Shipping Coupons
              </div>
              <div class="coupon-section mb-3">
                <button type="button" class="btn btn-outline-brown" data-bs-toggle="modal" data-bs-target="#couponModal">
                  <i class="fas fa-ticket-alt me-2"></i>
                  <?php if($selectedCoupon): ?>
                    Applied: <?php echo $selectedCoupon['name']; ?> - Free Shipping!
                  <?php else: ?>
                    Apply Shipping Coupon
                  <?php endif; ?>
                </button>
                <?php if($selectedCoupon): ?>
                  <a href="?removeCoupon=1" class="btn btn-sm btn-link text-danger ms-2">
                    <i class="fas fa-times"></i> Remove
                  </a>
                <?php endif; ?>
              </div>
            </div>
            
            <div class="form-section">
              <div class="form-section-title">
                <i class="fas fa-credit-card me-2"></i>Payment Method
              </div>
              <div class="payment-options">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="paymentMethod" id="paymentPaypal" value="paypal" required>
                  <label class="form-check-label" for="paymentPaypal">
                    <i class="fab fa-paypal me-2"></i>PayPal (Secure Online Payment)
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="paymentMethod" id="cod" value="cod" required>
                  <label class="form-check-label" for="cod">
                    <i class="fas fa-money-bill-wave me-2"></i>Cash on Delivery
                  </label>
                </div>
              </div>
            </div>
            
            <div class="checkout-action-buttons mt-4">
              <button type="submit" name="placeOrder" class="btn-place-order" id="placeOrderBtn" disabled>
                <i class="fas fa-check-circle me-2"></i>PLACE ORDER
              </button>
              <a href="cart.php" class="btn-cancel-order">
                <i class="fas fa-arrow-left me-2"></i>Back to Cart
              </a>
            </div>
          </form>
        </div>
      </div>
    </div>
    
    <div class="col-lg-4">
      <div class="cart-summary-card">
        <div class="cart-summary-header">
          <h5><i class="fas fa-receipt me-2"></i>Order Summary</h5>
        </div>
        <div class="cart-summary-body">
          <?php
          $cartSubtotal = 0;
          // Display cart items - ensure we're using the actual cart from session
          $cart = $_SESSION['cart'] ?? [];
          if(empty($cart)) {
            // If cart is empty, redirect to cart page
            header("Location: cart.php");
            exit();
          }
          
          // Debug: Log cart contents (remove in production)
          // error_log("Checkout Cart: " . print_r($cart, true));
          
          foreach($cart as $itemID => $quantity):
            $itemID = intval($itemID);
            $quantity = intval($quantity);
            
            // Skip if invalid
            if($itemID <= 0 || $quantity <= 0) continue;
            
            $itemQuery = "SELECT * FROM items WHERE itemID = ? AND status = 'Active'";
            $itemResult = executePreparedQuery($itemQuery, "i", [$itemID]);
            if($itemResult && mysqli_num_rows($itemResult) > 0):
              $item = mysqli_fetch_assoc($itemResult);
              $itemImage = product_image_url($item, 1);
              $unitPrice = floatval($item['price']);
              $totalPrice = $unitPrice * $quantity;
              $cartSubtotal += $totalPrice;
          ?>
          <div class="cart-item">
            <img src="<?php echo $itemImage; ?>" alt="<?php echo htmlspecialchars($item['packageName'], ENT_QUOTES, 'UTF-8'); ?>" class="cart-item-image">
            <div class="cart-item-details">
              <div class="cart-item-name"><?php echo htmlspecialchars($item['packageName'], ENT_QUOTES, 'UTF-8'); ?></div>
              <div class="cart-item-quantity">Quantity: <?php echo $quantity; ?> pcs</div>
              <div class="cart-item-unit-price text-muted small">₱<?php echo number_format($unitPrice, 2); ?> per pack</div>
            </div>
            <div class="cart-item-price">₱<?php echo number_format($totalPrice, 2); ?></div>
          </div>
          <?php
            endif;
          endforeach;
          ?>
          
          <div class="cart-summary-row">
            <span class="cart-summary-label">Subtotal</span>
            <span class="cart-summary-value">₱<?php echo number_format($cartSubtotal, 2); ?></span>
          </div>
          <?php
          // Compute shipping discount compared to base fee
          $shippingDiscount = max(0, $baseShippingFee - $shippingFee);
          ?>
          <?php if($shippingDiscount > 0 && $selectedCoupon): ?>
          <div class="cart-summary-row" style="color: #28a745; font-weight: 600;">
            <span class="cart-summary-label">
              <i class="fas fa-ticket-alt me-1"></i><?php echo $selectedCoupon['name']; ?> (<?php echo $selectedCouponCode; ?>)
            </span>
            <span class="cart-summary-value">-₱<?php echo number_format($shippingDiscount, 2); ?></span>
          </div>
          <div class="cart-summary-row" style="color: #28a745;">
            <span class="cart-summary-label">Shipping Fee</span>
            <span class="cart-summary-value">Free</span>
          </div>
          <?php else: ?>
          <div class="cart-summary-row">
            <span class="cart-summary-label">Shipping Fee</span>
            <span class="cart-summary-value">₱<?php echo number_format($shippingFee, 2); ?></span>
          </div>
          <?php endif; ?>
          <div class="cart-summary-row cart-summary-total">
            <span class="cart-summary-label">Total</span>
            <span class="cart-summary-value">₱<?php echo number_format($cartSubtotal + $shippingFee, 2); ?></span>
          </div>
          
          <div class="payment-section">
            <div class="paypal-section" id="paypalSection" style="display:block;">
              <div id="paypal-button-container"></div>
            </div>
            <div class="cod-section" id="codSection" style="display: none;">
              <div class="cod-info">
                <i class="fas fa-info-circle me-2"></i>
                <span>Pay with cash when your order is delivered</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <?php endif; ?>
</div>

<!-- Coupon Modal -->
<div class="modal fade" id="couponModal" tabindex="-1" aria-labelledby="couponModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="couponModalLabel">
          <i class="fas fa-ticket-alt me-2"></i>Available Shipping Coupons
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="POST" id="couponForm">
          <div class="coupon-list">
            <?php foreach($availableCoupons as $code => $coupon): ?>
            <div class="coupon-item mb-3 p-3 border rounded">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="couponCode" id="coupon<?php echo $code; ?>" 
                       value="<?php echo $code; ?>" 
                       <?php echo ($selectedCouponCode === $code) ? 'checked' : ''; ?>>
                <label class="form-check-label w-100" for="coupon<?php echo $code; ?>">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <strong><?php echo $coupon['name']; ?></strong>
                      <p class="mb-0 small text-muted"><?php echo $coupon['description']; ?></p>
                    </div>
                    <span class="badge bg-brown"><?php echo $code; ?></span>
                  </div>
                </label>
              </div>
            </div>
            <?php endforeach; ?>
            <div class="coupon-item mb-3 p-3 border rounded">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="couponCode" id="couponNone" value="" 
                       <?php echo (!$selectedCouponCode) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="couponNone">
                  No Coupon
                </label>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="applyCouponBtn" class="btn btn-brown">
          <i class="fas fa-check me-2"></i>Apply Coupon
        </button>
      </div>
    </div>
  </div>
</div>

<?php include("includes/footer.php"); ?>

<script>
// Toast notification system
function showToast(title, message, type = 'success') {
  const container = document.querySelector('.bakery-toast-container') || (() => {
    const div = document.createElement('div');
    div.className = 'bakery-toast-container';
    document.body.appendChild(div);
    return div;
  })();

  const toast = document.createElement('div');
  toast.className = `bakery-toast ${type}`;
  
  const iconMap = {
    success: 'fas fa-check-circle',
    error: 'fas fa-exclamation-circle',
    info: 'fas fa-info-circle'
  };

  toast.innerHTML = `
    <div class="bakery-toast-icon">
      <i class="${iconMap[type]}"></i>
    </div>
    <div class="bakery-toast-content">
      <div class="bakery-toast-title">${title}</div>
      <div class="bakery-toast-message">${message}</div>
    </div>
    <button class="bakery-toast-close">
      <i class="fas fa-times"></i>
    </button>
  `;

  container.appendChild(toast);

  // Show toast
  setTimeout(() => toast.classList.add('show'), 10);

  // Auto hide after 3 seconds
  const hideToast = () => {
    toast.classList.remove('show');
    setTimeout(() => toast.remove(), 300);
  };
  const timeoutId = setTimeout(hideToast, 3000);

  // Close button
  toast.querySelector('.bakery-toast-close').addEventListener('click', () => {
    clearTimeout(timeoutId);
    hideToast();
  });
}

(function() {
  const form = document.querySelector('form[method="POST"]');
  const btnContainer = document.getElementById('paypal-button-container');
  const placeOrderBtn = document.getElementById('placeOrderBtn');
  const paypalSection = document.getElementById('paypalSection');
  const codSection = document.getElementById('codSection');

  // Save form data to localStorage
  function saveFormData() {
    if (!form) return;
    const formData = {};
    const inputs = form.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
      if (input.name && input.type !== 'file') {
        formData[input.name] = input.value;
      }
    });
    localStorage.setItem('checkoutFormData', JSON.stringify(formData));
  }

  // Restore form data from localStorage
  function restoreFormData() {
    if (!form) return;
    const saved = localStorage.getItem('checkoutFormData');
    if (saved) {
      try {
        const formData = JSON.parse(saved);
        Object.keys(formData).forEach(name => {
          const input = form.querySelector(`[name="${name}"]`);
          if (input && input.type !== 'file') {
            input.value = formData[name];
          }
        });
      } catch (e) {
        console.error('Error restoring form data:', e);
      }
    }
  }

  // Restore form data on page load
  restoreFormData();

  // Save form data on input change
  if (form) {
    form.addEventListener('input', saveFormData);
    form.addEventListener('change', saveFormData);
  }

  // Clear saved form data after successful order
  if (document.querySelector('.alert-success')) {
    localStorage.removeItem('checkoutFormData');
  }

  // Handle coupon application via AJAX
  const applyCouponBtn = document.getElementById('applyCouponBtn');
  const couponForm = document.getElementById('couponForm');
  const couponModal = document.getElementById('couponModal');
  
  if (applyCouponBtn && couponForm) {
    applyCouponBtn.addEventListener('click', async function() {
      const formData = new FormData(couponForm);
      formData.append('applyCoupon', '1');
      formData.append('ajax', '1');
      
      try {
        const response = await fetch('checkout.php', {
          method: 'POST',
          body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
          // Close modal
          const modalInstance = bootstrap.Modal.getInstance(couponModal);
          if (modalInstance) modalInstance.hide();
          
          // Reload page to update order summary
          window.location.reload();
        } else {
          showToast('Error', result.message || 'Failed to apply coupon', 'error');
        }
      } catch (error) {
        console.error('Error applying coupon:', error);
        showToast('Error', 'Failed to apply coupon. Please try again.', 'error');
      }
    });
  }

  if (!form || !btnContainer) return;

  // Payment method handling
  const paymentRadios = document.querySelectorAll('input[name="paymentMethod"]');
  paymentRadios.forEach(radio => {
    radio.addEventListener('change', function() {
      // Hide all payment sections first
      paypalSection.style.display = 'none';
      paypalSection.classList.remove('active');
      codSection.style.display = 'none';
      codSection.classList.remove('active');
      
      if (this.value === 'paypal') {
        paypalSection.style.display = 'block';
        paypalSection.classList.add('active');
        placeOrderBtn.disabled = true; // Disable until PayPal approval
        loadPayPal();
      } else if (this.value === 'cod') {
        codSection.style.display = 'block';
        codSection.classList.add('active');
        placeOrderBtn.disabled = false; // Enable for COD
        // Clear PayPal container
        btnContainer.innerHTML = '';
      }
    });
  });

  // Form validation
  function validateForm() {
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    let firstInvalidField = null;
    
    requiredFields.forEach(field => {
      // Remove previous invalid state
      field.classList.remove('is-invalid');
      
      // Check if field is empty
      if (!field.value.trim()) {
        isValid = false;
        field.classList.add('is-invalid');
        if (!firstInvalidField) firstInvalidField = field;
      } else {
        // Additional validation based on field type
        if (field.type === 'tel' || field.name === 'contactNumber') {
          const phoneRegex = /^[\d\s\-\+\(\)]+$/;
          if (!phoneRegex.test(field.value.replace(/\s/g, ''))) {
            isValid = false;
            field.classList.add('is-invalid');
            if (!firstInvalidField) firstInvalidField = field;
          }
        }
        if (field.type === 'text' && field.name === 'zipcode') {
          const zipRegex = /^\d{4,6}$/;
          if (!zipRegex.test(field.value.replace(/\s/g, ''))) {
            isValid = false;
            field.classList.add('is-invalid');
            if (!firstInvalidField) firstInvalidField = field;
          }
        }
      }
    });
    
    // Check if payment method is selected
    const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked');
    if (!paymentMethod) {
      isValid = false;
      // Highlight payment section
      const paymentSection = document.querySelector('.payment-options');
      if (paymentSection) {
        paymentSection.style.border = '2px solid #dc3545';
        paymentSection.style.borderRadius = '8px';
        paymentSection.style.padding = '16px';
        setTimeout(() => {
          paymentSection.style.border = '';
          paymentSection.style.borderRadius = '';
          paymentSection.style.padding = '';
        }, 3000);
      }
    }
    
    // Focus on first invalid field
    if (firstInvalidField) {
      firstInvalidField.focus();
    }
    
    return isValid;
  }

  // Add validation feedback
  const requiredFields = form.querySelectorAll('[required]');
  requiredFields.forEach(field => {
    field.addEventListener('blur', function() {
      if (!this.value.trim()) {
        this.classList.add('is-invalid');
      } else {
        this.classList.remove('is-invalid');
        // Additional validation on blur
        if (this.type === 'tel' || this.name === 'contactNumber') {
          const phoneRegex = /^[\d\s\-\+\(\)]+$/;
          if (!phoneRegex.test(this.value.replace(/\s/g, ''))) {
            this.classList.add('is-invalid');
          }
        }
        if (this.type === 'text' && this.name === 'zipcode') {
          const zipRegex = /^\d{4,6}$/;
          if (!zipRegex.test(this.value.replace(/\s/g, ''))) {
            this.classList.add('is-invalid');
          }
        }
      }
    });
    
    // Clear validation on input
    field.addEventListener('input', function() {
      this.classList.remove('is-invalid');
    });
  });

  // Prevent form submission if invalid
  form.addEventListener('submit', function(e) {
    if (!validateForm()) {
      e.preventDefault();
      showToast('Validation Error', 'Please fill in all required fields correctly', 'error');
      return false;
    }
  });

  // PayPal integration
  let paypalSdkLoading = null;
  function renderPayPalButtons() {
    if (!window.paypal || typeof window.paypal.Buttons !== 'function') {
      throw new Error('PayPal SDK not ready (Buttons missing)');
    }

    window.paypal.Buttons({
      style: {
        layout: 'vertical',
        color: 'gold',
        shape: 'rect',
        label: 'paypal',
        height: 45
      },
      createOrder: function() {
        console.log('Creating PayPal order...');
        return fetch('api/paypal-create-order.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({})
        })
        .then(res => {
          console.log('Create order response status:', res.status);
          return res.json();
        })
        .then(order => {
          console.log('Order created:', order);
          if (order && order.id) return order.id;
          throw new Error('Unable to create PayPal order');
        });
      },
      onApprove: function(data) {
        console.log('PayPal payment approved:', data);
        return fetch('api/paypal-capture-order.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ orderID: data.orderID })
        })
        .then(res => res.json())
        .then(details => {
          const status = (details && details.status) ? details.status : '';
          console.log('Payment captured:', details);
          if (status !== 'COMPLETED') {
            throw new Error('PayPal capture not completed');
          }

          const hidden = document.createElement('input');
          hidden.type = 'hidden';
          hidden.name = 'paypalOrderID';
          hidden.value = data.orderID;
          form.appendChild(hidden);

          placeOrderBtn.disabled = false;
          showToast('Payment Approved', 'PayPal payment successful. You can now place your order.', 'success');
        });
      },
      onError: function(err) {
        console.error('PayPal Error:', err);
        btnContainer.innerHTML = '<div class="alert alert-danger mb-0">PayPal error. Please try again.</div>';
      },
      onCancel: function() {
        console.log('PayPal payment cancelled');
        showToast('Payment Cancelled', 'PayPal payment was cancelled.', 'info');
      }
    }).render('#paypal-button-container');
  }

  function ensurePayPalSdk(cfg) {
    if (window.paypal && typeof window.paypal.Buttons === 'function') {
      return Promise.resolve();
    }

    if (paypalSdkLoading) return paypalSdkLoading;

    paypalSdkLoading = new Promise((resolve, reject) => {
      const existing = document.querySelector('script[src*="www.paypal.com/sdk/js"]');
      if (existing) {
        existing.addEventListener('load', () => resolve());
        existing.addEventListener('error', (e) => reject(e));
        return;
      }

      const script = document.createElement('script');
      // Use minimal SDK params to avoid SDK runtime crashes leaving a partial window.paypal
      script.src = `https://www.paypal.com/sdk/js?client-id=${cfg.client_id}&currency=${cfg.currency || 'PHP'}`;
      script.onload = () => {
        if (window.paypal && typeof window.paypal.Buttons === 'function') {
          resolve();
          return;
        }
        reject(new Error('PayPal SDK loaded but Buttons is missing'));
      };
      script.onerror = (e) => reject(e);
      document.head.appendChild(script);
    });

    return paypalSdkLoading;
  }

  function loadPayPal() {
    console.log('Loading PayPal...');

    btnContainer.innerHTML = '<div class="loading-paypal">Loading PayPal...</div>';
    btnContainer.style.display = 'flex';
    btnContainer.style.visibility = 'visible';
    btnContainer.style.width = '100%';
    btnContainer.style.height = 'auto';

    fetch('api/paypal-config.php')
      .then(r => {
        console.log('PayPal config response status:', r.status);
        console.log('PayPal config response headers:', r.headers);
        
        if (!r.ok) {
          throw new Error(`HTTP error! status: ${r.status}`);
        }
        return r.json();
      })
      .then(cfg => {
        console.log('PayPal config:', cfg);
        
        if (cfg.error) {
          console.error('PayPal config error:', cfg.error);
          btnContainer.innerHTML = '<div class="alert alert-danger mb-0">PayPal configuration error: ' + cfg.error + '</div>';
          return;
        }
        
        if (!cfg || !cfg.client_id) {
          console.error('PayPal not configured:', cfg);
          btnContainer.innerHTML = '<div class="alert alert-warning mb-0">PayPal is not configured.</div>';
          return;
        }
        
        btnContainer.innerHTML = '';
        return ensurePayPalSdk(cfg)
          .then(() => {
            console.log('PayPal SDK loaded successfully');
            console.log('Rendering PayPal buttons...');
            renderPayPalButtons();
          })
          .catch((e) => {
            console.error('PayPal SDK load failed:', e);
            btnContainer.innerHTML = '<div class="alert alert-danger mb-0">Failed to load PayPal SDK.</div>';
          });
      })
      .catch((error) => {
        console.error('PayPal Config Error:', error);
        btnContainer.innerHTML = '<div class="alert alert-warning mb-0">PayPal is not available right now.</div>';
      });
  }
})();
</script>

