<?php
require_once __DIR__ . '/bootstrap.php';
requireAdminLogin();
adminRegenerateSession();
$notif = adminGetNotifications();

// Calculate base path for navigation links
// Get the directory of the current script relative to admin folder
$currentScript = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$adminPos = strpos($currentScript, '/admin/');
$adminBasePath = '';
if ($adminPos !== false) {
    $pathAfterAdmin = substr($currentScript, $adminPos + 7); // +7 for '/admin/'
    // Remove filename, keep only directory path
    $lastSlash = strrpos($pathAfterAdmin, '/');
    if ($lastSlash !== false) {
        $dirPath = substr($pathAfterAdmin, 0, $lastSlash + 1);
        $depth = substr_count($dirPath, '/');
        $adminBasePath = str_repeat('../', $depth);
    }
}

// Compute app base path (URL path up to but not including '/backend/') for linking shared assets like CSS
$scriptName = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? ''));
$backendPos = strpos($scriptName, '/backend/');
$appBasePath = '';
if ($backendPos !== false) {
    $appBasePath = substr($scriptName, 0, $backendPos);
} else {
    // Fallback: try to detect from document root
    $docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? '');
    $scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? '');
    if (strpos($scriptPath, $docRoot) === 0) {
        $relativePath = substr($scriptPath, strlen($docRoot));
        $backendPos2 = strpos($relativePath, '/backend/');
        if ($backendPos2 !== false) {
            $appBasePath = substr($relativePath, 0, $backendPos2);
        }
    }
}
// Ensure appBasePath starts with / if not empty, and remove trailing slash
if ($appBasePath !== '') {
    if ($appBasePath[0] !== '/') {
        $appBasePath = '/' . $appBasePath;
    }
    $appBasePath = rtrim($appBasePath, '/');
} else {
    // Final fallback: calculate relative path from current location
    // Count how many directories deep we are from admin root
    $currentDir = dirname(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''));
    $adminDir = str_replace('\\', '/', __DIR__ . '/..');
    $adminDir = str_replace('\\', '/', realpath($adminDir));
    $docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? '');
    if ($adminDir && strpos($adminDir, $docRoot) === 0) {
        $adminRelative = substr($adminDir, strlen($docRoot));
        $depth = substr_count($adminRelative, '/') - 1; // -1 because we're in includes/
        if ($depth > 0) {
            $appBasePath = str_repeat('../', $depth);
            $appBasePath = rtrim($appBasePath, '/');
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Panel - Bakery Management</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <?php
  // Calculate CSS path - always use relative path from includes/ directory
  $cssPath = '../../frontend/css/admin-style.css';
  if ($appBasePath && $appBasePath !== '') {
      $cssPath = $appBasePath . '/frontend/css/admin-style.css';
  }
  ?>
  <link rel="stylesheet" href="<?php echo htmlspecialchars($cssPath); ?>?v=<?php echo time(); ?>">
</head>
<body>
  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar -->
      <div class="col-md-2 sidebar">
        <div class="logo-section">
          <?php
          // Calculate logo path - always use relative path from includes/ directory
          $logoPath = '../../frontend/images/logo.png';
          if ($appBasePath && $appBasePath !== '') {
              $logoPath = $appBasePath . '/frontend/images/logo.png';
          }
          ?>
          <img src="<?php echo htmlspecialchars($logoPath); ?>" alt="Bakery Logo">
        </div>
        <nav class="nav flex-column">
          <?php
          // Determine active page
          $currentPage = basename($_SERVER['PHP_SELF']);
          $currentPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
          $isDashboard = strpos($currentPath, 'dashboard.php') !== false;
          $isCustomerMessages = strpos($currentPath, 'read-enquiry.php') !== false;
          $isSmsMessages = strpos($currentPath, 'sms-messages.php') !== false;
          ?>
          <a class="nav-link <?php echo $isDashboard ? 'active' : ''; ?>" href="<?php echo $adminBasePath; ?>dashboard.php">
            <i class="fas fa-home"></i> Dashboard
          </a>
          <a class="nav-link" href="#" data-bs-toggle="collapse" data-bs-target="#itemMenu">
            <i class="fas fa-list"></i> Items <i class="fas fa-chevron-right float-end"></i>
          </a>
          <div class="collapse" id="itemMenu">
            <a class="nav-link ps-5" href="<?php echo $adminBasePath; ?>catalog/manage-category.php">Categories</a>
            <a class="nav-link ps-5" href="<?php echo $adminBasePath; ?>catalog/add-food-package.php">Add Food Package</a>
            <a class="nav-link ps-5" href="<?php echo $adminBasePath; ?>catalog/manage-food-package.php">Manage Food Package</a>
            <a class="nav-link ps-5" href="<?php echo $adminBasePath; ?>catalog/inventory-management.php">Inventory</a>
          </div>
          <a class="nav-link" href="#" data-bs-toggle="collapse" data-bs-target="#orderMenu">
            <i class="fas fa-file-alt"></i> Orders <i class="fas fa-chevron-right float-end"></i>
          </a>
          <div class="collapse" id="orderMenu">
            <a class="nav-link ps-5" href="<?php echo $adminBasePath; ?>orders/new-orders.php">New Orders</a>
            <a class="nav-link ps-5" href="<?php echo $adminBasePath; ?>orders/confirmed-orders.php">Confirmed</a>
            <a class="nav-link ps-5" href="<?php echo $adminBasePath; ?>orders/on-the-way-orders.php">On The Way</a>
            <a class="nav-link ps-5" href="<?php echo $adminBasePath; ?>orders/delivered-orders.php">Delivered</a>
            <a class="nav-link ps-5" href="<?php echo $adminBasePath; ?>orders/cancelled-orders.php">Cancelled</a>
            <a class="nav-link ps-5" href="<?php echo $adminBasePath; ?>orders/all-order.php">All Orders</a>
          </div>
          <a class="nav-link <?php echo $isCustomerMessages ? 'active' : ''; ?>" href="<?php echo $adminBasePath; ?>messages/read-enquiry.php">
            <i class="fas fa-list"></i> Customer Messages
          </a>
          <a class="nav-link <?php echo $isSmsMessages ? 'active' : ''; ?>" href="<?php echo $adminBasePath; ?>messages/sms-messages.php">
            <i class="fas fa-sms"></i> SMS Messages
          </a>
          <a class="nav-link" href="#" data-bs-toggle="collapse" data-bs-target="#reportMenu">
            <i class="fas fa-chart-line"></i> Reports <i class="fas fa-chevron-right float-end"></i>
          </a>
          <div class="collapse" id="reportMenu">
            <a class="nav-link ps-5" href="<?php echo $adminBasePath; ?>reports/api-sales-report.php">API Sales Report</a>
          </div>
          <a class="nav-link" href="<?php echo $adminBasePath; ?>auth/account.php">
            <i class="fas fa-user-cog"></i> Admin Account
          </a>
          <a class="nav-link" href="<?php echo $adminBasePath; ?>auth/logout.php">
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
                        <a class="dropdown-item" href="<?php echo htmlspecialchars($adminBasePath . $n['url']); ?>">
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
                  <li><a class="dropdown-item" href="<?php echo $adminBasePath; ?>auth/account.php">Account</a></li>
                  <li><a class="dropdown-item" href="<?php echo $adminBasePath; ?>auth/logout.php">Logout</a></li>
                </ul>
              </div>
            </div>
          </div>
        </div>
