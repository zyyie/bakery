<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
requireAdminLogin();
adminRegenerateSession();

if(isset($_POST['orderID'])){
  $orderID = intval($_POST['orderID']);
  $remark = trim($_POST['remark']);
  $orderStatus = trim($_POST['orderStatus']);
  $deliveryDate = trim($_POST['deliveryDate'] ?? '');
  
  // Validate order status
  $validStatuses = ['Still Pending', 'Confirmed', 'On The Way', 'Delivered', 'Cancelled'];
  if(!in_array($orderStatus, $validStatuses)){
    $orderStatus = 'Still Pending';
  }
  
  // Normalize delivery date to YYYY-MM-DD or set to NULL if empty
  if (!empty($deliveryDate)) {
    if (strpos($deliveryDate, '/') !== false) {
      $dt = DateTime::createFromFormat('d/m/Y', $deliveryDate);
      if ($dt instanceof DateTime) {
        $deliveryDate = $dt->format('Y-m-d');
      }
    } else {
      $ts = strtotime($deliveryDate);
      if ($ts !== false) {
        $deliveryDate = date('Y-m-d', $ts);
      }
    }
  } else {
    $deliveryDate = null;
  }
  
  // Check if deliveryDate column exists before updating
  $colCheck = executePreparedQuery("SHOW COLUMNS FROM orders LIKE 'deliveryDate'", "", []);
  if ($colCheck && mysqli_num_rows($colCheck) > 0) {
    if ($deliveryDate !== null) {
      $query = "UPDATE orders SET remark = ?, orderStatus = ?, deliveryDate = ? WHERE orderID = ?";
      executePreparedUpdate($query, "sssi", [$remark, $orderStatus, $deliveryDate, $orderID]);
    } else {
      $query = "UPDATE orders SET remark = ?, orderStatus = ? WHERE orderID = ?";
      executePreparedUpdate($query, "ssi", [$remark, $orderStatus, $orderID]);
    }
  } else {
    $query = "UPDATE orders SET remark = ?, orderStatus = ? WHERE orderID = ?";
    executePreparedUpdate($query, "ssi", [$remark, $orderStatus, $orderID]);
  }
  
  header("Location: view-order-detail.php?viewid=$orderID");
  exit();
}
?>

