<?php
require_once __DIR__ . '/includes/bootstrap.php';

if(!isset($_SESSION['userID'])){
  header("Location: login.php");
  exit();
}

$userID = intval($_SESSION['userID']);

// Get user information
$userQuery = "SELECT userID, fullName, email, mobileNumber, regDate FROM users WHERE userID = ?";
$userResult = executePreparedQuery($userQuery, "i", [$userID]);
$user = mysqli_fetch_assoc($userResult);

// Get order statistics
$totalOrders = (int)mysqli_fetch_assoc(executePreparedQuery("SELECT COUNT(*) as count FROM orders WHERE userID = ?", "i", [$userID]))['count'];
$pendingOrders = (int)mysqli_fetch_assoc(executePreparedQuery("SELECT COUNT(*) as count FROM orders WHERE userID = ? AND orderStatus = ?", "is", [$userID, 'Still Pending']))['count'];
$confirmedOrders = (int)mysqli_fetch_assoc(executePreparedQuery("SELECT COUNT(*) as count FROM orders WHERE userID = ? AND orderStatus = ?", "is", [$userID, 'Confirmed']))['count'];
$deliveredOrders = (int)mysqli_fetch_assoc(executePreparedQuery("SELECT COUNT(*) as count FROM orders WHERE userID = ? AND orderStatus = ?", "is", [$userID, 'Delivered']))['count'];

// Get recent orders (last 5) with total amount
$recentOrdersQuery = "SELECT o.orderID, o.orderNumber, o.orderDate, o.orderStatus,
                      COALESCE(SUM(oi.totalPrice), 0) as totalAmount
                      FROM orders o
                      LEFT JOIN order_items oi ON o.orderID = oi.orderID
                      WHERE o.userID = ?
                      GROUP BY o.orderID, o.orderNumber, o.orderDate, o.orderStatus
                      ORDER BY o.orderDate DESC 
                      LIMIT 5";
$recentOrdersResult = executePreparedQuery($recentOrdersQuery, "i", [$userID]);

// Calculate cart items count
$cartCount = 0;
if(isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
  foreach($_SESSION['cart'] as $quantity) {
    $cartCount += intval($quantity);
  }
}

include(__DIR__ . "/includes/header.php");
?>

<div class="checkout-container">
  <div class="checkout-header">
    <h1><i class="fas fa-tachometer-alt me-2"></i>My Dashboard</h1>
    <p>Welcome back, <?php echo htmlspecialchars($user['fullName']); ?>!</p>
  </div>

  <?php 
  // Check if mobile number is valid
  $hasValidMobile = isValidMobileNumber($user['mobileNumber']);
  if(!$hasValidMobile): 
  ?>
  <div class="alert alert-warning alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <strong>Action Required:</strong> Please update your mobile number to continue shopping. 
    <a href="update-mobile.php" class="alert-link fw-bold">Update Mobile Number Now</a>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  <?php endif; ?>

  <!-- Statistics Cards -->
  <div class="row g-4 mb-4">
    <div class="col-md-3 col-sm-6">
      <div class="checkout-form-card h-100 text-center">
        <div class="checkout-form-body">
          <div class="dashboard-stat">
            <i class="fas fa-shopping-bag fa-3x mb-3" style="color: var(--brown-primary);"></i>
            <h2 class="mb-1"><?php echo $totalOrders; ?></h2>
            <p class="text-muted mb-0">Total Orders</p>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-sm-6">
      <div class="checkout-form-card h-100 text-center">
        <div class="checkout-form-body">
          <div class="dashboard-stat">
            <i class="fas fa-clock fa-3x mb-3" style="color: #ffc107;"></i>
            <h2 class="mb-1"><?php echo $pendingOrders; ?></h2>
            <p class="text-muted mb-0">Pending Orders</p>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-sm-6">
      <div class="checkout-form-card h-100 text-center">
        <div class="checkout-form-body">
          <div class="dashboard-stat">
            <i class="fas fa-check-circle fa-3x mb-3" style="color: #0d6efd;"></i>
            <h2 class="mb-1"><?php echo $confirmedOrders; ?></h2>
            <p class="text-muted mb-0">Confirmed</p>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-sm-6">
      <div class="checkout-form-card h-100 text-center">
        <div class="checkout-form-body">
          <div class="dashboard-stat">
            <i class="fas fa-check-double fa-3x mb-3" style="color: #198754;"></i>
            <h2 class="mb-1"><?php echo $deliveredOrders; ?></h2>
            <p class="text-muted mb-0">Delivered</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <!-- User Information Card -->
    <div class="col-lg-4">
      <div class="checkout-form-card h-100">
        <div class="checkout-form-header">
          <h4><i class="fas fa-user me-2"></i>Account Information</h4>
        </div>
        <div class="checkout-form-body">
          <div class="mb-3">
            <label class="form-label fw-bold">Full Name</label>
            <p class="text-muted mb-0"><?php echo htmlspecialchars($user['fullName']); ?></p>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Email Address</label>
            <p class="text-muted mb-0"><?php echo htmlspecialchars($user['email']); ?></p>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Mobile Number</label>
            <div class="d-flex justify-content-between align-items-center">
              <?php 
              // Check if mobile number looks like an email or is invalid
              $displayMobile = $user['mobileNumber'];
              if(empty($displayMobile) || strpos($displayMobile, '@') !== false || !$hasValidMobile) {
                $displayMobile = '<span class="text-danger fst-italic"><i class="fas fa-exclamation-triangle me-1"></i>Not Set</span>';
              } else {
                $displayMobile = '<span class="text-muted">' . htmlspecialchars($displayMobile) . '</span>';
              }
              ?>
              <div class="mb-0"><?php echo $displayMobile; ?></div>
              <?php if(!$hasValidMobile): ?>
                <a href="update-mobile.php" class="btn btn-sm btn-warning">
                  <i class="fas fa-edit me-1"></i>Update
                </a>
              <?php else: ?>
                <span class="badge bg-success"><i class="fas fa-check me-1"></i>Valid</span>
              <?php endif; ?>
            </div>
          </div>
          <div class="mb-0">
            <label class="form-label fw-bold">Member Since</label>
            <p class="text-muted mb-0"><?php echo date('F d, Y', strtotime($user['regDate'])); ?></p>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions Card -->
    <div class="col-lg-4">
      <div class="checkout-form-card h-100">
        <div class="checkout-form-header">
          <h4><i class="fas fa-bolt me-2"></i>Quick Actions</h4>
        </div>
        <div class="checkout-form-body">
          <div class="d-grid gap-2">
            <a href="my-orders.php" class="btn btn-brown">
              <i class="fas fa-receipt me-2"></i>View All Orders
            </a>
            <a href="cart.php" class="btn btn-outline-brown">
              <i class="fas fa-shopping-cart me-2"></i>My Cart 
              <?php if($cartCount > 0): ?>
                <span class="badge bg-danger ms-2"><?php echo $cartCount; ?></span>
              <?php endif; ?>
            </a>
            <a href="my-favorites.php" class="btn btn-outline-brown">
              <i class="fas fa-heart me-2"></i>My Favorites
            </a>
            <a href="customer-messages.php" class="btn btn-outline-brown">
              <i class="fas fa-envelope me-2"></i>My Messages
            </a>
            <a href="products.php" class="btn btn-outline-brown">
              <i class="fas fa-store me-2"></i>Continue Shopping
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Cart & Favorites Summary -->
    <div class="col-lg-4">
      <div class="checkout-form-card h-100">
        <div class="checkout-form-header">
          <h4><i class="fas fa-list me-2"></i>Quick Summary</h4>
        </div>
        <div class="checkout-form-body">
          <div class="mb-3 d-flex justify-content-between align-items-center">
            <span><i class="fas fa-shopping-cart me-2"></i>Items in Cart:</span>
            <strong id="cartCountDisplay"><?php echo $cartCount; ?></strong>
          </div>
          <div class="mb-3 d-flex justify-content-between align-items-center">
            <span><i class="fas fa-heart me-2"></i>Favorites:</span>
            <strong id="favoritesCountDisplay">0</strong>
          </div>
          <div class="mb-0 d-flex justify-content-between align-items-center">
            <span><i class="fas fa-receipt me-2"></i>Total Orders:</span>
            <strong><?php echo $totalOrders; ?></strong>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent Orders -->
  <div class="row mt-4">
    <div class="col-12">
      <div class="checkout-form-card">
        <div class="checkout-form-header d-flex justify-content-between align-items-center">
          <h4><i class="fas fa-history me-2"></i>Recent Orders</h4>
          <a href="my-orders.php" class="btn btn-sm btn-outline-brown">View All</a>
        </div>
        <div class="checkout-form-body">
          <?php if(mysqli_num_rows($recentOrdersResult) > 0): ?>
          <div class="table-responsive">
            <table class="table table-striped align-middle">
              <thead>
                <tr>
                  <th>Order Number</th>
                  <th>Order Date</th>
                  <th>Total Amount</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php while($order = mysqli_fetch_assoc($recentOrdersResult)): ?>
                <tr>
                  <td><strong><?php echo htmlspecialchars($order['orderNumber']); ?></strong></td>
                  <td><?php echo date('M d, Y g:i A', strtotime($order['orderDate'])); ?></td>
                  <td>₱<?php echo number_format($order['totalAmount'] ?? 0, 2); ?></td>
                  <td>
                    <span class="badge 
                      <?php 
                      if($order['orderStatus'] == 'Delivered') echo 'bg-success';
                      elseif($order['orderStatus'] == 'On The Way') echo 'bg-info';
                      elseif($order['orderStatus'] == 'Confirmed') echo 'bg-primary';
                      else echo 'bg-warning';
                      ?>">
                      <?php echo htmlspecialchars($order['orderStatus']); ?>
                    </span>
                  </td>
                  <td>
                    <a href="order-details.php?orderID=<?php echo $order['orderID']; ?>" class="btn btn-sm btn-primary">
                      <i class="fas fa-eye"></i> View
                    </a>
                    <a href="track-order.php?orderID=<?php echo $order['orderID']; ?>" class="btn btn-sm btn-info">
                      <i class="fas fa-motorcycle"></i> Track
                    </a>
                  </td>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
          <?php else: ?>
          <div class="text-center py-5">
            <i class="fas fa-shopping-bag fa-4x text-muted mb-3"></i>
            <h5 class="text-muted">No orders yet</h5>
            <p class="text-muted">Start shopping to see your orders here!</p>
            <a href="products.php" class="btn btn-brown mt-3">Browse Products</a>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Update favorites count from localStorage
document.addEventListener('DOMContentLoaded', function() {
  const favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
  const favoritesCount = favorites.filter(id => Number.isFinite(parseInt(id, 10)) && parseInt(id, 10) > 0).length;
  const favoritesDisplay = document.getElementById('favoritesCountDisplay');
  if(favoritesDisplay) {
    favoritesDisplay.textContent = favoritesCount;
  }
});
</script>

<style>
.dashboard-stat {
  padding: 1rem;
}

.dashboard-stat h2 {
  font-size: 2.5rem;
  font-weight: 700;
  color: var(--brown-dark);
  margin: 0;
}

.dashboard-stat p {
  font-size: 0.9rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.checkout-form-card {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.checkout-form-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(139, 69, 19, 0.15);
}
</style>

<?php include(__DIR__ . "/includes/footer.php"); ?>

