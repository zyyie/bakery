<?php
require_once __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/json');

if (!isset($_SESSION['userID'])) {
    echo json_encode(['canReview' => false, 'message' => 'Please login to review']);
    exit;
}

$userID = intval($_SESSION['userID']);
$itemID = isset($_GET['itemID']) ? intval($_GET['itemID']) : 0;

if ($itemID <= 0) {
    echo json_encode(['canReview' => false, 'message' => 'Invalid product']);
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

$canReview = ($deliveredOrderResult && mysqli_num_rows($deliveredOrderResult) > 0);

echo json_encode([
    'canReview' => $canReview,
    'message' => $canReview ? 'You can review this product' : 'You can only review products you have received. Please wait until your order is delivered.'
]);

