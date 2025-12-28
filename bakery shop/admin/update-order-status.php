<?php
session_start();
include("../connect.php");

if(isset($_POST['orderID'])){
  $orderID = intval($_POST['orderID']);
  $remark = $_POST['remark'];
  $orderStatus = $_POST['orderStatus'];
  
  $query = "UPDATE orders SET remark = '$remark', orderStatus = '$orderStatus' WHERE orderID = $orderID";
  executeQuery($query);
  
  header("Location: view-order-detail.php?viewid=$orderID");
  exit();
}
?>

