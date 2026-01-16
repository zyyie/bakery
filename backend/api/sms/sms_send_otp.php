<?php
session_start();
header('Content-Type: application/json');

// SMS Gateway configuration
$smsConfig = require __DIR__ . '/../../config/sms.php';
$gateway_url = $smsConfig['gateway_url'] ?? 'http://10.179.50.3:8080';
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

// Handle Philippine phone numbers (09xxxxxxxxx format)
// Convert 09xxxxxxxxx to 639xxxxxxxxx for international format
if (strlen($digits) === 11 && substr($digits, 0, 2) === '09') {
    $digits = '63' . substr($digits, 1); // Remove leading 0, add 63
}

if (strlen($digits) < 7 || strlen($digits) > 15) {
    echo json_encode(['ok' => false, 'error' => 'Invalid phone number format. Please use format: 09123456789 or +639123456789']);
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

// Try cURL first (more reliable), fallback to file_get_contents
$response = false;
$statusLine = 'HTTP/1.1 (no status)';
$httpCode = null;

if (function_exists('curl_init')) {
    // Use cURL for better error handling
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode("$username:$password")
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($response === false && $curlError) {
        $errorMessage = "cURL Error: $curlError";
    } else {
        $statusLine = "HTTP/1.1 $httpCode " . ($httpCode >= 200 && $httpCode < 300 ? 'OK' : 'Error');
    }
} else {
    // Fallback to file_get_contents
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
}

// Check if request was successful
$success = false;
$errorMessage = null;

// Extract HTTP status code if not already set (from cURL)
if ($httpCode === null && preg_match('/HTTP\/\d\.\d\s+(\d+)/', $statusLine, $matches)) {
    $httpCode = (int)$matches[1];
}

// Determine success based on HTTP code
if ($httpCode !== null) {
    $success = ($httpCode >= 200 && $httpCode < 300);
} else {
    // No HTTP code means connection failed
    $success = false;
}

// If request failed, check for errors
if ($response === false) {
    if (empty($errorMessage)) {
        $error = error_get_last();
        $errorMessage = $error['message'] ?? 'Failed to connect to SMS gateway. Please check if SMS Gateway is running.';
    }
    $success = false;
} elseif (!$success && $httpCode !== null) {
    // Parse error from response
    $responseData = json_decode($response, true);
    if (is_array($responseData)) {
        $errorMessage = $responseData['error'] ?? $responseData['message'] ?? "HTTP Error $httpCode";
    } else {
        $errorMessage = "HTTP Error $httpCode: Server returned an error";
    }
} elseif (!$success) {
    $errorMessage = "Connection failed. Check SMS Gateway configuration or network connectivity.";
}

// Log the attempt
$logFile = __DIR__ . '/../../logs/sms_log.txt';
$timestamp = date("Y-m-d H:i:s");
$logEntry = "[$timestamp] OTP SEND: Phone=$gatewayPhone, Status=" . ($success ? 'SUCCESS' : 'FAILED');
if ($errorMessage) {
    $logEntry .= ", Error=$errorMessage";
}
$logEntry .= ", HTTP=$httpCode, URL=$url" . PHP_EOL;
if ($response) {
    $logEntry .= "[$timestamp] Response: " . substr($response, 0, 500) . PHP_EOL;
}
file_put_contents($logFile, $logEntry, FILE_APPEND);

// Only store OTP in session if SMS was sent successfully
if ($success) {
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
        'message' => 'OTP sent successfully',
        'statusLine' => $statusLine,
        'httpCode' => $httpCode
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => $errorMessage ?? 'Failed to send OTP. Please check SMS gateway configuration.',
        'phone' => $gatewayPhone,
        'statusLine' => $statusLine,
        'httpCode' => $httpCode,
        'debug' => [
            'gateway_url' => $gateway_url,
            'url' => $url,
            'response' => substr($response ?: 'null', 0, 500)
        ]
    ]);
}
?>
