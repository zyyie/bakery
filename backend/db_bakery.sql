-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 12, 2026 at 10:29 AM
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
CREATE DATABASE db_bakery;
USE db_bakery;
-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `addressID` int(11) NOT NULL,
  `userID` int(11) DEFAULT NULL,
  `label` varchar(50) DEFAULT NULL,
  `flatNumber` varchar(50) DEFAULT NULL,
  `streetName` varchar(255) DEFAULT NULL,
  `area` varchar(255) DEFAULT NULL,
  `landmark` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `zipcode` varchar(20) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(2) NOT NULL DEFAULT 'PH',
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`addressID`, `userID`, `label`, `flatNumber`, `streetName`, `area`, `landmark`, `city`, `zipcode`, `state`, `country`, `is_default`, `created_at`) VALUES
(1, 1, 'Default', '2', 'Zone 2, Pantay Matanda, Tanauan City, Batangas', 'hdfbdfb', 'afwgsfgvsdvs', 'Batangas', '4232', 'Batangas', 'PH', 1, '2026-01-11 14:27:29'),
(2, 2, 'Default', '2', 'Zone 2, Pantay Matanda, Tanauan City, Batangas', 'hdfbdfb', 'afwgsfgvsdvs', 'Batangas', '0000', 'Batangas', 'PH', 1, '2026-01-11 14:27:29'),
(4, 1, 'Order #454419773', '2', 'hjbsda', 'hdfbdfb', 'afwgsfgvsdvs', 'sgfscvd', '4232', 'sdfsdfs', 'PH', 0, '2026-01-11 14:27:29'),
(5, 1, 'Order #160022551', '2', 'Pantay Matanda', 'hdfbdfb', 'afwgsfgvsdvs', 'Tanauan', '0000', 'Batangas', 'PH', 0, '2026-01-11 14:27:29');

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
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `cartID` int(11) NOT NULL,
  `userID` int(11) DEFAULT NULL,
  `sessionKey` varchar(64) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `cartItemID` int(11) NOT NULL,
  `cartID` int(11) NOT NULL,
  `itemID` int(11) NOT NULL,
  `productOptionID` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unitPrice` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(6, 'Special (Budget-Friendly)', '2025-12-28 16:26:35'),
(7, 'Crinkles', '2026-01-07 16:44:39'),
(8, 'Cookies', '2026-01-07 16:44:39'),
(9, 'Brownies', '2026-01-07 16:44:39');

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
(1, 'Eliaza Mae Malibiran', 'malibiraneliazamae@gmail.com', '', 'how can i order 100 boxes of pandesal?', '2026-01-03 22:24:58', 'Replied', 2, 'by truck', '2026-01-03 22:33:19'),
(2, 'Eliaza Mae Malibiran', 'malibiraneliazamae@gmail.com', '09930152544', 'hello', '2026-01-07 18:23:31', 'Unread', 2, NULL, NULL),
(3, 'Eliaza Mae Malibiran', 'malibiraneliazamae@gmail.com', '09930152544', 'hello', '2026-01-07 18:27:52', 'Unread', 2, NULL, NULL),
(4, 'Eliaza Mae Malibiran', 'malibiraneliazamae@gmail.com', '09930152544', 'hello', '2026-01-07 18:28:06', 'Unread', 2, NULL, NULL),
(5, 'Eliaza Mae Malibiran', 'malibiraneliazamae@gmail.com', '09930152544', 'hi', '2026-01-07 18:28:40', 'Unread', 2, NULL, NULL),
(6, 'Eliaza Mae Malibiran', 'malibiraneliazamae@gmail.com', '09930152544', 'hi', '2026-01-07 18:33:05', 'Unread', 2, NULL, NULL),
(7, 'Eliaza', 'malibiraneliazamae@gmail.com', '09930152544', 'hi po', '2026-01-07 18:33:37', 'Unread', 2, NULL, NULL),
(8, 'Eliaza Mae Malibiran', 'malibiraneliazamae@gmail.com', '09493380766', 'hello', '2026-01-07 20:24:55', 'Unread', 2, NULL, NULL),
(9, 'Eliaza Mae Malibiran', 'malibiraneliazamae@gmail.com', '09493380766', 'hello', '2026-01-07 20:28:58', 'Unread', 2, NULL, NULL),
(10, 'Eliaza Mae Malibiran', 'malibiraneliazamae@gmail.com', '09493380766', 'hello', '2026-01-07 20:29:05', 'Unread', 2, NULL, NULL),
(11, 'Eliaza Mae Malibiran', 'malibiraneliazamae@gmail.com', '09493380766', 'hello', '2026-01-07 20:33:28', 'Unread', 2, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `userID` int(11) NOT NULL,
  `itemID` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `itemID` int(11) NOT NULL,
  `stock_qty` int(11) NOT NULL DEFAULT 0,
  `reorder_point` int(11) NOT NULL DEFAULT 0,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `min_stock_level` int(11) NOT NULL DEFAULT 10,
  `last_updated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`itemID`, `stock_qty`, `reorder_point`, `updated_at`, `min_stock_level`, `last_updated`) VALUES
(1, 500, 5, '2026-01-12 00:54:08', 10, '2026-01-12 00:54:08'),
(2, 500, 5, '2026-01-12 00:54:08', 10, '2026-01-12 00:54:08'),
(3, 500, 5, '2026-01-12 00:54:08', 10, '2026-01-12 00:54:08'),
(4, 500, 5, '2026-01-12 00:54:08', 10, '2026-01-12 00:54:08'),
(5, 500, 5, '2026-01-12 00:54:08', 10, '2026-01-12 00:54:08'),
(6, 500, 5, '2026-01-12 00:54:08', 10, '2026-01-12 00:54:08'),
(7, 500, 5, '2026-01-12 00:54:08', 10, '2026-01-12 00:54:08'),
(8, 500, 5, '2026-01-12 00:54:08', 10, '2026-01-12 00:54:08'),
(9, 500, 5, '2026-01-12 00:54:08', 10, '2026-01-12 00:54:08'),
(10, 500, 5, '2026-01-12 00:54:08', 10, '2026-01-12 00:54:08'),
(11, 500, 5, '2026-01-12 00:54:08', 10, '2026-01-12 00:54:08'),
(12, 500, 5, '2026-01-12 00:54:08', 10, '2026-01-12 00:54:08'),
(13, 500, 5, '2026-01-12 00:54:08', 10, '2026-01-12 00:54:08'),
(14, 500, 5, '2026-01-12 00:54:08', 10, '2026-01-12 00:54:08'),
(15, 500, 5, '2026-01-12 00:54:08', 10, '2026-01-12 00:54:08'),
(16, 500, 5, '2026-01-12 00:54:08', 10, '2026-01-12 00:54:08'),
(17, 494, 5, '2026-01-12 01:36:10', 10, '2026-01-12 01:36:10'),
(18, 500, 5, '2026-01-12 00:54:08', 10, '2026-01-12 00:54:08'),
(19, 500, 5, '2026-01-12 00:54:08', 10, '2026-01-12 00:54:08'),
(20, 500, 5, '2026-01-12 00:54:08', 10, '2026-01-12 00:54:08'),
(21, 500, 5, '2026-01-12 00:54:08', 10, '2026-01-12 00:54:08'),
(22, 500, 5, '2026-01-12 00:54:08', 10, '2026-01-12 00:54:08'),
(23, 500, 5, '2026-01-12 00:54:08', 10, '2026-01-12 00:54:08'),
(24, 500, 5, '2026-01-12 00:54:08', 10, '2026-01-12 00:54:08'),
(25, 500, 5, '2026-01-12 00:54:08', 10, '2026-01-12 00:54:08'),
(26, 500, 5, '2026-01-12 00:54:08', 10, '2026-01-12 00:54:08'),
(27, 500, 5, '2026-01-12 00:54:08', 10, '2026-01-12 00:54:08'),
(28, 500, 5, '2026-01-12 00:54:08', 10, '2026-01-12 00:54:08'),
(29, 500, 5, '2026-01-12 00:54:08', 10, '2026-01-12 00:54:08'),
(30, 500, 5, '2026-01-12 00:54:08', 10, '2026-01-12 00:54:08'),
(31, 500, 5, '2026-01-12 00:54:08', 10, '2026-01-12 00:54:08'),
(32, 500, 5, '2026-01-12 00:53:56', 10, '2026-01-12 00:53:56'),
(33, 500, 5, '2026-01-12 00:53:56', 10, '2026-01-12 00:53:56'),
(34, 500, 5, '2026-01-12 00:53:56', 10, '2026-01-12 00:53:56'),
(35, 500, 5, '2026-01-12 00:53:56', 10, '2026-01-12 00:53:56'),
(36, 500, 5, '2026-01-12 00:53:56', 10, '2026-01-12 00:53:56'),
(37, 500, 5, '2026-01-12 00:53:56', 10, '2026-01-12 00:53:56'),
(38, 500, 5, '2026-01-12 00:53:56', 10, '2026-01-12 00:53:56'),
(39, 500, 5, '2026-01-12 00:53:56', 10, '2026-01-12 00:53:56'),
(40, 500, 5, '2026-01-12 00:53:56', 10, '2026-01-12 00:53:56'),
(41, 500, 5, '2026-01-12 00:53:56', 10, '2026-01-12 00:53:56'),
(42, 500, 5, '2026-01-12 00:53:56', 10, '2026-01-12 00:53:56'),
(43, 500, 5, '2026-01-12 00:53:56', 10, '2026-01-12 00:53:56'),
(44, 500, 5, '2026-01-12 00:53:56', 10, '2026-01-12 00:53:56'),
(45, 500, 5, '2026-01-12 00:53:56', 10, '2026-01-12 00:53:56'),
(46, 500, 5, '2026-01-12 00:53:56', 10, '2026-01-12 00:53:56'),
(47, 500, 5, '2026-01-12 00:53:56', 10, '2026-01-12 00:53:56');

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
(1, 'Pandesal (plain)', 'Classic Filipino bread roll, 3-5 pcs per pack', 'Flour, Yeast, Sugar, Salt', 1, NULL, NULL, 'Active', NULL, 10.00, '2025-12-28 16:26:35'),
(2, 'Buttered pandesal', 'Soft pandesal with butter spread', 'Flour, Yeast, Sugar, Salt, Butter', 1, NULL, NULL, 'Active', NULL, 11.00, '2025-12-28 16:26:35'),
(3, 'Malunggay pandesal', 'Nutritious pandesal with malunggay leaves', 'Flour, Yeast, Sugar, Salt, Malunggay', 1, NULL, NULL, 'Active', NULL, 14.00, '2025-12-28 16:26:35'),
(4, 'Wheat pandesal', 'Healthy whole wheat pandesal', 'Whole Wheat Flour, Yeast, Sugar, Salt', 1, NULL, NULL, 'Active', NULL, 12.00, '2025-12-28 16:26:35'),
(5, 'Spanish bread', 'Sweet bread roll with butter and sugar filling', 'Flour, Yeast, Sugar, Butter', 1, NULL, NULL, 'Active', NULL, 10.00, '2025-12-28 16:26:35'),
(6, 'Cheese bread', 'Soft bread with cheese filling', 'Flour, Yeast, Sugar, Cheese', 1, NULL, NULL, 'Active', NULL, 19.00, '2025-12-28 16:26:35'),
(7, 'Ensaymada (mini)', 'Mini sweet bread topped with butter, sugar, and cheese', 'Flour, Yeast, Sugar, Butter, Cheese', 2, NULL, NULL, 'Active', NULL, 22.00, '2025-12-28 16:26:35'),
(8, 'Ube cheese bread', 'Purple yam bread with cheese topping', 'Flour, Yeast, Ube, Cheese', 2, NULL, NULL, 'Active', NULL, 13.00, '2025-12-28 16:26:35'),
(9, 'Chocolate bread / Choco roll', 'Sweet bread with chocolate filling', 'Flour, Yeast, Sugar, Chocolate', 2, NULL, NULL, 'Active', NULL, 23.00, '2025-12-28 16:26:35'),
(10, 'Cream bread', 'Soft bread with sweet cream filling', 'Flour, Yeast, Sugar, Cream', 2, NULL, NULL, 'Active', NULL, 15.00, '2025-12-28 16:26:35'),
(11, 'Custard bread', 'Bread filled with creamy custard', 'Flour, Yeast, Sugar, Custard', 2, NULL, NULL, 'Active', NULL, 16.00, '2025-12-28 16:26:35'),
(12, 'Monggo bread', 'Sweet bread with mung bean filling', 'Flour, Yeast, Sugar, Mung Beans', 2, NULL, NULL, 'Active', NULL, 24.00, '2025-12-28 16:26:35'),
(13, 'Strawberry bread', 'Bread with strawberry jam filling', 'Flour, Yeast, Sugar, Strawberry Jam', 2, NULL, NULL, 'Active', NULL, 14.00, '2025-12-28 16:26:35'),
(14, 'Pineapple bread', 'Bread with pineapple filling', 'Flour, Yeast, Sugar, Pineapple', 2, NULL, NULL, 'Active', NULL, 22.00, '2025-12-28 16:26:35'),
(15, 'Ham & cheese bread', 'Bread stuffed with ham and cheese', 'Flour, Yeast, Ham, Cheese', 3, NULL, NULL, 'Active', NULL, 25.00, '2025-12-28 16:26:35'),
(16, 'Hotdog roll', 'Soft roll with hotdog filling', 'Flour, Yeast, Hotdog', 3, NULL, NULL, 'Active', NULL, 20.00, '2025-12-28 16:26:35'),
(17, 'Sausage roll', 'Bread roll with sausage', 'Flour, Yeast, Sausage', 3, NULL, NULL, 'Active', NULL, 14.00, '2025-12-28 16:26:35'),
(18, 'Tuna bread', 'Bread filled with tuna spread', 'Flour, Yeast, Tuna, Mayonnaise', 3, NULL, NULL, 'Active', NULL, 15.00, '2025-12-28 16:26:35'),
(19, 'Chicken bread', 'Bread stuffed with chicken filling', 'Flour, Yeast, Chicken', 3, NULL, NULL, 'Active', NULL, 23.00, '2025-12-28 16:26:35'),
(20, 'Cheese stick bread', 'Bread with cheese stick filling', 'Flour, Yeast, Cheese', 3, NULL, NULL, 'Active', NULL, 12.00, '2025-12-28 16:26:35'),
(21, 'Mini burger bun (with filling)', 'Small burger bun with filling', 'Flour, Yeast, Sugar, Filling', 4, NULL, NULL, 'Active', NULL, 17.00, '2025-12-28 16:26:35'),
(22, 'Dinner rolls', 'Soft dinner rolls, 3-4 pcs per pack', 'Flour, Yeast, Sugar, Butter', 4, NULL, NULL, 'Active', NULL, 21.00, '2025-12-28 16:26:35'),
(23, 'Soft roll bread', 'Soft and fluffy bread rolls', 'Flour, Yeast, Sugar, Milk', 4, NULL, NULL, 'Active', NULL, 11.00, '2025-12-28 16:26:35'),
(24, 'Mini banana bread slice', 'Moist banana bread slice', 'Flour, Banana, Sugar, Eggs', 5, NULL, NULL, 'Active', NULL, 17.00, '2025-12-28 16:26:35'),
(25, 'Mini chiffon cake slice', 'Light and airy chiffon cake slice', 'Flour, Eggs, Sugar, Oil', 5, NULL, NULL, 'Active', NULL, 12.00, '2025-12-28 16:26:35'),
(26, 'Mini pound cake', 'Rich and buttery pound cake', 'Flour, Butter, Sugar, Eggs', 5, NULL, NULL, 'Active', NULL, 12.00, '2025-12-28 16:26:35'),
(27, 'Cupcake', 'Delicious cupcakes, 1-2 pcs per pack', 'Flour, Sugar, Eggs, Frosting', 5, NULL, NULL, 'Active', NULL, 17.00, '2025-12-28 16:26:35'),
(28, 'Garlic bread sticks', 'Crispy bread sticks with garlic butter', 'Flour, Yeast, Garlic, Butter', 6, NULL, NULL, 'Active', NULL, 23.00, '2025-12-28 16:26:35'),
(29, 'Cheese garlic roll', 'Soft roll with cheese and garlic', 'Flour, Yeast, Cheese, Garlic', 6, NULL, NULL, 'Active', NULL, 23.00, '2025-12-28 16:26:35'),
(30, 'Cinnamon roll (mini)', 'Sweet mini cinnamon rolls', 'Flour, Yeast, Cinnamon, Sugar', 6, NULL, NULL, 'Active', NULL, 18.00, '2025-12-28 16:26:35'),
(31, 'Pandesal bites (assorted flavors)', 'Small pandesal bites in assorted flavors', 'Flour, Yeast, Sugar, Various Flavors', 6, NULL, NULL, 'Active', NULL, 12.00, '2025-12-28 16:26:35'),
(32, 'Assorted Crinkles', 'Mix of different crinkle flavors: Chocolate, Matcha, Red Velvet, Ube, and Vanilla', 'Flour, Cocoa Powder, Matcha Powder, Red Velvet, Ube, Vanilla, Sugar, Eggs, Butter', 7, NULL, NULL, 'Active', NULL, 15.00, '2026-01-07 16:46:49'),
(33, 'Chocolate Crinkles', 'Rich chocolate cookies with powdered sugar coating', 'Flour, Cocoa Powder, Sugar, Eggs, Butter, Powdered Sugar', 7, NULL, NULL, 'Active', NULL, 12.00, '2026-01-07 16:46:49'),
(34, 'Matcha Crinkles', 'Japanese green tea flavored cookies with powdered sugar', 'Flour, Matcha Powder, Sugar, Eggs, Butter, Powdered Sugar', 7, NULL, NULL, 'Active', NULL, 24.00, '2026-01-07 16:46:49'),
(35, 'Red Velvet Crinkles', 'Red velvet cookies with classic crinkle appearance', 'Flour, Cocoa Powder, Red Food Color, Sugar, Eggs, Butter, Powdered Sugar', 7, NULL, NULL, 'Active', NULL, 23.00, '2026-01-07 16:46:49'),
(36, 'Ube Crinkles', 'Purple yam flavored cookies with powdered sugar coating', 'Flour, Ube Powder, Sugar, Eggs, Butter, Powdered Sugar', 7, NULL, NULL, 'Active', NULL, 20.00, '2026-01-07 16:46:49'),
(37, 'Vanilla Crinkles', 'Classic vanilla cookies with powdered sugar coating', 'Flour, Vanilla Extract, Sugar, Eggs, Butter, Powdered Sugar', 7, NULL, NULL, 'Active', NULL, 21.00, '2026-01-07 16:46:49'),
(38, 'Assorted Brownies', 'Mix of different brownie flavors: Fudge, Oreo Fudge, and Walnut Fudge', 'Flour, Cocoa Powder, Chocolate, Oreo Cookies, Walnuts, Sugar, Eggs, Butter', 8, NULL, NULL, 'Active', NULL, 19.00, '2026-01-07 16:46:49'),
(39, 'Fudge Brownie', 'Rich and fudgy chocolate brownies', 'Flour, Cocoa Powder, Chocolate, Sugar, Eggs, Butter, Vanilla', 8, NULL, NULL, 'Active', NULL, 24.00, '2026-01-07 16:46:49'),
(40, 'Oreo Fudge Brownie', 'Fudgy brownies with crushed Oreo cookies', 'Flour, Cocoa Powder, Chocolate, Oreo Cookies, Sugar, Eggs, Butter', 8, NULL, NULL, 'Active', NULL, 20.00, '2026-01-07 16:46:49'),
(41, 'Walnut Fudge Brownie', 'Fudgy brownies with crunchy walnuts', 'Flour, Cocoa Powder, Chocolate, Walnuts, Sugar, Eggs, Butter, Vanilla', 8, NULL, NULL, 'Active', NULL, 20.00, '2026-01-07 16:46:49'),
(42, 'Assorted Cookies', 'Mix of different cookie flavors: Black Cocoa White Chocolate, Black Velvet Chunky, Chocolate Chip, Dark Chocolate Mint, and Double Chocolate', 'Flour, Cocoa Powder, Chocolate Chips, White Chocolate, Mint, Sugar, Eggs, Butter, Vanilla', 9, NULL, NULL, 'Active', NULL, 14.00, '2026-01-07 16:46:49'),
(43, 'Black Cocoa and White Chocolate Chips', 'Dark black cocoa cookies with white chocolate chips', 'Flour, Black Cocoa Powder, White Chocolate Chips, Sugar, Eggs, Butter', 9, NULL, NULL, 'Active', NULL, 16.00, '2026-01-07 16:46:49'),
(44, 'Black Velvet Chunky Cookie', 'Dark and rich black velvet cookies with chunks', 'Flour, Black Cocoa Powder, Chocolate Chunks, Sugar, Eggs, Butter', 9, NULL, NULL, 'Active', NULL, 12.00, '2026-01-07 16:46:49'),
(45, 'Chocolate Chip Cookie', 'Classic cookies with chocolate chips', 'Flour, Chocolate Chips, Sugar, Eggs, Butter, Vanilla, Baking Soda', 9, NULL, NULL, 'Active', NULL, 17.00, '2026-01-07 16:46:49'),
(46, 'Dark Chocolate Mint Cookies', 'Rich dark chocolate cookies with refreshing mint flavor', 'Flour, Dark Cocoa Powder, Chocolate Chips, Mint Extract, Sugar, Eggs, Butter', 9, NULL, NULL, 'Active', NULL, 24.00, '2026-01-07 16:46:49'),
(47, 'Double Chocolate White Chunk Cookie', 'Double chocolate cookies with white chocolate chunks', 'Flour, Cocoa Powder, Chocolate Chips, White Chocolate Chunks, Sugar, Eggs, Butter', 9, NULL, NULL, 'Active', NULL, 14.00, '2026-01-07 16:46:49');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `orderID` int(11) NOT NULL,
  `orderNumber` varchar(50) NOT NULL,
  `userID` int(11) DEFAULT NULL,
  `billingAddressID` int(11) DEFAULT NULL,
  `shippingAddressID` int(11) DEFAULT NULL,
  `fullName` varchar(255) NOT NULL,
  `contactNumber` varchar(20) NOT NULL,
  `orderDate` datetime DEFAULT current_timestamp(),
  `deliveryDate` date DEFAULT NULL,
  `orderStatus` varchar(50) DEFAULT 'Still Pending',
  `remark` text DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `shipping_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `grand_total` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`orderID`, `orderNumber`, `userID`, `billingAddressID`, `shippingAddressID`, `fullName`, `contactNumber`, `orderDate`, `deliveryDate`, `orderStatus`, `remark`, `subtotal`, `shipping_fee`, `discount_total`, `grand_total`) VALUES
(1, '454419773', 1, 1, 4, 'Eliaza Mae Malibiran', '09471110102', '2025-12-28 17:31:15', '2025-12-30', 'Delivered', '', 110.00, 0.00, 0.00, 110.00),
(2, '160022551', 1, 1, 5, 'Eliaza Mae Malibiran', '09471110102', '2025-12-31 15:15:40', '2025-03-12', 'Still Pending', NULL, 215.00, 0.00, 0.00, 215.00),
(3, '429199344', 1, 1, 1, 'Eliaza Mae Malibiran', '09471110102', '2026-01-02 22:05:22', '2026-01-05', 'Still Pending', NULL, 270.00, 0.00, 0.00, 270.00),
(4, '716567391', 2, 2, 2, 'Eliaza Mae Malibiran', '09471110102', '2026-01-04 16:48:23', '2026-01-10', 'Confirmed', '', 55.00, 0.00, 0.00, 55.00),
(5, '492896706', 2, 2, 2, 'Eliaza Mae Malibiran', '09471110102', '2026-01-04 21:55:18', '2026-01-24', 'Still Pending', NULL, 45.00, 0.00, 0.00, 45.00),
(6, '349701152', 2, NULL, NULL, 'Eliaza Mae Malibiran', '09471110102', '2026-01-11 22:34:20', '2026-01-24', 'Still Pending', NULL, 0.00, 0.00, 0.00, 0.00),
(7, '687087433', 2, NULL, NULL, 'Eliaza Mae Malibiran', '09471110102', '2026-01-12 01:36:10', NULL, 'Still Pending', NULL, 0.00, 0.00, 0.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `orderItemID` int(11) NOT NULL,
  `orderID` int(11) DEFAULT NULL,
  `itemID` int(11) DEFAULT NULL,
  `productNameSnapshot` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unitPrice` decimal(10,2) NOT NULL,
  `totalPrice` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`orderItemID`, `orderID`, `itemID`, `productNameSnapshot`, `quantity`, `unitPrice`, `totalPrice`) VALUES
(1, 1, 1, 'Pandesal (plain)', 1, 50.00, 50.00),
(2, 1, 2, 'Buttered pandesal', 1, 60.00, 60.00),
(3, 2, 3, 'Malunggay pandesal', 3, 55.00, 165.00),
(4, 2, 8, 'Ube cheese bread', 1, 50.00, 50.00),
(5, 3, 2, 'Buttered pandesal', 2, 60.00, 120.00),
(6, 3, 1, 'Pandesal (plain)', 3, 50.00, 150.00),
(7, 4, 6, 'Cheese bread', 1, 55.00, 55.00),
(8, 5, 5, 'Spanish bread', 1, 45.00, 45.00),
(9, 6, 11, '', 1, 50.00, 50.00),
(10, 7, 17, '', 6, 14.00, 84.00);

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
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `paymentID` int(11) NOT NULL,
  `orderID` int(11) NOT NULL,
  `provider` varchar(50) NOT NULL,
  `providerRef` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('initiated','authorized','captured','failed','refunded') NOT NULL,
  `paid_at` datetime DEFAULT NULL,
  `raw_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`raw_payload`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_options`
--

CREATE TABLE `product_options` (
  `productOptionID` int(11) NOT NULL,
  `itemID` int(11) NOT NULL,
  `optionName` varchar(50) NOT NULL,
  `optionValue` varchar(50) NOT NULL,
  `priceDelta` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sku` varchar(64) DEFAULT NULL,
  `stock_qty` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `roleID` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`roleID`, `name`) VALUES
(2, 'admin'),
(1, 'customer');

-- --------------------------------------------------------

--
-- Table structure for table `subscribers`
--

CREATE TABLE `subscribers` (
  `subscriberID` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `userID` int(11) DEFAULT NULL,
  `subscribingDate` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscribers`
--

INSERT INTO `subscribers` (`subscriberID`, `email`, `userID`, `subscribingDate`) VALUES
(1, 'malibiraneliazamae@gmail.com', 2, '2026-01-03 21:47:39'),
(2, 'eliazamaemalibiran@gmail.com', NULL, '2026-01-12 08:47:55');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `userID` int(11) NOT NULL,
  `fullName` varchar(255) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mobileNumber` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `google_id` varchar(255) DEFAULT NULL,
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
  `regDate` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`userID`, `fullName`, `first_name`, `last_name`, `email`, `mobileNumber`, `password`, `google_id`, `failed_login_attempts`, `last_failed_login`, `remember_token`, `token_expires`, `last_login`, `reset_token`, `reset_token_expires`, `email_verified`, `verification_token`, `account_status`, `regDate`, `updated_at`) VALUES
(1, 'Eliaza Mae Malibiran', 'Eliaza', 'Malibiran', 'eliazamaemalibiran@gmail.com', '09471110102', '$2y$10$LIBm63bqlL/LpFDK/17Im.UFS3s.BjHv/P4est2gWst7Hh3/ZJSKS', NULL, 2, '2026-01-05 18:12:21', NULL, NULL, '2025-12-28 17:30:42', NULL, NULL, 0, NULL, 'active', '2025-12-28 17:04:59', NULL),
(2, 'Eliaza Mae Malibiran', 'Eliaza', 'Malibiran', 'malibiraneliazamae@gmail.com', '09471110102', '$2y$10$y2oqc2bnP2mZoIVvnuJ9QOcGkaDqleNBWCUQzSK8MArESRTfS1.NW', '101062961528374618649', 0, '2026-01-07 16:53:38', 'b08a8e2e8fc2e0cd5acacad1a185fc8d17d04ddc1886da5242082fb3bbdba664', '2026-02-11 01:06:51', '2026-01-12 08:04:46', 'df3df9008288996687fb07dc654896bb6274be3b0d65365a98085c1b9b289016', '2026-01-03 15:45:36', 0, NULL, 'active', '2026-01-02 23:50:14', NULL),
(3, 'Ericka Maynete', 'Ericka', 'Maynete', 'erickamaynete@gmail.com', '+639930152544', '$2y$10$78juTmxpxYYGElRPasRTaep/x/GtY8g0TAB5Olrz4Pl4eBhbJGdha', NULL, 0, NULL, NULL, NULL, '2026-01-08 15:04:25', NULL, NULL, 0, NULL, 'active', '2026-01-08 15:04:15', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `userID` int(11) NOT NULL,
  `roleID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`addressID`),
  ADD KEY `ix_addresses_user` (`userID`,`is_default`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`adminID`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`cartID`),
  ADD UNIQUE KEY `ux_carts_session` (`sessionKey`),
  ADD KEY `ix_carts_user` (`userID`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`cartItemID`),
  ADD UNIQUE KEY `ux_cart_item` (`cartID`,`itemID`,`productOptionID`),
  ADD KEY `ix_ci_cart` (`cartID`),
  ADD KEY `fk_ci_item` (`itemID`);

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
  ADD KEY `userID` (`userID`),
  ADD KEY `idx_enquiries_email` (`email`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`userID`,`itemID`),
  ADD KEY `fk_fav_item` (`itemID`);

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
  ADD KEY `idx_orders_user` (`userID`),
  ADD KEY `idx_orders_shipping` (`shippingAddressID`),
  ADD KEY `idx_orders_billing` (`billingAddressID`);

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
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`paymentID`),
  ADD KEY `ix_payments_order` (`orderID`);

--
-- Indexes for table `product_options`
--
ALTER TABLE `product_options`
  ADD PRIMARY KEY (`productOptionID`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `ix_po_item` (`itemID`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`roleID`),
  ADD UNIQUE KEY `ux_roles_name` (`name`);

--
-- Indexes for table `subscribers`
--
ALTER TABLE `subscribers`
  ADD PRIMARY KEY (`subscriberID`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `ix_subscribers_user` (`userID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`userID`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email_verification` (`email_verified`),
  ADD KEY `idx_account_status` (`account_status`),
  ADD KEY `idx_google_id` (`google_id`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`userID`,`roleID`),
  ADD KEY `fk_user_roles_role` (`roleID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `addressID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `adminID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `cartID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `cartItemID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `categoryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `enquiries`
--
ALTER TABLE `enquiries`
  MODIFY `enquiryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `itemID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `orderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `orderItemID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `pageID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `paymentID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_options`
--
ALTER TABLE `product_options`
  MODIFY `productOptionID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `roleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `subscribers`
--
ALTER TABLE `subscribers`
  MODIFY `subscriberID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `userID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `fk_addresses_user` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE SET NULL;

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `fk_carts_user` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE SET NULL;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `fk_ci_cart` FOREIGN KEY (`cartID`) REFERENCES `carts` (`cartID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ci_item` FOREIGN KEY (`itemID`) REFERENCES `items` (`itemID`);

--
-- Constraints for table `enquiries`
--
ALTER TABLE `enquiries`
  ADD CONSTRAINT `enquiries_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE SET NULL;

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `fk_fav_item` FOREIGN KEY (`itemID`) REFERENCES `items` (`itemID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_fav_user` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `fk_orders_billing_addr` FOREIGN KEY (`billingAddressID`) REFERENCES `addresses` (`addressID`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_orders_shipping_addr` FOREIGN KEY (`shippingAddressID`) REFERENCES `addresses` (`addressID`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`orderID`) REFERENCES `orders` (`orderID`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`itemID`) REFERENCES `items` (`itemID`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_order` FOREIGN KEY (`orderID`) REFERENCES `orders` (`orderID`) ON DELETE CASCADE;

--
-- Constraints for table `product_options`
--
ALTER TABLE `product_options`
  ADD CONSTRAINT `fk_po_item` FOREIGN KEY (`itemID`) REFERENCES `items` (`itemID`) ON DELETE CASCADE;

--
-- Constraints for table `subscribers`
--
ALTER TABLE `subscribers`
  ADD CONSTRAINT `fk_subscribers_user` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE SET NULL;

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `fk_user_roles_role` FOREIGN KEY (`roleID`) REFERENCES `roles` (`roleID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user_roles_user` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE CASCADE;
COMMIT;

-- Migration: Add source column to orders table
-- This allows tracking orders from different sources (KARNEEK bakery vs Carnick Canteen API)

ALTER TABLE `orders` 
ADD COLUMN `source` VARCHAR(50) DEFAULT 'KARNEEK' AFTER `grand_total`;

-- Update existing orders to have default source
UPDATE `orders` SET `source` = 'KARNEEK' WHERE `source` IS NULL OR `source` = '';

-- Add index for better query performance
CREATE INDEX `idx_orders_source` ON `orders` (`source`);



/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
