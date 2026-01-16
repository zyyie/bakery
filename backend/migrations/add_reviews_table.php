<?php
/**
 * Migration: Add reviews table
 * This table stores product reviews that customers can only leave after receiving the product
 */

require_once __DIR__ . '/../config/connect.php';

// Create reviews table
$query = "CREATE TABLE IF NOT EXISTS `reviews` (
  `reviewID` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL,
  `itemID` int(11) NOT NULL,
  `orderID` int(11) DEFAULT NULL COMMENT 'The order this review is for',
  `rating` tinyint(1) NOT NULL CHECK (`rating` >= 1 AND `rating` <= 5),
  `reviewText` text DEFAULT NULL,
  `reviewDate` datetime DEFAULT current_timestamp(),
  `isVisible` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`reviewID`),
  KEY `userID` (`userID`),
  KEY `itemID` (`itemID`),
  KEY `orderID` (`orderID`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`itemID`) REFERENCES `items` (`itemID`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`orderID`) REFERENCES `orders` (`orderID`) ON DELETE SET NULL,
  UNIQUE KEY `unique_user_item_order` (`userID`, `itemID`, `orderID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

$result = mysqli_query($conn, $query);

if($result){
    echo "Reviews table created successfully!\n";
} else {
    echo "Error creating reviews table: " . mysqli_error($conn) . "\n";
}
