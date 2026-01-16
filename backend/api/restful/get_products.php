<?php
// Siguraduhin na tama ang path ng bootstrap para makuha ang $conn (database connection)
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

// 1. API KEY CHECK
$sharedKey = 'CARNICK-CANTEEN-2026';
$receivedKey = $_GET['api_key'] ?? '';

if ($receivedKey !== $sharedKey) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid API Key']);
    exit;
}

/**
 * 2. LIVE DATA QUERY
 * Gagamit tayo ng direktang query sa $conn para iwas sa cache ng executePreparedQuery.
 * Sinisiguro nito na kung ano ang nasa database ngayon, iyon ang lalabas.
 */
$conn = $GLOBALS['conn'];

$sql = "SELECT 
            i.packageName, 
            i.foodDescription, 
            i.price, 
            COALESCE(inv.stock_qty, 0) AS stock_qty
        FROM items i
        LEFT JOIN inventory inv ON i.itemID = inv.itemID
        ORDER BY i.packageName ASC";

$result = $conn->query($sql);

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Database Query Failed: ' . $conn->error]);
    exit;
}

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'name'        => (string)$row['packageName'],
        'description' => (string)($row['foodDescription'] ?? ''),
        'price'       => (float)$row['price'],
        'stocks'      => (int)$row['stock_qty'],
        'source'      => 'carnick'
    ];
}

// 3. CLEAN JSON OUTPUT
echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
exit();

