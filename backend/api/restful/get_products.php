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

// Try remote inventory API first; fallback to DB if unavailable
$remoteHost = '192.168.18.171';
$remotePath = '/Finals_SCHOOLCANTEEN/partials/productloop.php';
$baseUrl = 'http://' . $remoteHost . $remotePath . '?api_key=' . rawurlencode($sharedKey);
$tryUrls = [
    $baseUrl,
    preg_replace('/^http:/i', 'https:', $baseUrl)
];
$remoteOk = false;
foreach ($tryUrls as $url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_TIMEOUT => 8,
        CURLOPT_HEADER => false,
    ]);
    if (stripos($url, 'https://') === 0) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    }
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE) ?: null;
    curl_close($ch);
    if ($body !== false && $code && $code >= 200 && $code < 300) {
        $decoded = json_decode($body, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            echo json_encode($decoded, JSON_UNESCAPED_UNICODE);
            $remoteOk = true;
            break;
        }
    }
}
if ($remoteOk) {
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

