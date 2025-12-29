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

// Place order
if(isset($_POST['placeOrder'])){
  $fullName = trim($_POST['fullName']);
  $contactNumber = trim($_POST['contactNumber']);
  $deliveryDate = trim($_POST['deliveryDate']);
  $flatNumber = trim($_POST['flatNumber']);
  $streetName = trim($_POST['streetName']);
  $area = trim($_POST['area']);
  $landmark = trim($_POST['landmark']);
  $city = trim($_POST['city']);
  $zipcode = trim($_POST['zipcode']);
  $state = trim($_POST['state']);
  $userID = intval($_SESSION['userID']);
  
  // Input validation
  if(empty($fullName) || empty($contactNumber) || empty($deliveryDate) || 
     empty($flatNumber) || empty($streetName) || empty($area) || 
     empty($city) || empty($zipcode) || empty($state)){
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
      
      // Clear cart
      unset($_SESSION['cart']);
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
  
  <div class="row">
    <div class="col-md-8">
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
                <label class="form-label">Area</label>
                <input type="text" class="form-control" name="area" required>
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
              <div class="col-md-6 mb-3">
                <label class="form-label">State</label>
                <input type="text" class="form-control" name="state" required>
              </div>
            </div>
        </div>
      </div>
    </div>
    
    <div class="col-md-4">
      <div class="card shadow">
        <div class="card-body">
          <h5 class="card-title">Cart Total</h5>
          <hr>
          <div class="d-flex justify-content-between mb-2">
            <span>Subtotal</span>
            <span class="text-danger">₱<?php echo number_format($cartTotal, 2); ?></span>
          </div>
          <hr>
          <div class="d-flex justify-content-between mb-3">
            <strong>Total</strong>
            <strong class="text-danger">₱<?php echo number_format($cartTotal, 2); ?></strong>
          </div>
          <button type="submit" name="placeOrder" class="btn btn-warning w-100">PLACE ORDER</button>
          </form>
        </div>
      </div>
    </div>
  </div>
  
  <?php endif; ?>
</div>

<?php include("includes/footer.php"); ?>

