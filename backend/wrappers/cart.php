<?php
require dirname(__DIR__) . '/pages/cart/cart.php';
exit; ?>

// Check if user has valid mobile number (if logged in)
if(isset($_SESSION['userID'])){
  $userID = intval($_SESSION['userID']);
  if(!userHasValidMobileNumber($userID)){
    // Store current URL to return after updating mobile number
    $_SESSION['redirect_after_mobile_update'] = 'cart.php';
    header("Location: update-mobile.php");
    exit();
  }
}

// Add to cart
if(isset($_POST['itemID']) && isset($_POST['quantity'])){
  if(!isset($_SESSION['cart'])){
    $_SESSION['cart'] = array();
  }
  
  $itemID = intval($_POST['itemID']);
  $quantity = intval($_POST['quantity']);
  
  if(isset($_SESSION['cart'][$itemID])){
    $_SESSION['cart'][$itemID] += $quantity;
  } else {
    $_SESSION['cart'][$itemID] = $quantity;
  }
  
  header("Location: cart.php");
  exit();
}

// Remove from cart
if(isset($_POST['deleteID'])){
  $deleteID = intval($_POST['deleteID']);
  if(isset($_SESSION['cart'][$deleteID])){
    unset($_SESSION['cart'][$deleteID]);
  }
  header("Location: cart.php");
  exit();
}

include(__DIR__ . "/includes/header.php");
?>


<style>
  .btn-place-order,
  .btn-place-order:hover,
  .btn-place-order:focus,
  .btn-place-order:active,
  .btn-place-order:disabled,
  .btn-place-order[disabled],
  .btn-place-order.disabled {
    color: #fff !important;
    text-shadow: 0 1px 0 rgba(0,0,0,0.25);
  }
  .btn-place-order i { color: #fff !important; }
</style>

<div class="checkout-container">
  <div class="checkout-header">
    <h1>My Cart</h1>
    <p>Review your items before checkout</p>
  </div>
  <?php
  if(!isset($_SESSION['cart']) || empty($_SESSION['cart'])):
  ?>
  <div class="alert alert-info text-center">
    <h4>Your Cart is Empty</h4>
    <a href="products.php" class="btn btn-brown">Continue Shopping</a>
  </div>
  <?php else: ?>
  
  <?php
    $total = 0;
    $totalQty = 0;
  ?>
  <div class="row g-4">
    <div class="col-lg-8">
      <div class="checkout-form-card">
        <div class="checkout-form-header">
          <h4><i class="fas fa-shopping-cart me-2"></i>Shopping Cart</h4>
        </div>
        <div class="checkout-form-body">
          <div class="cart-summary-body">
            <?php foreach($_SESSION['cart'] as $itemID => $quantity): ?>
              <?php
                $itemID = intval($itemID);
                $quantity = intval($quantity);
                $result = executePreparedQuery("SELECT * FROM items WHERE itemID = ?", "i", [$itemID]);
                if($result && ($row = mysqli_fetch_assoc($result))):
                  $subtotal = $row['price'] * $quantity;
                  $total += $subtotal;
                  $totalQty += $quantity;
              ?>
              <div class="cart-item">
                <img src="<?php echo product_image_url($row, 1); ?>" alt="<?php echo e($row['packageName']); ?>" class="cart-item-image">
                <div class="cart-item-details">
                  <div class="cart-item-name"><?php echo e($row['packageName']); ?></div>
                  <div class="cart-item-quantity">Quantity: <?php echo $quantity; ?></div>
                </div>
                <div class="cart-item-price">₱<?php echo number_format($subtotal, 2); ?></div>
                <div class="ms-2">
                  <form method="POST" class="m-0">
                    <input type="hidden" name="deleteID" value="<?php echo $itemID; ?>">
                    <button type="submit" class="btn btn-outline-danger btn-sm" aria-label="Remove">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                </div>
              </div>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="cart-summary-card">
        <div class="cart-summary-header">
          <h5><i class="fas fa-receipt me-2"></i>Summary</h5>
        </div>
        <div class="cart-summary-body">
          <div class="cart-summary-row">
            <span class="cart-summary-label">Items</span>
            <span class="cart-summary-value"><?php echo (int)$totalQty; ?></span>
          </div>
          <div class="cart-summary-row cart-summary-total">
            <span class="cart-summary-label">Total</span>
            <span class="cart-summary-value">₱<?php echo number_format($total, 2); ?></span>
          </div>

          <div class="checkout-action-buttons mt-4">
            <?php if(isset($_SESSION['userID'])): ?>
              <a href="checkout.php" class="btn-place-order">
                <i class="fas fa-arrow-right me-2"></i>Proceed to Checkout
              </a>
            <?php else: ?>
              <a href="login.php" class="btn-place-order">
                <i class="fas fa-sign-in-alt me-2"></i>Login to Checkout
              </a>
            <?php endif; ?>
            <a href="products.php" class="btn-cancel-order">
              <i class="fas fa-arrow-left me-2"></i>Continue Shopping
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <?php endif; ?>
</div>

<?php include(__DIR__ . "/includes/footer.php"); ?>

