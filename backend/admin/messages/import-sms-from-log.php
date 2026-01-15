<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
requireAdminLogin();
adminRegenerateSession();

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

$logFile = __DIR__ . '/../../logs/sms_log.txt';
$smsConfig = require __DIR__ . '/../../config/sms.php';
$ourReceiveNumber = $smsConfig['receive_number'] ?? '+639493380766';

$imported = 0;
$skipped = 0;
$errors = 0;

if (file_exists($logFile)) {
    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Look for INCOMING messages
        if (strpos($line, 'INCOMING:') !== false) {
            // Extract timestamp
            preg_match('/\[([^\]]+)\]/', $line, $timestampMatch);
            $logTimestamp = $timestampMatch[1] ?? null;
            
            // Extract JSON data
            $jsonStart = strpos($line, '{');
            if ($jsonStart !== false) {
                $jsonData = substr($line, $jsonStart);
                $data = json_decode($jsonData, true);
                
                if ($data && isset($data['from']) && isset($data['text'])) {
                    $phoneNumber = $data['from'];
                    $message = $data['text'];
                    $messageId = $data['messageId'] ?? $data['id'] ?? null;
                    
                    // Normalize phone number
                    $phoneNumber = trim($phoneNumber);
                    if (preg_match('/^09\d{9}$/', $phoneNumber)) {
                        $phoneNumber = '+63' . substr($phoneNumber, 1);
                    } elseif (preg_match('/^639\d{9}$/', $phoneNumber)) {
                        $phoneNumber = '+' . $phoneNumber;
                    } elseif (!str_starts_with($phoneNumber, '+')) {
                        $phoneNumber = '+' . $phoneNumber;
                    }
                    
                    // Skip SMART messages and other system messages
                    if (strtoupper($phoneNumber) === 'SMART' || strtoupper($phoneNumber) === '+SMART') {
                        continue;
                    }
                    
                    // Check if message already exists (by phone, message, and approximate time)
                    $checkQuery = "SELECT smsID FROM sms_messages WHERE phoneNumber = ? AND message = ? AND direction = 'inbound' LIMIT 1";
                    $existing = executePreparedQuery($checkQuery, "ss", [$phoneNumber, $message]);
                    
                    if ($existing && mysqli_num_rows($existing) > 0) {
                        $skipped++;
                        continue;
                    }
                    
                    // Parse timestamp from log
                    $createdAt = date('Y-m-d H:i:s');
                    if ($logTimestamp) {
                        try {
                            $parsedTime = DateTime::createFromFormat('Y-m-d H:i:s', $logTimestamp);
                            if ($parsedTime) {
                                $createdAt = $parsedTime->format('Y-m-d H:i:s');
                            }
                        } catch (Exception $e) {
                            // Use current time if parsing fails
                        }
                    }
                    
                    // Insert message
                    $insertQuery = "INSERT INTO sms_messages (phoneNumber, message, direction, status, messageID, created_at) VALUES (?, ?, 'inbound', 'received', ?, ?)";
                    $result = executePreparedUpdate($insertQuery, "ssss", [$phoneNumber, $message, $messageId ?? '', $createdAt]);
                    
                    if ($result !== false && $result > 0) {
                        $imported++;
                    } else {
                        $errors++;
                    }
                }
            }
        }
    }
}

// Redirect back with success message
header('Location: sms-messages.php?imported=' . $imported . '&skipped=' . $skipped . '&errors=' . $errors);
exit();
?>
