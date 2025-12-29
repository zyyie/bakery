<?php
session_start();
include("../connect.php");

if(isset($_POST['orderID'])){
  $orderID = intval($_POST['orderID']);
  $remark = trim($_POST['remark']);
  $orderStatus = trim($_POST['orderStatus']);
  
  // Validate order status
  $validStatuses = ['Still Pending', 'Confirmed', 'On The Way', 'Delivered', 'Cancelled'];
  if(!in_array($orderStatus, $validStatuses)){
    $orderStatus = 'Still Pending';
  }
  
  $query = "UPDATE orders SET remark = ?, orderStatus = ? WHERE orderID = ?";
  executePreparedUpdate($query, "ssi", [$remark, $orderStatus, $orderID]);
  
  header("Location: view-order-detail.php?viewid=$orderID");
  exit();
}
?>

