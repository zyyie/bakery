<?php
require_once __DIR__ . '/../../config/connect.php';

header('Content-Type: application/json');

// Get input from POST data (form or JSON)
$input = [];
if (($_SERVER['CONTENT_TYPE'] ?? '') === 'application/json') {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true) ?: [];
} else {
    $input = $_POST;
}

$recipient = trim($input['phone'] ?? $input['phoneNumber'] ?? '');
$message = trim($input['message'] ?? $input['text'] ?? '');

if (empty($recipient) || empty($message)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Missing phone number or message']);
    exit;
}

// Clean and validate phone number
if (!str_starts_with($recipient, '+')) {
    $recipient = '+' . $recipient;
}

// SMS Gateway configuration
$smsConfig = require __DIR__ . '/../../config/sms.php';
$gateway_url = $smsConfig['gateway_url'] ?? 'http://10.179.50.3:8080';
$username = $smsConfig['gateway_username'] ?? 'sms';
$password = $smsConfig['gateway_password'] ?? '1234567890';

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

// Parse response
$responseData = json_decode($response, true);
$messageId = $responseData['messageId'] ?? $responseData['id'] ?? null;
$success = strpos($statusLine, '200') !== false || strpos($statusLine, '201') !== false;

// Store in database
$status = $success ? 'sent' : 'failed';
$error = $success ? null : ($responseData['error'] ?? $response ?? 'Unknown error');

$query = "INSERT INTO sms_messages (phoneNumber, message, direction, status, messageID, error) VALUES (?, ?, 'outbound', ?, ?, ?)";
$dbResult = executePreparedUpdate($query, "sssss", [$recipient, $message, $status, $messageId ?? '', $error ?? '']);

// Log
$logFile = __DIR__ . "/../../logs/sms_log.txt";
$timestamp = date("Y-m-d H:i:s");
file_put_contents($logFile, "[$timestamp] OUTBOUND: To $recipient: $message (Status: $status)" . PHP_EOL, FILE_APPEND);

echo json_encode([
    'ok' => $success,
    'phone' => $recipient,
    'message' => $message,
    'messageId' => $messageId,
    'status' => $status,
    'statusLine' => $statusLine,
    'response' => $responseData,
    'stored' => $dbResult !== false
]);
?>
