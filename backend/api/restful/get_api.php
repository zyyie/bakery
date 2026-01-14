<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../config/connect.php';

header('Content-Type: application/json');

$apiKey = isset($_GET['key']) ? trim((string)$_GET['key']) : '';

$expectedKey = '';
$secretsPath = __DIR__ . '/../config/secrets.local.php';
if (file_exists($secretsPath)) {
    $secrets = require $secretsPath;
    if (is_array($secrets) && isset($secrets['public_api_key'])) {
        $expectedKey = (string)$secrets['public_api_key'];
    }
}
if ($expectedKey === '') {
    $expectedKey = (string)getenv('BAKERY_PUBLIC_API_KEY');
}

if ($expectedKey === '' || $apiKey !== $expectedKey) {
    http_response_code(401);
    echo json_encode([
        'ok' => false,
        'error' => 'Unauthorized',
    ], JSON_UNESCAPED_SLASHES);
    exit;
}

try {
    $sql = "SELECT i.itemID, i.itemName, i.itemDescription, i.itemPrice, i.itemImage, i.categoryID,
                   c.categoryName,
                   COALESCE(inv.quantity, 0) AS stock
            FROM items i
            LEFT JOIN categories c ON c.categoryID = i.categoryID
            LEFT JOIN inventory inv ON inv.itemID = i.itemID";

    $result = executeQuery($sql);
    if ($result === false) {
        throw new Exception('Database query failed');
    }

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $itemId = (int)$row['itemID'];

        $items[] = [
            'itemID' => $itemId,
            'itemName' => $row['itemName'],
            'itemDescription' => $row['itemDescription'],
            'itemPrice' => (float)$row['itemPrice'],
            'categoryID' => isset($row['categoryID']) ? (int)$row['categoryID'] : null,
            'categoryName' => $row['categoryName'] ?? null,
            'stock' => (int)$row['stock'],
            'images' => product_image_urls($itemId, $row),
        ];
    }

    echo json_encode([
        'ok' => true,
        'count' => count($items),
        'items' => $items,
    ], JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'Server error',
    ], JSON_UNESCAPED_SLASHES);
}
