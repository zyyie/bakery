<?php
$gateway_url = "http://192.168.1.101:8080";
$username = "sms";
$password = "1234567890";
$recipient = "+639618709063";
$otp = rand(100000, 999999);
$message = "Your OTP is $otp. Do not share this code with anyone.";

$url = rtrim($gateway_url, '/') . '/messages';

$payload = [
    "phoneNumbers" => [$recipient],
    "message" => $message
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
