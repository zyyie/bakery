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

$phoneNumber = isset($input['phone']) ? trim($input['phone']) : null;
$messageId = isset($input['messageId']) ? intval($input['messageId']) : null;

if (!$phoneNumber && !$messageId) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Missing phone number or message ID']);
    exit;
}

// Clean phone number format if provided
if ($phoneNumber) {
    if (!str_starts_with($phoneNumber, '+')) {
        $phoneNumber = '+' . $phoneNumber;
    }
}

// Mark messages as read
if ($messageId) {
    // Mark specific message
    $query = "UPDATE sms_messages SET read_at = NOW() WHERE smsID = ? AND direction = 'inbound' AND read_at IS NULL";
    $result = executePreparedUpdate($query, "i", [$messageId]);
} else {
    // Mark all messages from this phone number
    $query = "UPDATE sms_messages SET read_at = NOW() WHERE phoneNumber = ? AND direction = 'inbound' AND read_at IS NULL";
    $result = executePreparedUpdate($query, "s", [$phoneNumber]);
}

echo json_encode([
    'ok' => $result !== false,
    'updated' => $result !== false ? $result : 0
]);
?>
