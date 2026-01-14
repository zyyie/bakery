<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
requireAdminLogin();
adminRegenerateSession();

header('Content-Type: application/json');

if(isset($_POST['orderID']) && isset($_POST['deliveryDate'])){
  $orderID = intval($_POST['orderID']);
  $deliveryDate = trim($_POST['deliveryDate']);
  
  // Validate date format
  if(empty($deliveryDate)){
    echo json_encode(['success' => false, 'message' => 'Delivery date is required']);
    exit();
  }
  
  // Normalize delivery date to YYYY-MM-DD
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
  
  // Check if deliveryDate column exists
  $colCheck = executePreparedQuery("SHOW COLUMNS FROM orders LIKE 'deliveryDate'", "", []);
  if ($colCheck && mysqli_num_rows($colCheck) > 0) {
    $query = "UPDATE orders SET deliveryDate = ? WHERE orderID = ?";
    $result = executePreparedUpdate($query, "si", [$deliveryDate, $orderID]);
    
    if($result !== false){
      // If delivery date is today or past and status is "On The Way", auto-update to "Delivered"
      $today = date('Y-m-d');
      if($deliveryDate <= $today){
        $statusCheck = executePreparedQuery("SELECT orderStatus FROM orders WHERE orderID = ?", "i", [$orderID]);
        if($statusCheck && mysqli_num_rows($statusCheck) > 0){
          $orderData = mysqli_fetch_assoc($statusCheck);
          if($orderData['orderStatus'] == 'On The Way'){
            $updateStatusQuery = "UPDATE orders SET orderStatus = 'Delivered' WHERE orderID = ?";
            executePreparedUpdate($updateStatusQuery, "i", [$orderID]);
          }
        }
      }
      
      echo json_encode(['success' => true, 'message' => 'Delivery date updated successfully']);
    } else {
      echo json_encode(['success' => false, 'message' => 'Failed to update delivery date']);
    }
  } else {
    echo json_encode(['success' => false, 'message' => 'Delivery date column does not exist']);
  }
} else {
  echo json_encode(['success' => false, 'message' => 'Missing parameters']);
}
?>

