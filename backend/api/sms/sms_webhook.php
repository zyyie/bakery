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
$phoneNumber = null; // Sender's phone number
$receiveNumber = null; // Receiver's phone number (should be our receive_number)
$message = null;
$messageId = null;

// Get SMS config to check receiving number
$smsConfig = require __DIR__ . '/../../config/sms.php';
$ourReceiveNumber = $smsConfig['receive_number'] ?? '+639493380766';

// Try different possible formats from SMS gateway
// Get sender's phone number
if (isset($data['from'])) {
    $phoneNumber = $data['from'];
} elseif (isset($data['phoneNumber']) || isset($data['sender'])) {
    $phoneNumber = $data['phoneNumber'] ?? $data['sender'] ?? null;
}

// Get receiver's phone number (the number that received the SMS)
if (isset($data['to']) || isset($data['recipient']) || isset($data['receiveNumber']) || isset($data['destination'])) {
    $receiveNumber = $data['to'] ?? $data['recipient'] ?? $data['receiveNumber'] ?? $data['destination'] ?? null;
}

// Get message text
if (isset($data['text'])) {
    $message = $data['text'];
} elseif (isset($data['message']) || isset($data['textMessage'])) {
    $message = $data['message'] ?? $data['textMessage'] ?? null;
    // Handle nested textMessage structure
    if (is_array($message) && isset($message['text'])) {
        $message = $message['text'];
    }
}

// Get message ID (SMS Forwarder doesn't provide one in this template)
$messageId = $data['messageId'] ?? $data['id'] ?? $data['messageID'] ?? null;

// Normalize receiver number for comparison
$isForOurNumber = false;
if ($receiveNumber) {
    $receiveNumberNormalized = trim($receiveNumber);
    // Handle Philippine phone numbers
    if (preg_match('/^09\d{9}$/', $receiveNumberNormalized)) {
        $receiveNumberNormalized = '+63' . substr($receiveNumberNormalized, 1);
    } elseif (preg_match('/^639\d{9}$/', $receiveNumberNormalized)) {
        $receiveNumberNormalized = '+' . $receiveNumberNormalized;
    } elseif (!str_starts_with($receiveNumberNormalized, '+')) {
        $receiveNumberNormalized = '+' . $receiveNumberNormalized;
    }
    
    // Check if message is for our receiving number
    if ($receiveNumberNormalized === $ourReceiveNumber) {
        $isForOurNumber = true;
        file_put_contents($logFile, "[$timestamp] MESSAGE FOR OUR NUMBER ($ourReceiveNumber): From $phoneNumber" . PHP_EOL, FILE_APPEND);
    } else {
        file_put_contents($logFile, "[$timestamp] MESSAGE NOT FOR OUR NUMBER (Received: $receiveNumberNormalized, Expected: $ourReceiveNumber)" . PHP_EOL, FILE_APPEND);
    }
} else {
    // If receiver number is not provided, assume it's for us (some gateways don't send 'to' field)
    $isForOurNumber = true;
    file_put_contents($logFile, "[$timestamp] WARNING: Receiver number not provided, assuming message is for us" . PHP_EOL, FILE_APPEND);
}

// Only store messages that are sent TO our receiving number
if ($phoneNumber && $message && $isForOurNumber) {
    // Clean sender's phone number format
    $phoneNumber = trim($phoneNumber);
    
    // Handle Philippine phone numbers (09xxxxxxxxx or +639xxxxxxxxx)
    // Convert 09xxxxxxxxx to +639xxxxxxxxx
    if (preg_match('/^09\d{9}$/', $phoneNumber)) {
        $phoneNumber = '+63' . substr($phoneNumber, 1);
    } elseif (preg_match('/^639\d{9}$/', $phoneNumber)) {
        $phoneNumber = '+' . $phoneNumber;
    } elseif (!str_starts_with($phoneNumber, '+')) {
        $phoneNumber = '+' . $phoneNumber;
    }
    
    // Store in database (phoneNumber is the sender, not the receiver)
    $query = "INSERT INTO sms_messages (phoneNumber, message, direction, status, messageID) VALUES (?, ?, 'inbound', 'received', ?)";
    $result = executePreparedUpdate($query, "sss", [$phoneNumber, $message, $messageId ?? '']);
    
    if ($result !== false && $result > 0) {
        file_put_contents($logFile, "[$timestamp] STORED: From $phoneNumber TO $ourReceiveNumber: $message" . PHP_EOL, FILE_APPEND);
    } else {
        $dbError = $GLOBALS['db_last_error'] ?? 'Unknown database error';
        file_put_contents($logFile, "[$timestamp] ERROR: Failed to store message - $dbError" . PHP_EOL, FILE_APPEND);
        file_put_contents($logFile, "[$timestamp] DEBUG: Phone=$phoneNumber, Message=" . substr($message, 0, 50) . ", MessageID=" . ($messageId ?? 'NULL') . ", Result=" . var_export($result, true) . PHP_EOL, FILE_APPEND);
    }
} else {
    if (!$isForOurNumber) {
        file_put_contents($logFile, "[$timestamp] IGNORED: Message not for our receiving number ($ourReceiveNumber)" . PHP_EOL, FILE_APPEND);
    } else {
        file_put_contents($logFile, "[$timestamp] WARNING: Missing phone or message in webhook data" . PHP_EOL, FILE_APPEND);
    }
}

http_response_code(200);
echo json_encode([
    "status" => "ok", 
    "received" => true,
    "processed" => ($phoneNumber && $message) ? true : false
]);
?>
