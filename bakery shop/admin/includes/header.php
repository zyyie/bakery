<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if(!isset($_SESSION['adminID'])){
  header("Location: login.php");
  exit();
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
  <link rel="stylesheet" href="../css/admin-style.css">
  </style>
</head>
<body>
  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar -->
      <div class="col-md-2 sidebar">
        <div class="logo-section">
          <img src="../logo.png" alt="Bakery Logo">
        </div>
        <nav class="nav flex-column">
          <a class="nav-link" href="dashboard.php">
            <i class="fas fa-home"></i> Dashboard
          </a>
          <a class="nav-link" href="#" data-bs-toggle="collapse" data-bs-target="#categoryMenu">
            <i class="fas fa-cog"></i> Item Category <i class="fas fa-chevron-right float-end"></i>
          </a>
          <div class="collapse" id="categoryMenu">
            <a class="nav-link ps-5" href="add-category.php">Add Category</a>
            <a class="nav-link ps-5" href="manage-category.php">Manage Category</a>
          </div>
          <a class="nav-link" href="#" data-bs-toggle="collapse" data-bs-target="#itemMenu">
            <i class="fas fa-list"></i> Items <i class="fas fa-chevron-right float-end"></i>
          </a>
          <div class="collapse" id="itemMenu">
            <a class="nav-link ps-5" href="add-food-package.php">Add Food Package</a>
            <a class="nav-link ps-5" href="manage-food-package.php">Manage Food Package</a>
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
            <i class="fas fa-list"></i> Enquiry
          </a>
          <a class="nav-link" href="subscriber.php">
            <i class="fas fa-chart-bar"></i> Subscribers
          </a>
          <a class="nav-link" href="reg-users.php">
            <i class="fas fa-users"></i> Reg Users
          </a>
          <a class="nav-link" href="#" data-bs-toggle="collapse" data-bs-target="#reportMenu">
            <i class="fas fa-list"></i> Reports <i class="fas fa-chevron-right float-end"></i>
          </a>
          <div class="collapse" id="reportMenu">
            <a class="nav-link ps-5" href="bw-dates-report.php">B/W Dates Report</a>
            <a class="nav-link ps-5" href="sales-reports.php">Sales Reports</a>
          </div>
          <a class="nav-link" href="search-order.php">
            <i class="fas fa-search"></i> Search Order
          </a>
          <a class="nav-link" href="#" data-bs-toggle="collapse" data-bs-target="#pageMenu">
            <i class="fas fa-check-square"></i> Page <i class="fas fa-chevron-right float-end"></i>
          </a>
          <div class="collapse" id="pageMenu">
            <a class="nav-link ps-5" href="aboutus.php">About Us</a>
            <a class="nav-link ps-5" href="contactus.php">Contact Us</a>
          </div>
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
               <select class="form-select d-inline-block" style="width: auto;">
                 <option>Select Language</option>
               </select>
               <i class="fas fa-bell ms-3"></i> <span class="badge bg-danger">3</span>
               <i class="fas fa-user ms-3"></i> Guest (2)
             </div>
           </div>
         </div>

