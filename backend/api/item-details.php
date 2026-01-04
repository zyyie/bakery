<?php
require_once __DIR__ . '/../includes/bootstrap.php';

header('Content-Type: application/json');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid id']);
    exit;
}

$sql = "SELECT items.*, categories.categoryName, inv.stock_qty
        FROM items
        LEFT JOIN categories ON items.categoryID = categories.categoryID
        LEFT JOIN inventory inv ON inv.itemID = items.itemID
        WHERE items.itemID = ?";

$result = executePreparedQuery($sql, "i", [$id]);
if (!$result || mysqli_num_rows($result) === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Item not found']);
    exit;
}

$row = mysqli_fetch_assoc($result);

$images = product_image_urls($row, 1, 3);

echo json_encode([
    'itemID' => (int)$row['itemID'],
    'packageName' => (string)$row['packageName'],
    'categoryName' => (string)($row['categoryName'] ?? ''),
    'price' => (string)$row['price'],
    'foodDescription' => (string)($row['foodDescription'] ?? ''),
    'itemContains' => (string)($row['itemContains'] ?? ''),
    'stockQty' => isset($row['stock_qty']) ? (int)$row['stock_qty'] : 0,
    'deliveryWindow' => '9:00 AM – 6:00 PM',
    'deliveryLeadTime' => '1–2 days',
    'imageUrl' => $images[0] ?? '',
    'images' => $images,
    'itemImage' => (string)($row['itemImage'] ?? ''),
]);
