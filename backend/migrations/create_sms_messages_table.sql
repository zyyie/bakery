-- Create SMS messages table for storing incoming and outgoing SMS
CREATE TABLE IF NOT EXISTS `sms_messages` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
