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

// Load logger helper
require_once __DIR__ . '/../../includes/bootstrap.php';

// SMS Gateway configuration
$smsConfig = require __DIR__ . '/../../config/sms.php';
$gateway_url = $smsConfig['gateway_url'] ?? 'http://192.168.1.100:8080';
$username = $smsConfig['gateway_username'] ?? 'sms';
$password = $smsConfig['gateway_password'] ?? '1234567890';

// Log file for SMS diagnostics
$logFile = __DIR__ . '/../../logs/sms_log.txt';
$timestamp = date('Y-m-d H:i:s');

// Quick connectivity test to gateway
$gatewayHost = parse_url($gateway_url, PHP_URL_HOST);
$gatewayPort = parse_url($gateway_url, PHP_URL_PORT) ?: 80;
$timeout = 5;
$fp = @fsockopen($gatewayHost, $gatewayPort, $errno, $errstr, $timeout);
if ($fp) {
    fclose($fp);
    file_put_contents($logFile, "[$timestamp] SMS_CONNECTIVITY: OK to $gatewayHost:$gatewayPort" . PHP_EOL, FILE_APPEND);
} else {
    file_put_contents($logFile, "[$timestamp] SMS_CONNECTIVITY: FAILED to $gatewayHost:$gatewayPort - $errstr ($errno)" . PHP_EOL, FILE_APPEND);
}

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

// If file_get_contents failed, try cURL as fallback
if ($response === false) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode("$username:$password")
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HEADER, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    $statusLine = "HTTP/1.1 $httpCode";
    if ($response === false) {
        file_put_contents($logFile, "[$timestamp] SMS_SEND: CURL_ERROR=$curlError" . PHP_EOL, FILE_APPEND);
    } else {
        file_put_contents($logFile, "[$timestamp] SMS_SEND: FALLBACK_CURL_STATUS=$httpCode" . PHP_EOL, FILE_APPEND);
    }
}

// Log diagnostic details
file_put_contents($logFile, "[$timestamp] SMS_SEND: URL=$url" . PHP_EOL, FILE_APPEND);
file_put_contents($logFile, "[$timestamp] SMS_SEND: PAYLOAD=" . json_encode($payload) . PHP_EOL, FILE_APPEND);
file_put_contents($logFile, "[$timestamp] SMS_SEND: STATUS=$statusLine" . PHP_EOL, FILE_APPEND);
file_put_contents($logFile, "[$timestamp] SMS_SEND: RESPONSE=" . ($response ?: '(null)') . PHP_EOL, FILE_APPEND);
if (!$response) {
    $error = error_get_last();
    file_put_contents($logFile, "[$timestamp] SMS_SEND: ERROR=" . ($error['message'] ?? 'Unknown') . PHP_EOL, FILE_APPEND);
}

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
