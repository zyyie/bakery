-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 04, 2026 at 02:20 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_bakery`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `adminID` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`adminID`, `username`, `password`) VALUES
(1, 'admin', '$2y$10$u5p6HwGzqlZ6zGXquMiEl.nJD9kPiQxOXrUd/UH5.KJtE.GX3lWxu');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `categoryID` int(11) NOT NULL,
  `categoryName` varchar(255) NOT NULL,
  `creationDate` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`categoryID`, `categoryName`, `creationDate`) VALUES
(1, 'Classic & Basic Bread', '2025-12-28 16:26:35'),
(2, 'Sweet Bread', '2025-12-28 16:26:35'),
(3, 'Filled / Stuffed Bread', '2025-12-28 16:26:35'),
(4, 'Buns & Rolls', '2025-12-28 16:26:35'),
(5, 'Bread–Cake Combo', '2025-12-28 16:26:35'),
(6, 'Special (Budget-Friendly)', '2025-12-28 16:26:35');

-- --------------------------------------------------------

--
-- Table structure for table `enquiries`
--

CREATE TABLE `enquiries` (
  `enquiryID` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mobileNumber` varchar(20) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `enquiryDate` datetime DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'Unread',
  `userID` int(11) DEFAULT NULL,
  `replyMessage` text DEFAULT NULL,
  `replyDate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enquiries`
--

INSERT INTO `enquiries` (`enquiryID`, `name`, `email`, `mobileNumber`, `message`, `enquiryDate`, `status`, `userID`, `replyMessage`, `replyDate`) VALUES
(1, 'Eliaza Mae Malibiran', 'malibiraneliazamae@gmail.com', '', 'how can i order 100 boxes of pandesal?', '2026-01-03 22:24:58', 'Replied', 2, 'by truck', '2026-01-03 22:33:19');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `itemID` int(11) NOT NULL,
  `stock_qty` int(11) NOT NULL DEFAULT 0,
  `reorder_point` int(11) NOT NULL DEFAULT 0,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`itemID`, `stock_qty`, `reorder_point`, `updated_at`) VALUES
(1, 97, 0, '2026-01-02 22:05:22'),
(2, 98, 0, '2026-01-02 22:05:22'),
(3, 100, 0, '2025-12-31 15:34:15'),
(4, 100, 0, '2025-12-31 15:34:24'),
(5, 100, 0, '2025-12-31 15:34:30'),
(6, 99, 0, '2026-01-04 16:48:23'),
(7, 100, 0, '2025-12-31 15:34:39'),
(8, 100, 0, '2025-12-31 15:34:45'),
(9, 100, 0, '2025-12-31 15:34:50'),
(10, 100, 0, '2025-12-31 15:34:55'),
(11, 100, 0, '2025-12-31 15:35:00'),
(12, 100, 0, '2025-12-31 15:36:28'),
(13, 100, 0, '2025-12-31 15:36:32'),
(14, 100, 0, '2025-12-31 15:36:37'),
(15, 100, 0, '2025-12-31 15:36:23'),
(16, 100, 0, '2025-12-31 15:36:18'),
(17, 100, 0, '2025-12-31 15:36:13'),
(18, 100, 0, '2025-12-31 15:36:09'),
(19, 100, 0, '2025-12-31 15:36:03'),
(20, 100, 0, '2025-12-31 15:35:59'),
(21, 100, 0, '2025-12-31 15:35:55'),
(22, 100, 0, '2025-12-31 15:35:51'),
(23, 100, 0, '2025-12-31 15:35:47'),
(24, 100, 0, '2025-12-31 15:35:43'),
(25, 100, 0, '2025-12-31 15:35:39'),
(26, 100, 0, '2025-12-31 15:35:34'),
(27, 100, 0, '2025-12-31 15:35:29'),
(28, 100, 0, '2025-12-31 15:35:24'),
(29, 100, 0, '2025-12-31 15:35:20'),
(30, 100, 0, '2025-12-31 15:35:13'),
(31, 100, 0, '2025-12-31 15:35:09');

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `itemID` int(11) NOT NULL,
  `packageName` varchar(255) NOT NULL,
  `foodDescription` text DEFAULT NULL,
  `itemContains` text DEFAULT NULL,
  `categoryID` int(11) DEFAULT NULL,
  `itemImage` varchar(255) DEFAULT NULL,
  `size` varchar(50) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Active',
  `suitableFor` int(11) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `creationDate` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`itemID`, `packageName`, `foodDescription`, `itemContains`, `categoryID`, `itemImage`, `size`, `status`, `suitableFor`, `price`, `creationDate`) VALUES
(1, 'Pandesal (plain)', 'Classic Filipino bread roll, 3-5 pcs per pack', 'Flour, Yeast, Sugar, Salt', 1, NULL, NULL, 'Active', NULL, 50.00, '2025-12-28 16:26:35'),
(2, 'Buttered pandesal', 'Soft pandesal with butter spread', 'Flour, Yeast, Sugar, Salt, Butter', 1, NULL, NULL, 'Active', NULL, 60.00, '2025-12-28 16:26:35'),
(3, 'Malunggay pandesal', 'Nutritious pandesal with malunggay leaves', 'Flour, Yeast, Sugar, Salt, Malunggay', 1, NULL, NULL, 'Active', NULL, 55.00, '2025-12-28 16:26:35'),
(4, 'Wheat pandesal', 'Healthy whole wheat pandesal', 'Whole Wheat Flour, Yeast, Sugar, Salt', 1, NULL, NULL, 'Active', NULL, 60.00, '2025-12-28 16:26:35'),
(5, 'Spanish bread', 'Sweet bread roll with butter and sugar filling', 'Flour, Yeast, Sugar, Butter', 1, NULL, NULL, 'Active', NULL, 45.00, '2025-12-28 16:26:35'),
(6, 'Cheese bread', 'Soft bread with cheese filling', 'Flour, Yeast, Sugar, Cheese', 1, NULL, NULL, 'Active', NULL, 55.00, '2025-12-28 16:26:35'),
(7, 'Ensaymada (mini)', 'Mini sweet bread topped with butter, sugar, and cheese', 'Flour, Yeast, Sugar, Butter, Cheese', 2, NULL, NULL, 'Active', NULL, 40.00, '2025-12-28 16:26:35'),
(8, 'Ube cheese bread', 'Purple yam bread with cheese topping', 'Flour, Yeast, Ube, Cheese', 2, NULL, NULL, 'Active', NULL, 50.00, '2025-12-28 16:26:35'),
(9, 'Chocolate bread / Choco roll', 'Sweet bread with chocolate filling', 'Flour, Yeast, Sugar, Chocolate', 2, NULL, NULL, 'Active', NULL, 45.00, '2025-12-28 16:26:35'),
(10, 'Cream bread', 'Soft bread with sweet cream filling', 'Flour, Yeast, Sugar, Cream', 2, NULL, NULL, 'Active', NULL, 50.00, '2025-12-28 16:26:35'),
(11, 'Custard bread', 'Bread filled with creamy custard', 'Flour, Yeast, Sugar, Custard', 2, NULL, NULL, 'Active', NULL, 50.00, '2025-12-28 16:26:35'),
(12, 'Monggo bread', 'Sweet bread with mung bean filling', 'Flour, Yeast, Sugar, Mung Beans', 2, NULL, NULL, 'Active', NULL, 45.00, '2025-12-28 16:26:35'),
(13, 'Strawberry bread', 'Bread with strawberry jam filling', 'Flour, Yeast, Sugar, Strawberry Jam', 2, NULL, NULL, 'Active', NULL, 50.00, '2025-12-28 16:26:35'),
(14, 'Pineapple bread', 'Bread with pineapple filling', 'Flour, Yeast, Sugar, Pineapple', 2, NULL, NULL, 'Active', NULL, 50.00, '2025-12-28 16:26:35'),
(15, 'Ham & cheese bread', 'Bread stuffed with ham and cheese', 'Flour, Yeast, Ham, Cheese', 3, NULL, NULL, 'Active', NULL, 65.00, '2025-12-28 16:26:35'),
(16, 'Hotdog roll', 'Soft roll with hotdog filling', 'Flour, Yeast, Hotdog', 3, NULL, NULL, 'Active', NULL, 45.00, '2025-12-28 16:26:35'),
(17, 'Sausage roll', 'Bread roll with sausage', 'Flour, Yeast, Sausage', 3, NULL, NULL, 'Active', NULL, 50.00, '2025-12-28 16:26:35'),
(18, 'Tuna bread', 'Bread filled with tuna spread', 'Flour, Yeast, Tuna, Mayonnaise', 3, NULL, NULL, 'Active', NULL, 55.00, '2025-12-28 16:26:35'),
(19, 'Chicken bread', 'Bread stuffed with chicken filling', 'Flour, Yeast, Chicken', 3, NULL, NULL, 'Active', NULL, 60.00, '2025-12-28 16:26:35'),
(20, 'Cheese stick bread', 'Bread with cheese stick filling', 'Flour, Yeast, Cheese', 3, NULL, NULL, 'Active', NULL, 50.00, '2025-12-28 16:26:35'),
(21, 'Mini burger bun (with filling)', 'Small burger bun with filling', 'Flour, Yeast, Sugar, Filling', 4, NULL, NULL, 'Active', NULL, 40.00, '2025-12-28 16:26:35'),
(22, 'Dinner rolls', 'Soft dinner rolls, 3-4 pcs per pack', 'Flour, Yeast, Sugar, Butter', 4, NULL, NULL, 'Active', NULL, 55.00, '2025-12-28 16:26:35'),
(23, 'Soft roll bread', 'Soft and fluffy bread rolls', 'Flour, Yeast, Sugar, Milk', 4, NULL, NULL, 'Active', NULL, 45.00, '2025-12-28 16:26:35'),
(24, 'Mini banana bread slice', 'Moist banana bread slice', 'Flour, Banana, Sugar, Eggs', 5, NULL, NULL, 'Active', NULL, 35.00, '2025-12-28 16:26:35'),
(25, 'Mini chiffon cake slice', 'Light and airy chiffon cake slice', 'Flour, Eggs, Sugar, Oil', 5, NULL, NULL, 'Active', NULL, 40.00, '2025-12-28 16:26:35'),
(26, 'Mini pound cake', 'Rich and buttery pound cake', 'Flour, Butter, Sugar, Eggs', 5, NULL, NULL, 'Active', NULL, 45.00, '2025-12-28 16:26:35'),
(27, 'Cupcake', 'Delicious cupcakes, 1-2 pcs per pack', 'Flour, Sugar, Eggs, Frosting', 5, NULL, NULL, 'Active', NULL, 50.00, '2025-12-28 16:26:35'),
(28, 'Garlic bread sticks', 'Crispy bread sticks with garlic butter', 'Flour, Yeast, Garlic, Butter', 6, NULL, NULL, 'Active', NULL, 35.00, '2025-12-28 16:26:35'),
(29, 'Cheese garlic roll', 'Soft roll with cheese and garlic', 'Flour, Yeast, Cheese, Garlic', 6, NULL, NULL, 'Active', NULL, 40.00, '2025-12-28 16:26:35'),
(30, 'Cinnamon roll (mini)', 'Sweet mini cinnamon rolls', 'Flour, Yeast, Cinnamon, Sugar', 6, NULL, NULL, 'Active', NULL, 35.00, '2025-12-28 16:26:35'),
(31, 'Pandesal bites (assorted flavors)', 'Small pandesal bites in assorted flavors', 'Flour, Yeast, Sugar, Various Flavors', 6, NULL, NULL, 'Active', NULL, 45.00, '2025-12-28 16:26:35');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `orderID` int(11) NOT NULL,
  `orderNumber` varchar(50) NOT NULL,
  `userID` int(11) DEFAULT NULL,
  `fullName` varchar(255) NOT NULL,
  `contactNumber` varchar(20) NOT NULL,
  `orderDate` datetime DEFAULT current_timestamp(),
  `deliveryDate` date DEFAULT NULL,
  `flatNumber` varchar(50) DEFAULT NULL,
  `streetName` varchar(255) DEFAULT NULL,
  `area` varchar(255) DEFAULT NULL,
  `landmark` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `zipcode` varchar(20) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `orderStatus` varchar(50) DEFAULT 'Still Pending',
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`orderID`, `orderNumber`, `userID`, `fullName`, `contactNumber`, `orderDate`, `deliveryDate`, `flatNumber`, `streetName`, `area`, `landmark`, `city`, `zipcode`, `state`, `orderStatus`, `remark`) VALUES
(1, '454419773', 1, 'Eliaza Mae Malibiran', '09471110102', '2025-12-28 17:31:15', '2025-12-30', '2', 'hjbsda', 'hdfbdfb', 'afwgsfgvsdvs', 'sgfscvd', '4232', 'sdfsdfs', 'Delivered', ''),
(2, '160022551', 1, 'Eliaza Mae Malibiran', '09471110102', '2025-12-31 15:15:40', '2025-03-12', '2', 'Pantay Matanda', 'hdfbdfb', 'afwgsfgvsdvs', 'Tanauan', '0000', 'Batangas', 'Still Pending', NULL),
(3, '429199344', 1, 'Eliaza Mae Malibiran', '09471110102', '2026-01-02 22:05:22', '2026-01-05', '2', 'Zone 2, Pantay Matanda, Tanauan City, Batangas', 'hdfbdfb', 'afwgsfgvsdvs', 'Batangas', '4232', 'Batangas', 'Still Pending', NULL),
(4, '716567391', 2, 'Eliaza Mae Malibiran', '09471110102', '2026-01-04 16:48:23', '2026-01-10', '2', 'Zone 2, Pantay Matanda, Tanauan City, Batangas', 'hdfbdfb', 'afwgsfgvsdvs', 'Batangas', '0000', 'Batangas', 'Still Pending', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `orderItemID` int(11) NOT NULL,
  `orderID` int(11) DEFAULT NULL,
  `itemID` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unitPrice` decimal(10,2) NOT NULL,
  `totalPrice` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`orderItemID`, `orderID`, `itemID`, `quantity`, `unitPrice`, `totalPrice`) VALUES
(1, 1, 1, 1, 50.00, 50.00),
(2, 1, 2, 1, 60.00, 60.00),
(3, 2, 3, 3, 55.00, 165.00),
(4, 2, 8, 1, 50.00, 50.00),
(5, 3, 2, 2, 60.00, 120.00),
(6, 3, 1, 3, 50.00, 150.00),
(7, 4, 6, 1, 55.00, 55.00);

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `pageID` int(11) NOT NULL,
  `pageTitle` varchar(255) NOT NULL,
  `pageDescription` text DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `mobileNumber` varchar(20) DEFAULT NULL,
  `pageType` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`pageID`, `pageTitle`, `pageDescription`, `email`, `mobileNumber`, `pageType`) VALUES
(1, 'About Us', 'We are known as the best catering company in Seattle for good reason. Our dedication and commitment to quality and sustainability has earned us a loyal following among our clientele, one that continues to grow based on enthusiastic referrals. For nearly two decades, we have bridged the gap between the land, the sea, and your table. We leverage the best ingredients Washington has to offer, preparing them mindfully and always from scratch.', '', '', 'aboutus'),
(2, 'Contact Us', 'Your Business Address Here', 'your-email@example.com', '+63 XXX XXX XXXX', 'contactus');

-- --------------------------------------------------------

--
-- Table structure for table `subscribers`
--

CREATE TABLE `subscribers` (
  `subscriberID` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subscribingDate` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscribers`
--

INSERT INTO `subscribers` (`subscriberID`, `email`, `subscribingDate`) VALUES
(1, 'malibiraneliazamae@gmail.com', '2026-01-03 21:47:39');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `userID` int(11) NOT NULL,
  `fullName` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mobileNumber` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `failed_login_attempts` int(11) NOT NULL DEFAULT 0,
  `last_failed_login` datetime DEFAULT NULL,
  `remember_token` varchar(64) DEFAULT NULL,
  `token_expires` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `verification_token` varchar(64) DEFAULT NULL,
  `account_status` enum('active','suspended','deleted') NOT NULL DEFAULT 'active',
  `regDate` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`userID`, `fullName`, `email`, `mobileNumber`, `password`, `failed_login_attempts`, `last_failed_login`, `remember_token`, `token_expires`, `last_login`, `reset_token`, `reset_token_expires`, `email_verified`, `verification_token`, `account_status`, `regDate`) VALUES
(1, 'Eliaza Mae Malibiran', 'eliazamaemalibiran@gmail.com', '09471110102', '$2y$10$LIBm63bqlL/LpFDK/17Im.UFS3s.BjHv/P4est2gWst7Hh3/ZJSKS', 1, '2026-01-02 23:49:23', NULL, NULL, '2025-12-28 17:30:42', NULL, NULL, 0, NULL, 'active', '2025-12-28 17:04:59'),
(2, 'Eliaza Mae Malibiran', 'malibiraneliazamae@gmail.com', '09471110102', '$2y$10$y2oqc2bnP2mZoIVvnuJ9QOcGkaDqleNBWCUQzSK8MArESRTfS1.NW', 0, '2026-01-03 21:45:26', NULL, NULL, '2026-01-04 16:39:31', 'df3df9008288996687fb07dc654896bb6274be3b0d65365a98085c1b9b289016', '2026-01-03 15:45:36', 0, NULL, 'active', '2026-01-02 23:50:14');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`adminID`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`categoryID`);

--
-- Indexes for table `enquiries`
--
ALTER TABLE `enquiries`
  ADD PRIMARY KEY (`enquiryID`),
  ADD KEY `userID` (`userID`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`itemID`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`itemID`),
  ADD KEY `categoryID` (`categoryID`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`orderID`),
  ADD UNIQUE KEY `orderNumber` (`orderNumber`),
  ADD KEY `userID` (`userID`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`orderItemID`),
  ADD KEY `orderID` (`orderID`),
  ADD KEY `itemID` (`itemID`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`pageID`),
  ADD UNIQUE KEY `pageType` (`pageType`);

--
-- Indexes for table `subscribers`
--
ALTER TABLE `subscribers`
  ADD PRIMARY KEY (`subscriberID`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`userID`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email_verification` (`email_verified`),
  ADD KEY `idx_account_status` (`account_status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `adminID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `categoryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `enquiries`
--
ALTER TABLE `enquiries`
  MODIFY `enquiryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `itemID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `orderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `orderItemID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `pageID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `subscribers`
--
ALTER TABLE `subscribers`
  MODIFY `subscriberID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `userID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `enquiries`
--
ALTER TABLE `enquiries`
  ADD CONSTRAINT `enquiries_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE SET NULL;

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`itemID`) REFERENCES `items` (`itemID`);

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_ibfk_1` FOREIGN KEY (`categoryID`) REFERENCES `categories` (`categoryID`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`orderID`) REFERENCES `orders` (`orderID`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`itemID`) REFERENCES `items` (`itemID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
