<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
requireAdminLogin();
adminRegenerateSession();

header('Content-Type: application/json');

if(isset($_POST['orderID']) && isset($_POST['orderStatus'])){
  $orderID = intval($_POST['orderID']);
  $orderStatus = trim($_POST['orderStatus']);
  
  // Validate order status
  $validStatuses = ['Still Pending', 'Confirmed', 'On The Way', 'Delivered', 'Cancelled'];
  if(!in_array($orderStatus, $validStatuses)){
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
  }
  
  $query = "UPDATE orders SET orderStatus = ? WHERE orderID = ?";
  $result = executePreparedUpdate($query, "si", [$orderStatus, $orderID]);
  
  if($result !== false){
    echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
  } else {
    echo json_encode(['success' => false, 'message' => 'Failed to update status']);
  }
} else {
  echo json_encode(['success' => false, 'message' => 'Missing parameters']);
}
?>

