<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

header('Content-Type: application/json');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

$itemID = 0;
$quantity = 1;

if (is_array($data)) {
    if (isset($data['itemID'])) {
        $itemID = intval($data['itemID']);
    }
    if (isset($data['quantity'])) {
        $quantity = intval($data['quantity']);
    }
}

if ($itemID <= 0 || $quantity <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid item or quantity']);
    exit;
}

// Ensure item exists
$res = executePreparedQuery("SELECT itemID FROM items WHERE itemID = ? AND status = 'Active'", "i", [$itemID]);
if (!$res || mysqli_num_rows($res) === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Item not found']);
    exit;
}

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_SESSION['cart'][$itemID])) {
    $_SESSION['cart'][$itemID] += $quantity;
} else {
    $_SESSION['cart'][$itemID] = $quantity;
}

$count = 0;
foreach ($_SESSION['cart'] as $q) {
    $count += (int)$q;
}

echo json_encode(['ok' => true, 'cartCount' => $count]);
