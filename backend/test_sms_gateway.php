<?php
/**
 * SMS Gateway Connection Test
 * This script tests if your PHP server can connect to the SMS Gateway
 * 
 * Access this at: http://localhost/bakery/backend/test_sms_gateway.php
 */

require_once __DIR__ . '/config/sms.php';

$smsConfig = require __DIR__ . '/config/sms.php';
$gateway_url = $smsConfig['gateway_url'] ?? 'http://10.179.50.3:8080';
$username = $smsConfig['gateway_username'] ?? 'sms';
$password = $smsConfig['gateway_password'] ?? '1234567890';

?>
<!DOCTYPE html>
<html>
<head>
    <title>SMS Gateway Connection Test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .test-result { padding: 15px; margin: 10px 0; border-radius: 5px; }
        .success { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .error { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .info { background-color: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
        h1 { color: #333; }
    </style>
</head>
<body>
    <h1>🔍 SMS Gateway Connection Test</h1>
    
    <?php
    echo "<div class='info'>";
    echo "<h3>Configuration:</h3>";
    echo "<p><strong>Gateway URL:</strong> $gateway_url</p>";
    echo "<p><strong>Username:</strong> $username</p>";
    echo "<p><strong>Password:</strong> " . str_repeat('*', strlen($password)) . "</p>";
    echo "</div>";
    
    // Test 1: Check if cURL is available
    echo "<div class='test-result " . (function_exists('curl_init') ? 'success' : 'error') . "'>";
    echo "<h3>Test 1: cURL Availability</h3>";
    if (function_exists('curl_init')) {
        echo "<p>✅ cURL is available</p>";
    } else {
        echo "<p>❌ cURL is NOT available. Will use file_get_contents (less reliable).</p>";
    }
    echo "</div>";
    
    // Test 2: Try to connect to SMS Gateway
    echo "<div class='test-result'>";
    echo "<h3>Test 2: Connection to SMS Gateway</h3>";
    
    $url = rtrim($gateway_url, '/') . '/messages';
    $testPayload = [
        "phoneNumbers" => ["+639999999999"], // Test number
        "textMessage" => ["text" => "Test message"],
        "withDeliveryReport" => false
    ];
    
    $response = false;
    $httpCode = null;
    $error = null;
    $responseData = null;
    
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($testPayload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Basic ' . base64_encode("$username:$password")
            ],
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($response !== false) {
            $responseData = json_decode($response, true);
            if ($httpCode >= 200 && $httpCode < 300) {
                echo "<p class='success'>✅ Connection successful! HTTP Code: $httpCode</p>";
            } else {
                echo "<p class='error'>⚠️ Connection made but server returned error. HTTP Code: $httpCode</p>";
            }
        } else {
            echo "<p class='error'>❌ Connection failed: $error</p>";
        }
    } else {
        // Fallback to file_get_contents
        $options = [
            'http' => [
                'method' => 'POST',
                'header' => [
                    'Content-Type: application/json',
                    'Authorization: Basic ' . base64_encode("$username:$password")
                ],
                'content' => json_encode($testPayload),
                'ignore_errors' => true,
                'timeout' => 10
            ]
        ];
        
        $context = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);
        
        if ($response !== false) {
            $responseData = json_decode($response, true);
            $statusLine = isset($http_response_header[0]) ? $http_response_header[0] : '';
            if (preg_match('/HTTP\/\d\.\d\s+(\d+)/', $statusLine, $matches)) {
                $httpCode = (int)$matches[1];
                if ($httpCode >= 200 && $httpCode < 300) {
                    echo "<p class='success'>✅ Connection successful! HTTP Code: $httpCode</p>";
                } else {
                    echo "<p class='error'>⚠️ Connection made but server returned error. HTTP Code: $httpCode</p>";
                }
            } else {
                echo "<p class='info'>⚠️ Response received but could not determine HTTP status</p>";
            }
        } else {
            $error = error_get_last();
            $errorMsg = $error['message'] ?? 'Unknown error';
            echo "<p class='error'>❌ Connection failed: $errorMsg</p>";
        }
    }
    
    if ($response !== false) {
        echo "<h4>Response:</h4>";
        echo "<pre>" . htmlspecialchars(json_encode($responseData ?: $response, JSON_PRETTY_PRINT)) . "</pre>";
    }
    
    if ($error) {
        echo "<h4>Error Details:</h4>";
        echo "<pre>" . htmlspecialchars($error) . "</pre>";
    }
    
    echo "</div>";
    
    // Test 3: Check firewall/network
    echo "<div class='test-result info'>";
    echo "<h3>Test 3: Troubleshooting Tips</h3>";
    echo "<ul>";
    echo "<li>Make sure SMS Gateway software is running on <strong>$gateway_url</strong></li>";
    echo "<li>Try accessing <a href='$gateway_url' target='_blank'>$gateway_url</a> in your browser</li>";
    echo "<li>Check Windows Firewall - port 8080 should be open</li>";
    echo "<li>Verify the IP address is correct (run 'ipconfig' on the SMS Gateway computer)</li>";
    echo "<li>If SMS Gateway is on different computer, ensure both are on same network</li>";
    echo "</ul>";
    echo "</div>";
    ?>
    
    <div class='test-result info'>
        <h3>📝 Next Steps</h3>
        <ol>
            <li>If connection fails, check if SMS Gateway software is running</li>
            <li>Verify the IP address and port in <code>backend/config/sms.php</code></li>
            <li>Test sending OTP from the signup page after fixing connection issues</li>
            <li>Check <code>backend/logs/sms_log.txt</code> for detailed error logs</li>
        </ol>
    </div>
</body>
</html>
