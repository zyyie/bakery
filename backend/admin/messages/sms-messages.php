<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
requireAdminLogin();
adminRegenerateSession();

// Define e() function if not available
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

include(dirname(__DIR__) . "/includes/header.php");

// Check if table exists, create if not
$tableCheck = executePreparedQuery("SHOW TABLES LIKE 'sms_messages'", "", []);
if (!$tableCheck || mysqli_num_rows($tableCheck) == 0) {
    // Table doesn't exist, create it
    $createTableSQL = "CREATE TABLE IF NOT EXISTS `sms_messages` (
        `smsID` int(11) NOT NULL AUTO_INCREMENT,
        `phoneNumber` varchar(20) NOT NULL,
        `message` text NOT NULL,
        `direction` enum('inbound','outbound') NOT NULL,
        `status` varchar(50) DEFAULT 'sent',
        `messageID` varchar(255) DEFAULT NULL,
        `error` text DEFAULT NULL,
        `created_at` datetime NOT NULL DEFAULT current_timestamp(),
        `read_at` datetime DEFAULT NULL,
        PRIMARY KEY (`smsID`),
        KEY `idx_phone` (`phoneNumber`),
        KEY `idx_direction` (`direction`),
        KEY `idx_created` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    executeQuery($createTableSQL);
}

// Handle import from log
if (isset($_GET['imported']) || isset($_GET['skipped']) || isset($_GET['errors'])) {
    $imported = (int)($_GET['imported'] ?? 0);
    $skipped = (int)($_GET['skipped'] ?? 0);
    $errors = (int)($_GET['errors'] ?? 0);
    if ($imported > 0) {
        $success = "Successfully imported $imported message(s) from log file.";
        if ($skipped > 0) {
            $success .= " Skipped $skipped duplicate(s).";
        }
        if ($errors > 0) {
            $error = "Failed to import $errors message(s).";
        }
    }
}

$success = $success ?? "";
$error = $error ?? "";

// Handle mark as read
if(isset($_POST['markRead'])){
    $smsID = intval($_POST['smsID']);
    
    if($smsID > 0){
        $query = "UPDATE sms_messages SET read_at = NOW() WHERE smsID = ?";
        $result = executePreparedUpdate($query, "i", [$smsID]);
        if($result !== false) {
            $success = "Message marked as read.";
        } else {
            $error = "Failed to mark message as read.";
        }
    }
}

// Handle reply
if(isset($_POST['reply'])){
    $smsID = intval($_POST['smsID']);
    $replyMessage = trim($_POST['replyMessage'] ?? '');
    $phoneNumber = trim($_POST['phoneNumber'] ?? '');
    
    if($smsID > 0 && !empty($replyMessage) && !empty($phoneNumber)){
        // Send SMS reply
        $smsConfig = require __DIR__ . '/../../config/sms.php';
        $gateway_url = $smsConfig['gateway_url'] ?? 'http://192.168.18.112:8080';
        $username = $smsConfig['gateway_username'] ?? 'sms';
        $password = $smsConfig['gateway_password'] ?? '1234567890';
        
        // Clean phone number
        $recipient = $phoneNumber;
        if (!str_starts_with($recipient, '+')) {
            $recipient = '+' . $recipient;
        }
        
        $url = rtrim($gateway_url, '/') . '/messages';
        $payload = [
            "phoneNumbers" => [$recipient],
            "textMessage" => ["text" => $replyMessage],
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
        $responseData = json_decode($response, true);
        $messageId = $responseData['messageId'] ?? $responseData['id'] ?? null;
        $success = strpos($statusLine, '200') !== false || strpos($statusLine, '201') !== false;
        
        // Store outbound message
        $status = $success ? 'sent' : 'failed';
        $errorMsg = $success ? null : ($responseData['error'] ?? $response ?? 'Unknown error');
        $query = "INSERT INTO sms_messages (phoneNumber, message, direction, status, messageID, error) VALUES (?, ?, 'outbound', ?, ?, ?)";
        executePreparedUpdate($query, "sssss", [$recipient, $replyMessage, $status, $messageId ?? '', $errorMsg ?? '']);
        
        // Mark original message as read
        $markReadQuery = "UPDATE sms_messages SET read_at = NOW() WHERE smsID = ?";
        executePreparedUpdate($markReadQuery, "i", [$smsID]);
        
        if($success) {
            $success = "Reply sent successfully!";
        } else {
            $error = "Failed to send reply: " . ($errorMsg ?? 'Unknown error');
        }
    } else {
        $error = "Please provide a reply message.";
    }
}

// Get filter parameters
$filterPhone = isset($_GET['phone']) ? trim($_GET['phone']) : '';
$filterDirection = isset($_GET['direction']) ? trim($_GET['direction']) : '';
$unreadOnly = isset($_GET['unread']) && $_GET['unread'] === '1';

// Build query
$whereConditions = [];
$params = [];
$paramTypes = "";

if ($filterPhone) {
    $phone = $filterPhone;
    if (!str_starts_with($phone, '+')) {
        $phone = '+' . $phone;
    }
    $whereConditions[] = "phoneNumber = ?";
    $params[] = $phone;
    $paramTypes .= "s";
}

if ($filterDirection && in_array($filterDirection, ['inbound', 'outbound'])) {
    $whereConditions[] = "direction = ?";
    $params[] = $filterDirection;
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
          LIMIT 100";
$result = executePreparedQuery($query, $paramTypes, $params);

// Get unread count
$unreadQuery = "SELECT COUNT(*) as count FROM sms_messages WHERE read_at IS NULL AND direction = 'inbound'";
$unreadResult = executePreparedQuery($unreadQuery, "", []);
$unreadCount = 0;
if ($unreadResult && $row = mysqli_fetch_assoc($unreadResult)) {
    $unreadCount = (int)$row['count'];
}

// Get SMS config to show receiving number
$smsConfig = require __DIR__ . '/../../config/sms.php';
$receiveNumber = $smsConfig['receive_number'] ?? '+639493380766';
$displayReceiveNumber = str_replace('+63', '0', $receiveNumber); // Show as 09493380766
?>

<h2 class="mb-4" style="color: #8B4513; font-weight: 600;">
    <i class="fas fa-sms"></i> SMS Messages
    <?php if($unreadCount > 0): ?>
        <span class="badge bg-danger ms-2"><?php echo $unreadCount; ?> Unread</span>
    <?php endif; ?>
</h2>

<div class="alert alert-primary mb-3">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <i class="fas fa-info-circle"></i> 
            <strong>Receiving Number:</strong> <?php echo e($displayReceiveNumber); ?> 
            <small class="text-muted">(Messages sent to this number will appear here)</small>
        </div>
        <div class="d-flex gap-2">
            <a href="import-sms-from-log.php" class="btn btn-sm btn-success" onclick="return confirm('Import messages from log file?')">
                <i class="fas fa-file-import"></i> Import from Log
            </a>
            <a href="sms-messages.php" class="btn btn-sm btn-primary">
                <i class="fas fa-sync-alt"></i> Refresh
            </a>
        </div>
    </div>
</div>

<?php if($success): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?php echo e($success); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if($error): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?php echo e($error); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Filters -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Phone Number</label>
                <input type="text" name="phone" class="form-control form-control-sm" placeholder="09930152544 or +639930152544" value="<?php echo e($filterPhone); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Direction</label>
                <select name="direction" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="inbound" <?php echo $filterDirection === 'inbound' ? 'selected' : ''; ?>>Inbound</option>
                    <option value="outbound" <?php echo $filterDirection === 'outbound' ? 'selected' : ''; ?>>Outbound</option>
                </select>
            </div>
            <div class="col-md-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="unread" value="1" id="unreadOnly" <?php echo $unreadOnly ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="unreadOnly">
                        Unread Only
                    </label>
                </div>
            </div>
            <div class="col-md-5">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <a href="sms-messages.php" class="btn btn-sm btn-secondary">
                    <i class="fas fa-times"></i> Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Messages Table -->
<div class="card">
    <div class="card-body">
        <table class="table table-striped">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th style="width: 150px;">Phone Number</th>
                        <th>Message</th>
                        <th style="width: 100px;">Direction</th>
                        <th style="width: 100px;">Status</th>
                        <th style="width: 150px;">Date/Time</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $count = 1;
                    if($result && mysqli_num_rows($result) > 0):
                        while($row = mysqli_fetch_assoc($result)):
                            $isRead = !empty($row['read_at']);
                            $isInbound = $row['direction'] === 'inbound';
                            // Format phone number for display
                            $displayPhone = $row['phoneNumber'];
                            if (str_starts_with($displayPhone, '+63')) {
                                $displayPhone = '0' . substr($displayPhone, 3);
                            }
                    ?>
                    <tr class="<?php echo !$isRead && $isInbound ? 'table-warning' : ''; ?>">
                        <td><?php echo $count++; ?></td>
                        <td>
                            <strong><?php echo e($displayPhone); ?></strong>
                            <?php if(!$isRead && $isInbound): ?>
                                <span class="badge bg-danger ms-2">New</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="message-preview" style="max-width: 300px; cursor: pointer;" onclick="toggleMessage(this)">
                                <?php echo e(substr($row['message'], 0, 50)); ?>
                                <?php echo strlen($row['message']) > 50 ? '...' : ''; ?>
                                <div class="full-message" style="display: none; margin-top: 5px; padding: 10px; background: #f8f9fa; border-radius: 4px;">
                                    <?php echo e($row['message']); ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-<?php echo $isInbound ? 'primary' : 'success'; ?>" style="background-color: <?php echo $isInbound ? '#8B4513' : '#28a745'; ?> !important;">
                                <?php echo ucfirst($row['direction']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-<?php 
                                echo $row['status'] === 'sent' || $row['status'] === 'received' ? 'success' : 
                                    ($row['status'] === 'failed' ? 'danger' : 'warning'); 
                            ?>" style="background-color: <?php 
                                echo $row['status'] === 'sent' || $row['status'] === 'received' ? '#28a745' : 
                                    ($row['status'] === 'failed' ? '#dc3545' : '#D4A574'); 
                            ?> !important;">
                                <?php echo ucfirst($row['status']); ?>
                            </span>
                        </td>
                        <td>
                            <small><?php echo date('M d, Y h:i A', strtotime($row['created_at'])); ?></small>
                        </td>
                        <td>
                            <?php if(!$isRead && $isInbound): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="smsID" value="<?php echo $row['smsID']; ?>">
                                <button type="submit" name="markRead" class="btn btn-sm btn-primary me-2">
                                    <i class="fas fa-envelope-open"></i> Mark as Read
                                </button>
                            </form>
                            <?php endif; ?>
                            <?php if($isInbound): ?>
                            <button type="button" class="btn btn-sm btn-info" 
                                    onclick="showReplyModal('<?php echo e($row['phoneNumber']); ?>', <?php echo $row['smsID']; ?>)">
                                <i class="fas fa-reply"></i> Reply
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            No SMS messages found
                            <?php if($filterPhone || $filterDirection || $unreadOnly): ?>
                                <br><small>Try adjusting your filters or <a href="sms-messages.php">clear all filters</a>.</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
    </div>
</div>

<!-- Reply Modal -->
<div class="modal fade" id="replyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reply to SMS</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="smsID" id="replySmsID">
                    <input type="hidden" name="phoneNumber" id="replyPhoneNumber">
                    <div class="mb-3">
                        <label class="form-label">To:</label>
                        <input type="text" class="form-control" id="replyPhoneDisplay" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message:</label>
                        <textarea name="replyMessage" class="form-control" rows="5" required placeholder="Type your reply message here..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="reply" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Send Reply
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include(dirname(__DIR__) . "/includes/footer.php"); ?>

<style>
.message-preview:hover {
    opacity: 0.8;
}
.table tbody tr.table-warning {
    background-color: #fff3cd !important;
}
/* Ensure table headers use brown theme */
.table thead th {
    background-color: #8B4513 !important;
    color: #fff !important;
}
/* Ensure buttons match brown theme */
.btn-primary {
    background: linear-gradient(135deg, #8B4513, #654321) !important;
    border: none !important;
}
.btn-primary:hover {
    background: linear-gradient(135deg, #654321, #8B4513) !important;
}
.btn-info {
    background: linear-gradient(135deg, #8B4513, #654321) !important;
    border: none !important;
}
.btn-info:hover {
    background: linear-gradient(135deg, #654321, #8B4513) !important;
}
</style>

<script>
function toggleMessage(element) {
    const fullMessage = element.querySelector('.full-message');
    if (fullMessage.style.display === 'none') {
        fullMessage.style.display = 'block';
    } else {
        fullMessage.style.display = 'none';
    }
}

function showReplyModal(phoneNumber, smsID) {
    document.getElementById('replyPhoneNumber').value = phoneNumber;
    document.getElementById('replyPhoneDisplay').value = phoneNumber;
    document.getElementById('replySmsID').value = smsID;
    const modal = new bootstrap.Modal(document.getElementById('replyModal'));
    modal.show();
}
</script>
