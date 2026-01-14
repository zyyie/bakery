<?php
require_once __DIR__ . '/../../config/connect.php';

header('Content-Type: application/json');

// Get query parameters
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

// Get unique phone numbers with latest message info
$query = "SELECT 
            phoneNumber,
            MAX(created_at) as lastMessageAt,
            COUNT(*) as messageCount,
            SUM(CASE WHEN direction = 'inbound' AND read_at IS NULL THEN 1 ELSE 0 END) as unreadCount
          FROM sms_messages
          GROUP BY phoneNumber
          ORDER BY lastMessageAt DESC
          LIMIT ? OFFSET ?";

$result = executePreparedQuery($query, "ii", [$limit, $offset]);

$conversations = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Get the latest message for preview
        $latestQuery = "SELECT message, direction, created_at 
                        FROM sms_messages 
                        WHERE phoneNumber = ? 
                        ORDER BY created_at DESC 
                        LIMIT 1";
        $latestResult = executePreparedQuery($latestQuery, "s", [$row['phoneNumber']]);
        $latestMessage = null;
        if ($latestResult && $latest = mysqli_fetch_assoc($latestResult)) {
            $latestMessage = [
                'message' => $latest['message'],
                'direction' => $latest['direction'],
                'createdAt' => $latest['created_at']
            ];
        }
        
        $conversations[] = [
            'phoneNumber' => $row['phoneNumber'],
            'lastMessageAt' => $row['lastMessageAt'],
            'messageCount' => (int)$row['messageCount'],
            'unreadCount' => (int)$row['unreadCount'],
            'latestMessage' => $latestMessage
        ];
    }
}

// Get total count
$countQuery = "SELECT COUNT(DISTINCT phoneNumber) as total FROM sms_messages";
$countResult = executePreparedQuery($countQuery, "", []);
$total = 0;
if ($countResult && $row = mysqli_fetch_assoc($countResult)) {
    $total = (int)$row['total'];
}

echo json_encode([
    'ok' => true,
    'conversations' => $conversations,
    'total' => $total,
    'limit' => $limit,
    'offset' => $offset
]);
?>
