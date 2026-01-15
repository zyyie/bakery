<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

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

/**
 * 1. INAYOS NA SQL QUERY
 * - Isinama ang 'i.itemID' para sa tracking.
 * - Nag-LEFT JOIN sa 'item_images' para makuha ang 'image_path'.
 * - Naglagay ng CONDITION na 'img.is_primary = 1' para isang picture lang ang lumabas.
 */
$sql = "SELECT 
            i.itemID, 
            i.packageName, 
            i.foodDescription, 
            i.price, 
            inv.stock_qty, 
            img.image_path
        FROM items i
        LEFT JOIN inventory inv ON inv.itemID = i.itemID
        LEFT JOIN item_images img ON img.itemID = i.itemID AND img.is_primary = 1";

$res = executePreparedQuery($sql, "", []);

if ($res === false) {
    $error = $GLOBALS['db_last_error'] ?? 'Database query failed';
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $error]);
    exit;
}

$data = [];
while ($row = $res->fetch_assoc()) {
    /**
     * 2. INAYOS NA DATA MAPPING
     * - Isinama ang 'id' (itemID) para magamit sa cart/checkout.
     * - Isinama ang 'image_url' para lumitaw ang picture sa side ng Canteen.
     */
    $data[] = [
        'id'          => (int)$row['itemID'],
        'name'        => (string)$row['packageName'],
        'description' => (string)($row['foodDescription'] ?? ''),
        'price'       => (float)$row['price'],
        'stocks'      => isset($row['stock_qty']) ? (int)$row['stock_qty'] : 0,
        'image_url'   => !empty($row['image_path']) ? $row['image_path'] : '', // Ipapasa ang path ng primary image
        'source'      => 'carnick',
    ];
}

echo json_encode($data, JSON_UNESCAPED_UNICODE);