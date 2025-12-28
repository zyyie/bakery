<?php
session_start();
include("connect.php");

header('Content-Type: application/json');

if(!isset($_GET['ids']) || empty($_GET['ids'])){
  echo json_encode([]);
  exit();
}

$ids = explode(',', $_GET['ids']);
$ids = array_map('intval', $ids);
$ids = array_filter($ids);
$ids = implode(',', $ids);

if(empty($ids)){
  echo json_encode([]);
  exit();
}

$query = "SELECT items.*, categories.categoryName FROM items 
          LEFT JOIN categories ON items.categoryID = categories.categoryID 
          WHERE items.itemID IN ($ids) AND items.status = 'Active'";
$result = executeQuery($query);

$items = array();
while($row = mysqli_fetch_assoc($result)){
  $items[] = $row;
}

echo json_encode($items);
?>

