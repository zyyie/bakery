<?php
session_start();
include("connect.php");
require_once __DIR__ . '/includes/bootstrap.php';

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

// Load product images mapping from JSON file
$imagesMap = [];
$imagesJsonPath = __DIR__ . '/product-images.json';
if (file_exists($imagesJsonPath)) {
  $imagesJson = file_get_contents($imagesJsonPath);
  $imagesMap = json_decode($imagesJson, true) ?: [];
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
    // Get image from JSON mapping, fallback to database, then placeholder
    $productImage = 'https://via.placeholder.com/300x200';
    $packageName = $row['packageName'];
    
    if (isset($imagesMap[$packageName])) {
      $productImage = $imagesMap[$packageName];
      // Normalize path - convert 'bakery bread image' to 'bread image'
      $productImage = str_replace('bakery bread image/', 'bread image/', $productImage);
    } elseif (!empty($row['itemImage'])) {
      $productImage = 'bread image/' . $row['itemImage'];
    }
    
    // Resolve the actual image path
    $productImage = resolveImagePath($productImage);
    
    // Add resolved image path to the row
    $row['resolvedImage'] = imageUrl($productImage);
    $items[] = $row;
  }
}

echo json_encode($items);
?>

