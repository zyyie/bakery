<?php
require_once __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['userID'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to submit a review']);
    exit;
}

$userID = intval($_SESSION['userID']);
$itemID = isset($_POST['itemID']) ? intval($_POST['itemID']) : 0;
$rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

// Validation
if ($itemID <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit;
}

if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Please select a rating between 1 and 5 stars']);
    exit;
}

if (empty($comment)) {
    echo json_encode(['success' => false, 'message' => 'Please write a comment']);
    exit;
}

// Check if product exists
$productQuery = "SELECT itemID FROM items WHERE itemID = ? AND status = 'Active'";
$productResult = executePreparedQuery($productQuery, "i", [$itemID]);
if (!$productResult || mysqli_num_rows($productResult) === 0) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

// Check if user has received this product (has a delivered order containing this item)
$deliveredOrderQuery = "SELECT DISTINCT o.orderID 
                        FROM orders o
                        INNER JOIN order_items oi ON o.orderID = oi.orderID
                        WHERE o.userID = ? 
                        AND o.orderStatus = 'Delivered'
                        AND oi.itemID = ?";
$deliveredOrderResult = executePreparedQuery($deliveredOrderQuery, "ii", [$userID, $itemID]);

if (!$deliveredOrderResult || mysqli_num_rows($deliveredOrderResult) === 0) {
    echo json_encode(['success' => false, 'message' => 'You can only review products you have received. Please wait until your order is delivered.']);
    exit;
}

// Check if user already reviewed this product
$existingReviewQuery = "SELECT reviewID FROM product_reviews WHERE itemID = ? AND userID = ?";
$existingReviewResult = executePreparedQuery($existingReviewQuery, "ii", [$itemID, $userID]);

if ($existingReviewResult && mysqli_num_rows($existingReviewResult) > 0) {
    // Update existing review
    $updateQuery = "UPDATE product_reviews SET rating = ?, comment = ?, reviewDate = NOW() WHERE itemID = ? AND userID = ?";
    executePreparedUpdate($updateQuery, "isii", [$rating, $comment, $itemID, $userID]);
    echo json_encode(['success' => true, 'message' => 'Review updated successfully']);
} else {
    // Insert new review
    $insertQuery = "INSERT INTO product_reviews (itemID, userID, rating, comment) VALUES (?, ?, ?, ?)";
    executePreparedUpdate($insertQuery, "iiis", [$itemID, $userID, $rating, $comment]);
    echo json_encode(['success' => true, 'message' => 'Review submitted successfully']);
}

