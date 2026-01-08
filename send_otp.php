<?php
$gateway_url = "http://192.168.1.101:8080";
$username = "sms";
$password = "1234567890";

// Get recipient from URL parameter or form input
$recipient = $_GET['phone'] ?? $_POST['phone'] ?? "";

// Clean and validate phone number
$recipient = trim($recipient);
if (!empty($recipient) && !str_starts_with($recipient, '+')) {
    $recipient = '+' . $recipient;
}

if (empty($recipient)) {
    echo "<h3>SMS Sender</h3>";
    echo "<form method='get' action=''>";
    echo "<input type='text' name='phone' placeholder='Enter phone number (+63...)' required style='width: 300px; padding: 10px;'>";
    echo "<button type='submit' style='padding: 10px 20px; margin-left: 10px;'>Send OTP</button>";
    echo "</form>";
    echo "<p><small>Example: +639123456789</small></p>";
    exit;
}

$otp = rand(100000, 999999);
$message = "Your OTP is $otp. Do not share this code with anyone.";

$url = rtrim($gateway_url, '/') . '/messages';

$payload = [
    "phoneNumbers" => [$recipient],
    "textMessage" => ["text" => $message],
    "withDeliveryReport" => true
];

$options = [
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode("$username:$password")
        ],
        'content' => json_encode($payload)
    ]
];

$context = stream_context_create($options);
$response = @file_get_contents($url, false, $context);

// Check for connection errors
if ($response === false) {
    $error = error_get_last();
    $error_message = $error['message'] ?? 'Unknown error';
    echo "<h3>SMS Gateway Error</h3>";
    echo "<p style='color: red;'><strong>Connection Failed:</strong> $error_message</p>";
    echo "<p><strong>Gateway URL:</strong> $gateway_url</p>";
    echo "<p><strong>Solution:</strong> Check if the SMS gateway server is running and accessible.</p>";
} else {
    echo "<h3>OTP Sent</h3>";
}

echo "<p>Recipient: <strong>$recipient</strong></p>";
echo "<p>Generated OTP: <strong>$otp</strong></p>";
echo "<h4>API Response: </h4>";
echo "<pre>" . htmlspecialchars($response ?: "No response from SMSGate.") . "</pre>";
?>
