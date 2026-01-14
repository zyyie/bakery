<?php
session_start();
header('Content-Type: application/json');

$gateway_url = "http://192.168.18.112:8080";
$username = "sms";
$password = "1234567890";

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
if (!str_starts_with($recipient, '+')) {
    $recipient = '+' . $recipient;
}

$otp = rand(100000, 999999);
$message = "Your OTP is $otp. Do not share this code with anyone.";

$url = rtrim($gateway_url, '/') . '/messages';

$payload = [
    "phoneNumbers" => [$recipient],
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

// Store OTP in session
$_SESSION['otp'] = $_SESSION['otp'] ?? [];
$_SESSION['otp'][$recipient] = [
    'code' => (string)$otp,
    'exp' => time() + 300
];

echo json_encode([
    'ok' => true,
    'phone' => $recipient,
    'otp' => (string)$otp,
    'statusLine' => $statusLine,
    'response' => $response ?: null
]);
?>
