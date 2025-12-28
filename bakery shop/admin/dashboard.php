<?php
session_start();
include("../connect.php");
include("includes/header.php");

// Get statistics
$stats = array();

$stats['subscribers'] = mysqli_fetch_assoc(executeQuery("SELECT COUNT(*) as count FROM subscribers"))['count'];
$stats['regUsers'] = mysqli_fetch_assoc(executeQuery("SELECT COUNT(*) as count FROM users"))['count'];
$stats['readEnquiry'] = mysqli_fetch_assoc(executeQuery("SELECT COUNT(*) as count FROM enquiries WHERE status = 'Read'"))['count'];
$stats['unreadEnquiry'] = mysqli_fetch_assoc(executeQuery("SELECT COUNT(*) as count FROM enquiries WHERE status = 'Unread'"))['count'];
$stats['newOrders'] = mysqli_fetch_assoc(executeQuery("SELECT COUNT(*) as count FROM orders WHERE orderStatus = 'Still Pending'"))['count'];
$stats['confirmedOrders'] = mysqli_fetch_assoc(executeQuery("SELECT COUNT(*) as count FROM orders WHERE orderStatus = 'Confirmed'"))['count'];
$stats['deliveredOrders'] = mysqli_fetch_assoc(executeQuery("SELECT COUNT(*) as count FROM orders WHERE orderStatus = 'Delivered'"))['count'];
$stats['cancelledOrders'] = mysqli_fetch_assoc(executeQuery("SELECT COUNT(*) as count FROM orders WHERE orderStatus = 'Cancelled'"))['count'];
$stats['allOrders'] = mysqli_fetch_assoc(executeQuery("SELECT COUNT(*) as count FROM orders"))['count'];
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
            <h5 class="stat-label">Total Reads Enquiry</h5>
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
            <h5 class="stat-label">Total Unreads Enquiry</h5>
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

<?php include("includes/footer.php"); ?>

