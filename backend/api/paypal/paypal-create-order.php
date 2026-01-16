<?php
require_once __DIR__ . '/../../includes/bootstrap.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!isset($_SESSION['userID'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Cart is empty']);
    exit;
}

$cfg = require __DIR__ . '/../../config/paypal.php';

if (empty($cfg['client_id']) || empty($cfg['client_secret'])) {
    http_response_code(500);
    echo json_encode(['error' => 'PayPal is not configured']);
    exit;
}

// Calculate cart subtotal
$cartSubtotal = 0.0;
$totalQuantity = 0;
foreach ($_SESSION['cart'] as $itemID => $quantity) {
    $itemID = (int)$itemID;
    $quantity = (int)$quantity;
    $itemResult = executePreparedQuery('SELECT price FROM items WHERE itemID = ? AND status = \'Active\'', 'i', [$itemID]);
    if ($itemResult && mysqli_num_rows($itemResult) > 0) {
        $item = mysqli_fetch_assoc($itemResult);
        $cartSubtotal += (float)$item['price'] * $quantity;
        $totalQuantity += $quantity;
    }
}

// Calculate shipping fee
$shippingFee = 30.00; // Default
if ($totalQuantity >= 10 || $cartSubtotal >= 500) {
    $shippingFee = 0.00;
} elseif ($totalQuantity >= 5 || $cartSubtotal >= 200) {
    $shippingFee = 20.00;
}

// Calculate discount based on coupon
$discountAmount = 0.0;
$selectedCouponCode = $_SESSION['selected_coupon'] ?? null;
$availableCoupons = [
    'SAVE10' => ['name' => '10% Off', 'type' => 'percentage', 'value' => 10],
    'SAVE20' => ['name' => '20% Off', 'type' => 'percentage', 'value' => 20],
    'SAVE50' => ['name' => '₱50 Off', 'type' => 'fixed', 'value' => 50],
    'FREESHIP50' => ['name' => 'Free Shipping', 'type' => 'free_shipping', 'min_order' => 50],
    'FREESHIP100' => ['name' => 'Free Shipping', 'type' => 'free_shipping', 'min_order' => 100],
    'FREESHIP200' => ['name' => 'Free Shipping', 'type' => 'free_shipping', 'min_order' => 200],
];

if ($selectedCouponCode && isset($availableCoupons[$selectedCouponCode])) {
    $coupon = $availableCoupons[$selectedCouponCode];
    
    if ($coupon['type'] === 'percentage') {
        $discountAmount = $cartSubtotal * ($coupon['value'] / 100);
    } elseif ($coupon['type'] === 'fixed') {
        $discountAmount = min($coupon['value'], $cartSubtotal);
    } elseif ($coupon['type'] === 'free_shipping') {
        // Check if coupon applies (minimum order and quantity requirements)
        if ($totalQuantity >= 3 && $cartSubtotal >= $coupon['min_order']) {
            // For free shipping coupons, the discount is the shipping fee
            $discountAmount = $shippingFee;
            $shippingFee = 0;
        }
    }
}

// Calculate final total: subtotal - discount + shipping
$finalTotal = $cartSubtotal - $discountAmount + $shippingFee;
$amount = number_format(max(0, $finalTotal), 2, '.', '');

$base = ($cfg['mode'] === 'live') ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $base . '/v1/oauth2/token',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_USERPWD => $cfg['client_id'] . ':' . $cfg['client_secret'],
    CURLOPT_HTTPHEADER => ['Accept: application/json', 'Accept-Language: en_US'],
    CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
]);

$authResp = curl_exec($ch);
if ($authResp === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to connect to PayPal']);
    exit;
}
$authJson = json_decode($authResp, true);
$token = $authJson['access_token'] ?? null;
if (!$token) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to authenticate with PayPal']);
    exit;
}

// Build detailed amount breakdown for PayPal
$purchaseUnitAmount = [
    'currency_code' => $cfg['currency'],
    'value' => $amount,
    'breakdown' => [
        'item_total' => [
            'currency_code' => $cfg['currency'],
            'value' => number_format($cartSubtotal, 2, '.', ''),
        ],
    ],
];

// Add discount if applicable
if ($discountAmount > 0) {
    $purchaseUnitAmount['breakdown']['discount'] = [
        'currency_code' => $cfg['currency'],
        'value' => number_format($discountAmount, 2, '.', ''),
    ];
}

// Add shipping fee
if ($shippingFee > 0) {
    $purchaseUnitAmount['breakdown']['shipping'] = [
        'currency_code' => $cfg['currency'],
        'value' => number_format($shippingFee, 2, '.', ''),
    ];
} else {
    $purchaseUnitAmount['breakdown']['shipping'] = [
        'currency_code' => $cfg['currency'],
        'value' => '0.00',
    ];
}

$orderPayload = [
    'intent' => 'CAPTURE',
    'purchase_units' => [[
        'amount' => $purchaseUnitAmount,
    ]],
];

curl_setopt_array($ch, [
    CURLOPT_URL => $base . '/v2/checkout/orders',
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token,
    ],
    CURLOPT_POSTFIELDS => json_encode($orderPayload),
]);

$orderResp = curl_exec($ch);
curl_close($ch);

if ($orderResp === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to create PayPal order']);
    exit;
}

echo $orderResp;
