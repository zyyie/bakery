<?php
require_once __DIR__ . '/../../includes/bootstrap.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!isset($_SESSION['userID'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$body = file_get_contents('php://input');
$data = json_decode($body, true);
$orderId = $data['orderID'] ?? '';

if ($orderId === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Missing orderID']);
    exit;
}

$cfg = require __DIR__ . '/../../config/paypal.php';

if (empty($cfg['client_id']) || empty($cfg['client_secret'])) {
    http_response_code(500);
    echo json_encode(['error' => 'PayPal is not configured']);
    exit;
}

$base = ($cfg['mode'] === 'live') ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $base . '/v1/oauth2/token',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_USERPWD => $cfg['client_id'] . ':' . $cfg['client_secret'],
    CURLOPT_HTTPHEADER => ['Accept: application/json', 'Accept-Language: en_US'],
    CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
]);

$authResp = curl_exec($ch);
if ($authResp === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to connect to PayPal']);
    exit;
}
$authJson = json_decode($authResp, true);
$token = $authJson['access_token'] ?? null;
if (!$token) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to authenticate with PayPal']);
    exit;
}

curl_setopt_array($ch, [
    CURLOPT_URL => $base . '/v2/checkout/orders/' . rawurlencode($orderId) . '/capture',
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token,
    ],
    CURLOPT_POSTFIELDS => '{}',
]);

$captureResp = curl_exec($ch);
curl_close($ch);

if ($captureResp === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to capture PayPal order']);
    exit;
}

echo $captureResp;
