<?php
session_start();
header('Content-Type: application/json');

// SMS Gateway configuration
$smsConfig = require __DIR__ . '/../../config/sms.php';
$gateway_url = $smsConfig['gateway_url'] ?? 'http://192.168.18.112:8080';
$username = $smsConfig['gateway_username'] ?? 'sms';
$password = $smsConfig['gateway_password'] ?? '1234567890';

// Get recipient from POST data (form or JSON)
$input = [];
if (($_SERVER['CONTENT_TYPE'] ?? '') === 'application/json') {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true) ?: [];
} else {
    $input = $_POST;
}

$recipient = $input['phone'] ?? '';

if (empty($recipient)) {
    echo json_encode(['ok' => false, 'error' => 'Missing phone']);
    exit;
}

// Clean and validate phone number
$recipient = trim($recipient);
$digits = preg_replace('/\D+/', '', $recipient);
if (strlen($digits) < 7 || strlen($digits) > 15) {
    echo json_encode(['ok' => false, 'error' => 'Invalid phone number']);
    exit;
}
// Keep display/submit format with + for SMS gateway, but use digits-only as session key
$gatewayPhone = '+' . $digits;

$otp = rand(100000, 999999);
$message = "Your OTP is $otp. Do not share this code with anyone.";

$url = rtrim($gateway_url, '/') . '/messages';

$payload = [
    "phoneNumbers" => [$gatewayPhone],
    "textMessage" => ["text" => $message],
    "withDeliveryReport" => true
];

$options = [
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode("$username:$password")
        ],
        'content' => json_encode($payload),
        'ignore_errors' => true,
        'timeout' => 30
    ]
];

$context = stream_context_create($options);
$response = @file_get_contents($url, false, $context);
$statusLine = isset($http_response_header[0]) ? $http_response_header[0] : 'HTTP/1.1 (no status)';

// Store OTP in session using normalized key
$_SESSION['otp'] = $_SESSION['otp'] ?? [];
$_SESSION['otp'][$digits] = [
    'code' => (string)$otp,
    'sent_at' => time(),
    'exp' => time() + 300,
    'attempts' => 0,
    'max_attempts' => 5,
];

echo json_encode([
    'ok' => true,
    'phone' => $gatewayPhone,
    'otp' => (string)$otp,
    'statusLine' => $statusLine,
    'response' => $response ?: null
]);
?>
