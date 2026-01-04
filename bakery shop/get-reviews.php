<?php
require_once __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/json');

$itemID = isset($_GET['itemID']) ? intval($_GET['itemID']) : 0;

if ($itemID <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit;
}

// Fetch reviews with user information (only show reviews from users who have received the product)
$query = "SELECT pr.reviewID, pr.rating, pr.comment, pr.reviewDate, 
                 u.fullName, u.userID
          FROM product_reviews pr
          INNER JOIN users u ON pr.userID = u.userID
          WHERE pr.itemID = ? 
          AND (pr.status = 'Approved' OR pr.status IS NULL OR pr.status = '')
          AND EXISTS (
              SELECT 1 
              FROM orders o
              INNER JOIN order_items oi ON o.orderID = oi.orderID
              WHERE o.userID = pr.userID
              AND o.orderStatus = 'Delivered'
              AND oi.itemID = pr.itemID
          )
          ORDER BY pr.reviewDate DESC
          LIMIT 50";

$result = executePreparedQuery($query, "i", [$itemID]);

$reviews = [];
$totalRating = 0;
$ratingCount = 0;

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $reviews[] = [
            'reviewID' => $row['reviewID'],
            'rating' => intval($row['rating']),
            'comment' => $row['comment'],
            'reviewDate' => $row['reviewDate'],
            'fullName' => $row['fullName'],
            'userID' => intval($row['userID']),
            'isCurrentUser' => (isset($_SESSION['userID']) && intval($row['userID']) === intval($_SESSION['userID']))
        ];
        $totalRating += intval($row['rating']);
        $ratingCount++;
    }
}

$averageRating = $ratingCount > 0 ? round($totalRating / $ratingCount, 1) : 0;

echo json_encode([
    'success' => true,
    'reviews' => $reviews,
    'averageRating' => $averageRating,
    'totalReviews' => $ratingCount
]);

