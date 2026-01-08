<?php
require_once __DIR__ . '/includes/bootstrap.php';
requireAdminLogin();
adminRegenerateSession();

// Auto-deliver orders: Check and update orders with delivery date today or past
$today = date('Y-m-d');
$autoDeliverQuery = "UPDATE orders 
                     SET orderStatus = 'Delivered' 
                     WHERE deliveryDate IS NOT NULL 
                     AND DATE(deliveryDate) <= ? 
                     AND orderStatus = 'On The Way'";
executePreparedUpdate($autoDeliverQuery, "s", [$today]);

include(__DIR__ . "/includes/header.php");

// Get statistics
$stats = array();

$stats['subscribers'] = (int)mysqli_fetch_assoc(executePreparedQuery("SELECT COUNT(*) as count FROM subscribers", "", []))['count'];
$stats['regUsers'] = (int)mysqli_fetch_assoc(executePreparedQuery("SELECT COUNT(*) as count FROM users", "", []))['count'];
$stats['readEnquiry'] = (int)mysqli_fetch_assoc(executePreparedQuery("SELECT COUNT(*) as count FROM enquiries WHERE status = ?", "s", ['Read']))['count'];
$stats['unreadEnquiry'] = (int)mysqli_fetch_assoc(executePreparedQuery("SELECT COUNT(*) as count FROM enquiries WHERE status = ?", "s", ['Unread']))['count'];
$stats['newOrders'] = (int)mysqli_fetch_assoc(executePreparedQuery("SELECT COUNT(*) as count FROM orders WHERE orderStatus = ?", "s", ['Still Pending']))['count'];
$stats['confirmedOrders'] = (int)mysqli_fetch_assoc(executePreparedQuery("SELECT COUNT(*) as count FROM orders WHERE orderStatus = ?", "s", ['Confirmed']))['count'];
$stats['deliveredOrders'] = (int)mysqli_fetch_assoc(executePreparedQuery("SELECT COUNT(*) as count FROM orders WHERE orderStatus = ?", "s", ['Delivered']))['count'];
$stats['cancelledOrders'] = (int)mysqli_fetch_assoc(executePreparedQuery("SELECT COUNT(*) as count FROM orders WHERE orderStatus = ?", "s", ['Cancelled']))['count'];
$stats['allOrders'] = (int)mysqli_fetch_assoc(executePreparedQuery("SELECT COUNT(*) as count FROM orders", "", []))['count'];

$recentOrders = executePreparedQuery(
  "SELECT orderID, orderNumber, fullName, orderDate, orderStatus FROM orders ORDER BY orderDate DESC LIMIT 5",
  "",
  []
);

$recentMessages = executePreparedQuery(
  "SELECT enquiryID, name, email, enquiryDate, status FROM enquiries ORDER BY enquiryDate DESC LIMIT 5",
  "",
  []
);
?>

<div class="header-bar">
  <h2 class="mb-0" style="color: #333; font-weight: 600;">Dashboard</h2>
</div>

<div class="row">
  <div class="col-md-4 mb-4">
    <div class="card stat-card stat-card-1">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <div>
            <h5 class="stat-label">Total Subscriber</h5>
            <h2 class="stat-number"><?php echo $stats['subscribers']; ?></h2>
          </div>
          <div class="stat-icon">
            <i class="fas fa-users"></i>
          </div>
        </div>
        <a href="subscriber.php" class="stat-link">View Details <i class="fas fa-arrow-right ms-1"></i></a>
      </div>
    </div>
  </div>
  <div class="col-md-4 mb-4">
    <div class="card stat-card stat-card-2">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <div>
            <h5 class="stat-label">Total Reg Users</h5>
            <h2 class="stat-number"><?php echo $stats['regUsers']; ?></h2>
          </div>
          <div class="stat-icon">
            <i class="fas fa-user-check"></i>
          </div>
        </div>
        <a href="reg-users.php" class="stat-link">View Details <i class="fas fa-arrow-right ms-1"></i></a>
      </div>
    </div>
  </div>
  <div class="col-md-4 mb-4">
    <div class="card stat-card stat-card-3">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <div>
            <h5 class="stat-label">Customer Messages (Read)</h5>
            <h2 class="stat-number"><?php echo $stats['readEnquiry']; ?></h2>
          </div>
          <div class="stat-icon">
            <i class="fas fa-envelope-open"></i>
          </div>
        </div>
        <a href="read-enquiry.php" class="stat-link">View Details <i class="fas fa-arrow-right ms-1"></i></a>
      </div>
    </div>
  </div>
  <div class="col-md-4 mb-4">
    <div class="card stat-card stat-card-4">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <div>
            <h5 class="stat-label">Customer Messages (Unread)</h5>
            <h2 class="stat-number"><?php echo $stats['unreadEnquiry']; ?></h2>
          </div>
          <div class="stat-icon">
            <i class="fas fa-envelope"></i>
          </div>
        </div>
        <a href="read-enquiry.php" class="stat-link">View Details <i class="fas fa-arrow-right ms-1"></i></a>
      </div>
    </div>
  </div>
  <div class="col-md-4 mb-4">
    <div class="card stat-card stat-card-5">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <div>
            <h5 class="stat-label">Total New Orders</h5>
            <h2 class="stat-number"><?php echo $stats['newOrders']; ?></h2>
          </div>
          <div class="stat-icon">
            <i class="fas fa-shopping-cart"></i>
          </div>
        </div>
        <a href="new-orders.php" class="stat-link">View Details <i class="fas fa-arrow-right ms-1"></i></a>
      </div>
    </div>
  </div>
  <div class="col-md-4 mb-4">
    <div class="card stat-card stat-card-6">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <div>
            <h5 class="stat-label">Total Confirmed Orders</h5>
            <h2 class="stat-number"><?php echo $stats['confirmedOrders']; ?></h2>
          </div>
          <div class="stat-icon">
            <i class="fas fa-check-circle"></i>
          </div>
        </div>
        <a href="confirmed-orders.php" class="stat-link">View Details <i class="fas fa-arrow-right ms-1"></i></a>
      </div>
    </div>
  </div>
  <div class="col-md-4 mb-4">
    <div class="card stat-card stat-card-7">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <div>
            <h5 class="stat-label">Total Delivered Orders</h5>
            <h2 class="stat-number"><?php echo $stats['deliveredOrders']; ?></h2>
          </div>
          <div class="stat-icon">
            <i class="fas fa-truck"></i>
          </div>
        </div>
        <a href="delivered-orders.php" class="stat-link">View Details <i class="fas fa-arrow-right ms-1"></i></a>
      </div>
    </div>
  </div>
  <div class="col-md-4 mb-4">
    <div class="card stat-card stat-card-8">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <div>
            <h5 class="stat-label">Total Cancelled Orders</h5>
            <h2 class="stat-number"><?php echo $stats['cancelledOrders']; ?></h2>
          </div>
          <div class="stat-icon">
            <i class="fas fa-times-circle"></i>
          </div>
        </div>
        <a href="cancelled-orders.php" class="stat-link">View Details <i class="fas fa-arrow-right ms-1"></i></a>
      </div>
    </div>
  </div>
  <div class="col-md-4 mb-4">
    <div class="card stat-card stat-card-9">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <div>
            <h5 class="stat-label">All Orders</h5>
            <h2 class="stat-number"><?php echo $stats['allOrders']; ?></h2>
          </div>
          <div class="stat-icon">
            <i class="fas fa-list-alt"></i>
          </div>
        </div>
        <a href="all-order.php" class="stat-link">View Details <i class="fas fa-arrow-right ms-1"></i></a>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-lg-7 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="mb-0" style="font-weight: 600;">Recent Orders</h5>
          <a href="all-order.php" class="btn btn-sm btn-outline-secondary">View All</a>
        </div>
        <div class="table-responsive">
          <table class="table table-striped mb-0">
            <thead>
              <tr>
                <th>Order #</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Status</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php if ($recentOrders && $recentOrders->num_rows > 0): ?>
                <?php while ($o = $recentOrders->fetch_assoc()): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($o['orderNumber']); ?></td>
                    <td><?php echo htmlspecialchars($o['fullName']); ?></td>
                    <td><?php echo htmlspecialchars($o['orderDate']); ?></td>
                    <td><?php echo htmlspecialchars($o['orderStatus']); ?></td>
                    <td><a class="btn btn-sm btn-primary" href="view-order-detail.php?viewid=<?php echo (int)$o['orderID']; ?>">Open</a></td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="5" class="text-center">No orders yet</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-5 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="mb-0" style="font-weight: 600;">Recent Customer Messages</h5>
          <a href="read-enquiry.php" class="btn btn-sm btn-outline-secondary">View All</a>
        </div>
        <div class="table-responsive">
          <table class="table table-striped mb-0">
            <thead>
              <tr>
                <th>Name</th>
                <th>Date</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($recentMessages && $recentMessages->num_rows > 0): ?>
                <?php while ($m = $recentMessages->fetch_assoc()): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($m['name']); ?></td>
                    <td><?php echo htmlspecialchars($m['enquiryDate']); ?></td>
                    <td>
                      <span class="badge bg-<?php echo ($m['status'] === 'Read') ? 'success' : 'warning'; ?>">
                        <?php echo htmlspecialchars($m['status']); ?>
                      </span>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="3" class="text-center">No messages yet</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include(__DIR__ . "/includes/footer.php"); ?>

