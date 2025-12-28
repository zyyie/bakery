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

if(empty($ids)){
  echo json_encode([]);
  exit();
}

// Build prepared statement with placeholders
$placeholders = str_repeat('?,', count($ids) - 1) . '?';
$query = "SELECT items.*, categories.categoryName FROM items 
          LEFT JOIN categories ON items.categoryID = categories.categoryID 
          WHERE items.itemID IN ($placeholders) AND items.status = 'Active'";

$types = str_repeat('i', count($ids));
$result = executePreparedQuery($query, $types, $ids);

$items = array();
if($result){
  while($row = mysqli_fetch_assoc($result)){
    $items[] = $row;
  }
}

echo json_encode($items);
?>

