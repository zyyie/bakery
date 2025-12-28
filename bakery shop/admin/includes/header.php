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
  <style>
    body {
      background-color: #f8fafc;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    }
    .sidebar {
      min-height: 100vh;
      background-color: #d4c4a8;
      padding: 20px 0;
      box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    }
    .sidebar .logo-section {
      padding: 20px;
      text-align: center;
      border-bottom: 1px solid rgba(0,0,0,0.1);
      margin-bottom: 20px;
    }
    .sidebar .logo-section img {
      height: 130px;
      margin-bottom: 10px;
    }
    .sidebar .logo-section h5 {
      color: #333;
      margin: 0;
      font-weight: 600;
    }
    .sidebar .nav-link {
      color: #333;
      padding: 12px 20px;
      margin: 2px 10px;
      border-radius: 8px;
      transition: all 0.3s;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .sidebar .nav-link:hover {
      background-color: #b8956a;
      color: #fff;
      transform: translateX(5px);
    }
    .sidebar .nav-link.active {
      background-color: #8b6f47;
      color: #fff;
      font-weight: 600;
    }
    .sidebar .nav-link i {
      width: 20px;
    }
    .main-content {
      padding: 30px;
      background-color: #f8fafc;
      min-height: 100vh;
    }
    .header-bar {
      background: white;
      padding: 20px 30px;
      border-radius: 12px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.05);
      margin-bottom: 30px;
      border-left: 4px solid #8b6f47;
    }
    .header-bar h2 {
      color: #1e293b;
      font-weight: 600;
      font-size: 1.75rem;
    }
    .stat-card {
      border-radius: 16px;
      padding: 28px;
      box-shadow: 0 2px 12px rgba(0,0,0,0.06);
      transition: all 0.3s ease;
      border: none;
      background: #fff;
      height: 100%;
    }
    .stat-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    }
    
    /* Brown and beige color palette - Alternating pattern */
    .stat-card-1 {
      background: linear-gradient(135deg, #f5f5dc 0%, #f0e6d2 100%);
      border-left: 4px solid #d4a574;
    }
    .stat-card-2 {
      background: linear-gradient(135deg, #e8dcc6 0%, #d4c4a8 100%);
      border-left: 4px solid #8b6f47;
    }
    .stat-card-3 {
      background: linear-gradient(135deg, #c9a961 0%, #b8956a 100%);
      border-left: 4px solid #6b5638;
    }
    .stat-card-4 {
      background: linear-gradient(135deg, #f5f5dc 0%, #f0e6d2 100%);
      border-left: 4px solid #d4a574;
    }
    .stat-card-5 {
      background: linear-gradient(135deg, #e8dcc6 0%, #d4c4a8 100%);
      border-left: 4px solid #8b6f47;
    }
    .stat-card-6 {
      background: linear-gradient(135deg, #c9a961 0%, #b8956a 100%);
      border-left: 4px solid #6b5638;
    }
    .stat-card-7 {
      background: linear-gradient(135deg, #f5f5dc 0%, #f0e6d2 100%);
      border-left: 4px solid #d4a574;
    }
    .stat-card-8 {
      background: linear-gradient(135deg, #e8dcc6 0%, #d4c4a8 100%);
      border-left: 4px solid #8b6f47;
    }
    .stat-card-9 {
      background: linear-gradient(135deg, #c9a961 0%, #b8956a 100%);
      border-left: 4px solid #6b5638;
    }
    
    .stat-label {
      color: #000;
      font-size: 0.875rem;
      font-weight: 500;
      margin-bottom: 8px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .stat-number {
      color: #000;
      font-size: 2.5rem;
      font-weight: 700;
      margin: 0;
      line-height: 1.2;
    }
    .stat-icon {
      width: 56px;
      height: 56px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      opacity: 0.8;
    }
    .stat-card-1 .stat-icon {
      background: rgba(212, 165, 116, 0.2);
      color: #8b6f47;
    }
    .stat-card-2 .stat-icon {
      background: rgba(139, 111, 71, 0.2);
      color: #6b5638;
    }
    .stat-card-3 .stat-icon {
      background: rgba(107, 86, 56, 0.25);
      color: #5a4630;
    }
    .stat-card-4 .stat-icon {
      background: rgba(212, 165, 116, 0.2);
      color: #8b6f47;
    }
    .stat-card-5 .stat-icon {
      background: rgba(139, 111, 71, 0.2);
      color: #6b5638;
    }
    .stat-card-6 .stat-icon {
      background: rgba(107, 86, 56, 0.25);
      color: #5a4630;
    }
    .stat-card-7 .stat-icon {
      background: rgba(212, 165, 116, 0.2);
      color: #8b6f47;
    }
    .stat-card-8 .stat-icon {
      background: rgba(139, 111, 71, 0.2);
      color: #6b5638;
    }
    .stat-card-9 .stat-icon {
      background: rgba(107, 86, 56, 0.25);
      color: #5a4630;
    }
    .stat-link {
      color: #000;
      text-decoration: none;
      font-size: 0.875rem;
      font-weight: 500;
      display: inline-flex;
      align-items: center;
      transition: all 0.2s;
      margin-top: 12px;
    }
    .stat-link:hover {
      color: #000;
      opacity: 0.8;
      transform: translateX(4px);
    }
    .stat-link i {
      font-size: 0.75rem;
    }
    /* Black text for all cards including darker brown cards */
    .stat-card-3 .stat-label,
    .stat-card-3 .stat-number,
    .stat-card-3 .stat-link,
    .stat-card-6 .stat-label,
    .stat-card-6 .stat-number,
    .stat-card-6 .stat-link,
    .stat-card-9 .stat-label,
    .stat-card-9 .stat-number,
    .stat-card-9 .stat-link {
      color: #000;
    }
    .stat-card-3 .stat-link:hover,
    .stat-card-6 .stat-link:hover,
    .stat-card-9 .stat-link:hover {
      color: #000;
      opacity: 0.8;
    }
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

