<?php
require_once __DIR__ . '/bootstrap.php';
requireAdminLogin();
adminRegenerateSession();
$notif = adminGetNotifications();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Panel - Bakery Management</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../../frontend/css/admin-style.css">
  </style>
</head>
<body>
  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar -->
      <div class="col-md-2 sidebar">
        <div class="logo-section">
          <img src="../../frontend/images/logo.png" alt="Bakery Logo">
        </div>
        <nav class="nav flex-column">
          <a class="nav-link" href="dashboard.php">
            <i class="fas fa-home"></i> Dashboard
          </a>
          <a class="nav-link" href="#" data-bs-toggle="collapse" data-bs-target="#itemMenu">
            <i class="fas fa-list"></i> Items <i class="fas fa-chevron-right float-end"></i>
          </a>
          <div class="collapse" id="itemMenu">
            <a class="nav-link ps-5" href="manage-category.php">Categories</a>
            <a class="nav-link ps-5" href="add-food-package.php">Add Food Package</a>
            <a class="nav-link ps-5" href="manage-food-package.php">Manage Food Package</a>
            <a class="nav-link ps-5" href="inventory-management.php">Inventory</a>
          </div>
          <a class="nav-link" href="#" data-bs-toggle="collapse" data-bs-target="#orderMenu">
            <i class="fas fa-file-alt"></i> Orders <i class="fas fa-chevron-right float-end"></i>
          </a>
          <div class="collapse" id="orderMenu">
            <a class="nav-link ps-5" href="new-orders.php">New Orders</a>
            <a class="nav-link ps-5" href="confirmed-orders.php">Confirmed</a>
            <a class="nav-link ps-5" href="on-the-way-orders.php">On The Way</a>
            <a class="nav-link ps-5" href="delivered-orders.php">Delivered</a>
            <a class="nav-link ps-5" href="cancelled-orders.php">Cancelled</a>
            <a class="nav-link ps-5" href="all-order.php">All Orders</a>
          </div>
          <a class="nav-link" href="read-enquiry.php">
            <i class="fas fa-list"></i> Customer Messages
          </a>
          <a class="nav-link" href="account.php">
            <i class="fas fa-user-cog"></i> Admin Account
          </a>
          <a class="nav-link" href="logout.php">
            <i class="fas fa-sign-out-alt"></i> Logout
          </a>
        </nav>
      </div>
      
       <!-- Main Content -->
       <div class="col-md-10 main-content">
         <div class="header-bar">
           <div class="d-flex justify-content-between align-items-center">
             <div>
               <h4 class="mb-0" style="color: #8B4513; font-weight: 600;"><?php echo date('D M d Y H:i:s'); ?></h4>
            </div>
            <div>
              <div class="dropdown d-inline-block">
                <a class="text-decoration-none" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="fas fa-bell"></i>
                  <?php if (!empty($notif['total'])): ?>
                    <span class="badge bg-danger"><?php echo (int)$notif['total']; ?></span>
                  <?php endif; ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                  <?php if (empty($notif['items'])): ?>
                    <li><span class="dropdown-item-text">No notifications</span></li>
                  <?php else: ?>
                    <?php foreach ($notif['items'] as $n): ?>
                      <li>
                        <a class="dropdown-item" href="<?php echo htmlspecialchars($n['url']); ?>">
                          <?php echo htmlspecialchars($n['label']); ?>
                          <span class="badge bg-danger ms-2"><?php echo (int)$n['count']; ?></span>
                        </a>
                      </li>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </ul>
              </div>

              <div class="dropdown d-inline-block ms-3">
                <a class="text-decoration-none" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['adminUsername'] ?? 'Admin'); ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li><a class="dropdown-item" href="account.php">Account</a></li>
                  <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                </ul>
              </div>
            </div>
          </div>
        </div>
