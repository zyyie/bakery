<?php
session_start();
include("connect.php");

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
  $fullName = $_POST['fullName'];
  $contactNumber = $_POST['contactNumber'];
  $deliveryDate = $_POST['deliveryDate'];
  $flatNumber = $_POST['flatNumber'];
  $streetName = $_POST['streetName'];
  $area = $_POST['area'];
  $landmark = $_POST['landmark'];
  $city = $_POST['city'];
  $zipcode = $_POST['zipcode'];
  $state = $_POST['state'];
  
  // Generate order number
  $orderNumber = rand(100000000, 999999999);
  
  // Insert order
  $orderQuery = "INSERT INTO orders (orderNumber, userID, fullName, contactNumber, deliveryDate, 
                flatNumber, streetName, area, landmark, city, zipcode, state, orderStatus) 
                VALUES ('$orderNumber', '".$_SESSION['userID']."', '$fullName', '$contactNumber', 
                '$deliveryDate', '$flatNumber', '$streetName', '$area', '$landmark', '$city', '$zipcode', '$state', 'Still Pending')";
  
  if(executeQuery($orderQuery)){
    $orderID = mysqli_insert_id($GLOBALS['conn']);
    
    // Insert order items
    foreach($_SESSION['cart'] as $itemID => $quantity){
      $itemQuery = "SELECT price FROM items WHERE itemID = $itemID";
      $itemResult = executeQuery($itemQuery);
      $item = mysqli_fetch_assoc($itemResult);
      
      $unitPrice = $item['price'];
      $totalPrice = $unitPrice * $quantity;
      
      $orderItemQuery = "INSERT INTO order_items (orderID, itemID, quantity, unitPrice, totalPrice) 
                        VALUES ($orderID, $itemID, $quantity, $unitPrice, $totalPrice)";
      executeQuery($orderItemQuery);
    }
    
    // Clear cart
    unset($_SESSION['cart']);
    $success = "Order placed successfully! Order Number: $orderNumber";
  } else {
    $error = "Failed to place order!";
  }
}

// Get user info
$userQuery = "SELECT * FROM users WHERE userID = '".$_SESSION['userID']."'";
$userResult = executeQuery($userQuery);
$user = mysqli_fetch_assoc($userResult);

// Calculate cart total
$cartTotal = 0;
foreach($_SESSION['cart'] as $itemID => $quantity){
  $itemQuery = "SELECT price FROM items WHERE itemID = $itemID";
  $itemResult = executeQuery($itemQuery);
  $item = mysqli_fetch_assoc($itemResult);
  $cartTotal += $item['price'] * $quantity;
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

