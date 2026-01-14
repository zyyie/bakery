<?php
require_once __DIR__ . '/../../config/connect.php';

header('Content-Type: application/json');

$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);
$timestamp = date("Y-m-d H:i:s");
$logFile = __DIR__ . "/../../logs/sms_log.txt";

// Log the raw data
file_put_contents($logFile, "[$timestamp] INCOMING: $rawData" . PHP_EOL, FILE_APPEND);

// Process incoming SMS
// Expected format from gateway may vary, adjust based on your gateway's webhook format
$phoneNumber = null;
$message = null;
$messageId = null;

// Try different possible formats from SMS gateway
if (isset($data['from']) || isset($data['phoneNumber']) || isset($data['sender'])) {
    $phoneNumber = $data['from'] ?? $data['phoneNumber'] ?? $data['sender'] ?? null;
}

if (isset($data['message']) || isset($data['text']) || isset($data['textMessage'])) {
    $message = $data['message'] ?? $data['text'] ?? $data['textMessage'] ?? null;
    // Handle nested textMessage structure
    if (is_array($message) && isset($message['text'])) {
        $message = $message['text'];
    }
}

if (isset($data['messageId']) || isset($data['id']) || isset($data['messageID'])) {
    $messageId = $data['messageId'] ?? $data['id'] ?? $data['messageID'] ?? null;
}

// If we have phone and message, store in database
if ($phoneNumber && $message) {
    // Clean phone number format
    $phoneNumber = trim($phoneNumber);
    if (!str_starts_with($phoneNumber, '+')) {
        $phoneNumber = '+' . $phoneNumber;
    }
    
    // Store in database
    $query = "INSERT INTO sms_messages (phoneNumber, message, direction, status, messageID) VALUES (?, ?, 'inbound', 'received', ?)";
    $result = executePreparedUpdate($query, "sss", [$phoneNumber, $message, $messageId ?? '']);
    
    if ($result !== false) {
        file_put_contents($logFile, "[$timestamp] STORED: From $phoneNumber: $message" . PHP_EOL, FILE_APPEND);
    } else {
        file_put_contents($logFile, "[$timestamp] ERROR: Failed to store message" . PHP_EOL, FILE_APPEND);
    }
} else {
    file_put_contents($logFile, "[$timestamp] WARNING: Missing phone or message in webhook data" . PHP_EOL, FILE_APPEND);
}

http_response_code(200);
echo json_encode([
    "status" => "ok", 
    "received" => true,
    "processed" => ($phoneNumber && $message) ? true : false
]);
?>
