<?php
require_once __DIR__ . '/../../includes/bootstrap.php';

header('Content-Type: application/json');

// SMS Gateway configuration
$smsConfig = require __DIR__ . '/../../config/sms.php';
$gateway_url = $smsConfig['gateway_url'] ?? 'http://192.168.1.100';
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
    file_put_contents($logFile, "[$timestamp] SMS_TEST_CONNECTIVITY: OK to $gatewayHost:$gatewayPort" . PHP_EOL, FILE_APPEND);
} else {
    file_put_contents($logFile, "[$timestamp] SMS_TEST_CONNECTIVITY: FAILED to $gatewayHost:$gatewayPort - $errstr ($errno)" . PHP_EOL, FILE_APPEND);
    echo json_encode(['ok' => false, 'error' => "Gateway unreachable: $errstr ($errno)"]);
    exit;
}

// Get input numbers (comma-separated or array)
$input = [];
if (($_SERVER['CONTENT_TYPE'] ?? '') === 'application/json') {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true) ?: [];
} else {
    $input = $_POST;
}

$phoneNumbers = [];
if (!empty($input['phoneNumbers'])) {
    if (is_array($input['phoneNumbers'])) {
        $phoneNumbers = $input['phoneNumbers'];
    } else {
        $phoneNumbers = array_map('trim', explode(',', $input['phoneNumbers']));
    }
} else {
    // Default test numbers if none provided
    $phoneNumbers = ['+639162606403', '+639493380766', '+639930152544'];
}

// Normalize phone numbers
$normalizedNumbers = [];
foreach ($phoneNumbers as $num) {
    $digits = preg_replace('/\D+/', '', $num);
    if (strlen($digits) >= 7 && strlen($digits) <= 15) {
        $normalizedNumbers[] = '+' . $digits;
    }
}

if (empty($normalizedNumbers)) {
    echo json_encode(['ok' => false, 'error' => 'No valid phone numbers provided']);
    exit;
}

$message = $input['message'] ?? 'Test message from SMS gateway';

$url = rtrim($gateway_url, '/') . '/messages';

$payload = [
    "phoneNumbers" => $normalizedNumbers,
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
        file_put_contents($logFile, "[$timestamp] SMS_TEST: CURL_ERROR=$curlError" . PHP_EOL, FILE_APPEND);
    } else {
        file_put_contents($logFile, "[$timestamp] SMS_TEST: FALLBACK_CURL_STATUS=$httpCode" . PHP_EOL, FILE_APPEND);
    }
}

// Log diagnostic details
file_put_contents($logFile, "[$timestamp] SMS_TEST: NUMBERS=" . json_encode($normalizedNumbers) . PHP_EOL, FILE_APPEND);
file_put_contents($logFile, "[$timestamp] SMS_TEST: URL=$url" . PHP_EOL, FILE_APPEND);
file_put_contents($logFile, "[$timestamp] SMS_TEST: PAYLOAD=" . json_encode($payload) . PHP_EOL, FILE_APPEND);
file_put_contents($logFile, "[$timestamp] SMS_TEST: STATUS=$statusLine" . PHP_EOL, FILE_APPEND);
file_put_contents($logFile, "[$timestamp] SMS_TEST: RESPONSE=" . ($response ?: '(null)') . PHP_EOL, FILE_APPEND);
if (!$response) {
    $error = error_get_last();
    file_put_contents($logFile, "[$timestamp] SMS_TEST: ERROR=" . ($error['message'] ?? 'Unknown') . PHP_EOL, FILE_APPEND);
}

echo json_encode([
    'ok' => !empty($response),
    'phoneNumbers' => $normalizedNumbers,
    'message' => $message,
    'statusLine' => $statusLine,
    'response' => json_decode($response, true) ?: $response
]);
?>
