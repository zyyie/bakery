<?php
session_start();
header('Content-Type: application/json');

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

// Get recipient from POST data (form or JSON)
$input = [];
if (($_SERVER['CONTENT_TYPE'] ?? '') === 'application/json') {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true) ?: [];
} else {
    $input = $_POST;
}

// Log raw input for debugging
file_put_contents($logFile, "[$timestamp] SMS_SEND_OTP: RAW_INPUT=" . json_encode($input) . PHP_EOL, FILE_APPEND);

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
        file_put_contents($logFile, "[$timestamp] SMS_SEND_OTP: CURL_ERROR=$curlError" . PHP_EOL, FILE_APPEND);
    } else {
        file_put_contents($logFile, "[$timestamp] SMS_SEND_OTP: FALLBACK_CURL_STATUS=$httpCode" . PHP_EOL, FILE_APPEND);
    }
}

// Log diagnostic details
file_put_contents($logFile, "[$timestamp] SMS_SEND_OTP: RECIPIENT_RAW=$recipient" . PHP_EOL, FILE_APPEND);
file_put_contents($logFile, "[$timestamp] SMS_SEND_OTP: RECIPIENT_NORMALIZED=$gatewayPhone" . PHP_EOL, FILE_APPEND);
file_put_contents($logFile, "[$timestamp] SMS_SEND_OTP: URL=$url" . PHP_EOL, FILE_APPEND);
file_put_contents($logFile, "[$timestamp] SMS_SEND_OTP: PAYLOAD=" . json_encode($payload) . PHP_EOL, FILE_APPEND);
file_put_contents($logFile, "[$timestamp] SMS_SEND_OTP: STATUS=$statusLine" . PHP_EOL, FILE_APPEND);
file_put_contents($logFile, "[$timestamp] SMS_SEND_OTP: RESPONSE=" . ($response ?: '(null)') . PHP_EOL, FILE_APPEND);
if (!$response) {
    $error = error_get_last();
    file_put_contents($logFile, "[$timestamp] SMS_SEND_OTP: ERROR=" . ($error['message'] ?? 'Unknown') . PHP_EOL, FILE_APPEND);
}

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
