<?php
require_once __DIR__ . '/includes/bootstrap.php';

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
  
  // Check if buy now was clicked
  if(isset($_POST['buy_now']) || isset($_GET['buy_now'])){
    header("Location: checkout.php");
  } else {
    header("Location: cart.php");
  }
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

include("includes/header.php");
?>


<div class="container my-5">
  <?php
  if(!isset($_SESSION['cart']) || empty($_SESSION['cart'])):
  ?>
  <div class="alert alert-info text-center">
    <h4>Your Cart is Empty</h4>
    <a href="products.php" class="btn btn-warning">Continue Shopping</a>
  </div>
  <?php else: ?>
  
  <div class="card shadow">
    <div class="card-body">
      <h4 class="mb-4">Shopping Cart</h4>
      <table class="table table-striped">
        <thead>
          <tr>
            <th>Product Image</th>
            <th>Product Name</th>
            <th>Code</th>
            <th>Quantity</th>
            <th>Unit Price</th>
            <th>Price</th>
            <th>Remove</th>
          </tr>
        </thead>
        <tbody>
          <?php
          // Load product images mapping from JSON file
          $imagesMap = [];
          $imagesJsonPath = __DIR__ . '/product-images.json';
          if (file_exists($imagesJsonPath)) {
            $imagesJson = file_get_contents($imagesJsonPath);
            $imagesMap = json_decode($imagesJson, true) ?: [];
          }
          
          $total = 0;
          $totalQty = 0;
          foreach($_SESSION['cart'] as $itemID => $quantity):
            $itemID = intval($itemID);
            $quantity = intval($quantity);
            $result = executePreparedQuery("SELECT * FROM items WHERE itemID = ?", "i", [$itemID]);
            if($result && ($row = mysqli_fetch_assoc($result))):
              $subtotal = $row['price'] * $quantity;
              $total += $subtotal;
              $totalQty += $quantity;
              
              // Get product image from JSON mapping, fallback to database, then placeholder
              $productImage = 'https://via.placeholder.com/100';
              $packageName = $row['packageName'];
              
              if (isset($imagesMap[$packageName])) {
                $productImage = $imagesMap[$packageName];
              } elseif (!empty($row['itemImage'])) {
                $productImage = 'bakery bread image/' . $row['itemImage'];
              }
              
              // Resolve the actual image path
              $productImage = resolveImagePath($productImage);
          ?>
          <tr>
            <td>
              <img src="<?php echo imageUrl($productImage); ?>" 
                   alt="<?php echo e($row['packageName']); ?>" 
                   style="width: 80px; height: 80px; object-fit: cover; border-radius: 5px;">
            </td>
            <td><?php echo e($row['packageName']); ?></td>
            <td><?php echo e($row['itemID']); ?></td>
            <td><?php echo $quantity; ?></td>
            <td>₱<?php echo e($row['price']); ?></td>
            <td>₱<?php echo number_format($subtotal, 2); ?></td>
            <td>
              <form method="POST">
                <input type="hidden" name="deleteID" value="<?php echo $itemID; ?>">
                <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
              </form>
            </td>
          </tr>
          <?php
            endif;
          endforeach;
          ?>
          <tr>
            <td colspan="3" class="text-end"><strong>Total:</strong></td>
            <td><strong><?php echo $totalQty; ?></strong></td>
            <td colspan="2"><strong>₱<?php echo number_format($total, 2); ?></strong></td>
            <td></td>
          </tr>
        </tbody>
      </table>
      
      <!-- Free Shipping Badge -->
      <div class="alert alert-success d-flex align-items-center mb-4">
        <i class="fas fa-truck me-2"></i>
        <strong>Free Shipping</strong>
        <span class="ms-2">on all orders!</span>
      </div>
      
      <div class="mt-4">
        <a href="products.php" class="btn btn-secondary">CONTINUE SHOPPING</a>
        <?php if(isset($_SESSION['userID'])): ?>
        <a href="checkout.php" class="btn btn-warning float-end">PROCEED TO CHECKOUT</a>
        <?php else: ?>
        <a href="login.php" class="btn btn-warning float-end">LOGIN TO CHECKOUT</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
  
  <?php endif; ?>
</div>

<?php include("includes/footer.php"); ?>

