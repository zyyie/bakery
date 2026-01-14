<?php
require_once __DIR__ . '/../includes/bootstrap.php';

header('Content-Type: application/json');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$sharedKey = 'CARNICK-CANTEEN-2026';
$receivedKey = $_GET['api_key'] ?? '';

if (!hash_equals($sharedKey, (string)$receivedKey)) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid API Key']);
    exit;
}

// Inventory table may be optional; detect if it exists
$hasInventory = false;
try {
    $invCheck = executePreparedQuery("SHOW TABLES LIKE 'inventory'", "", []);
    $hasInventory = ($invCheck && mysqli_num_rows($invCheck) > 0);
} catch (Exception $e) {
    $hasInventory = false;
}

if ($hasInventory) {
    $sql = "SELECT i.packageName, i.foodDescription, i.price, inv.stock_qty
            FROM items i
            LEFT JOIN inventory inv ON inv.itemID = i.itemID";
} else {
    // Fallback when inventory table is missing
    $sql = "SELECT i.packageName, i.foodDescription, i.price, 0 AS stock_qty
            FROM items i";
}

$res = executePreparedQuery($sql, "", []);

if ($res === false) {
    $error = $GLOBALS['db_last_error'] ?? 'Database query failed';
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $error]);
    exit;
}

$data = [];
while ($row = $res->fetch_assoc()) {
    $data[] = [
        'name' => (string)$row['packageName'],
        'description' => (string)($row['foodDescription'] ?? ''),
        'price' => (float)$row['price'],
        'stocks' => isset($row['stock_qty']) ? (int)$row['stock_qty'] : 0,
        'source' => 'carnick',
    ];
}

echo json_encode($data, JSON_UNESCAPED_UNICODE);

