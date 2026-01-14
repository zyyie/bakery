<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

header('Content-Type: application/json');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid id']);
    exit;
}

// Check if inventory table exists
$hasInventory = false;
try {
    $invCheck = executePreparedQuery("SHOW TABLES LIKE 'inventory'", "", []);
    $hasInventory = ($invCheck && mysqli_num_rows($invCheck) > 0);
} catch (Exception $e) {
    $hasInventory = false;
}

if ($hasInventory) {
    $sql = "SELECT items.*, categories.categoryName, inv.stock_qty
            FROM items
            LEFT JOIN categories ON items.categoryID = categories.categoryID
            LEFT JOIN inventory inv ON inv.itemID = items.itemID
            WHERE items.itemID = ?";
} else {
    $sql = "SELECT items.*, categories.categoryName, 0 as stock_qty
            FROM items
            LEFT JOIN categories ON items.categoryID = categories.categoryID
            WHERE items.itemID = ?";
}

$result = executePreparedQuery($sql, "i", [$id]);
if (!$result) {
    $error = isset($GLOBALS['db_last_error']) ? $GLOBALS['db_last_error'] : 'Database query failed';
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $error]);
    exit;
}

if (mysqli_num_rows($result) === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Item not found']);
    exit;
}

$row = mysqli_fetch_assoc($result);

// Get images with error handling
$images = [];
try {
    $images = product_image_urls($row, 1, 3);
    if (empty($images)) {
        // Fallback to placeholder
        $images = ['frontend/images/placeholder.jpg'];
    }
} catch (Exception $e) {
    error_log("Error getting product images: " . $e->getMessage());
    $images = ['frontend/images/placeholder.jpg'];
}

echo json_encode([
    'itemID' => (int)$row['itemID'],
    'packageName' => (string)$row['packageName'],
    'categoryName' => (string)($row['categoryName'] ?? ''),
    'price' => number_format((float)$row['price'], 2, '.', ''),
    'foodDescription' => (string)($row['foodDescription'] ?? ''),
    'itemContains' => (string)($row['itemContains'] ?? ''),
    'stockQty' => isset($row['stock_qty']) ? (int)$row['stock_qty'] : 0,
    'deliveryWindow' => '9:00 AM – 6:00 PM',
    'deliveryLeadTime' => '1–2 days',
    'imageUrl' => $images[0] ?? 'frontend/images/placeholder.jpg',
    'images' => $images,
    'itemImage' => (string)($row['itemImage'] ?? ''),
], JSON_UNESCAPED_SLASHES);
