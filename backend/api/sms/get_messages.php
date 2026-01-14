<?php
require_once __DIR__ . '/../../config/connect.php';

header('Content-Type: application/json');

// Get query parameters
$phoneNumber = isset($_GET['phone']) ? trim($_GET['phone']) : null;
$direction = isset($_GET['direction']) ? trim($_GET['direction']) : null; // 'inbound' or 'outbound'
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
$unreadOnly = isset($_GET['unread']) && $_GET['unread'] === 'true';

// Build query
$whereConditions = [];
$params = [];
$paramTypes = "";

if ($phoneNumber) {
    $phoneNumber = trim($phoneNumber);
    if (!str_starts_with($phoneNumber, '+')) {
        $phoneNumber = '+' . $phoneNumber;
    }
    $whereConditions[] = "phoneNumber = ?";
    $params[] = $phoneNumber;
    $paramTypes .= "s";
}

if ($direction && in_array($direction, ['inbound', 'outbound'])) {
    $whereConditions[] = "direction = ?";
    $params[] = $direction;
    $paramTypes .= "s";
}

if ($unreadOnly) {
    $whereConditions[] = "read_at IS NULL AND direction = 'inbound'";
}

$whereClause = !empty($whereConditions) ? "WHERE " . implode(' AND ', $whereConditions) : "";

$query = "SELECT smsID, phoneNumber, message, direction, status, messageID, error, created_at, read_at 
          FROM sms_messages 
          $whereClause
          ORDER BY created_at DESC 
          LIMIT ? OFFSET ?";

$params[] = $limit;
$params[] = $offset;
$paramTypes .= "ii";

$result = executePreparedQuery($query, $paramTypes, $params);

$messages = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $messages[] = [
            'id' => (int)$row['smsID'],
            'phoneNumber' => $row['phoneNumber'],
            'message' => $row['message'],
            'direction' => $row['direction'],
            'status' => $row['status'],
            'messageId' => $row['messageID'],
            'error' => $row['error'],
            'createdAt' => $row['created_at'],
            'readAt' => $row['read_at'],
            'isRead' => !empty($row['read_at'])
        ];
    }
}

// Get total count
$countQuery = "SELECT COUNT(*) as total FROM sms_messages $whereClause";
$countParams = array_slice($params, 0, -2); // Remove limit and offset
$countTypes = substr($paramTypes, 0, -2);
$countResult = executePreparedQuery($countQuery, $countTypes, $countParams);
$total = 0;
if ($countResult && $row = mysqli_fetch_assoc($countResult)) {
    $total = (int)$row['total'];
}

echo json_encode([
    'ok' => true,
    'messages' => $messages,
    'total' => $total,
    'limit' => $limit,
    'offset' => $offset
]);
?>
