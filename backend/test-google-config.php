<?php
/**
 * Test script to verify Google OAuth configuration
 */

require_once __DIR__ . '/includes/bootstrap.php';

$config = require __DIR__ . '/config/google-oauth.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Google OAuth Config Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .test-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .code {
            background: #f5f5f5;
            padding: 10px;
            border-radius: 4px;
            font-family: monospace;
            word-break: break-all;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="test-box">
        <h1>Google OAuth Configuration Test</h1>
        
        <?php
        $hasClientId = !empty($config['client_id']) && $config['client_id'] !== 'YOUR_GOOGLE_CLIENT_ID_HERE';
        $hasClientSecret = !empty($config['client_secret']) && $config['client_secret'] !== 'YOUR_GOOGLE_CLIENT_SECRET_HERE';
        
        if ($hasClientId && $hasClientSecret) {
            echo '<div class="success">✓ Configuration looks good! Client ID and Secret are set.</div>';
        } else {
            echo '<div class="error">✗ Configuration incomplete. Please check your settings.</div>';
        }
        ?>
        
        <h2>Configuration Details:</h2>
        
        <div class="info">
            <strong>Client ID:</strong>
            <div class="code">
                <?php 
                if ($hasClientId) {
                    echo htmlspecialchars($config['client_id']);
                } else {
                    echo '<span style="color: red;">NOT SET</span>';
                }
                ?>
            </div>
        </div>
        
        <div class="info">
            <strong>Client Secret:</strong>
            <div class="code">
                <?php 
                if ($hasClientSecret) {
                    echo htmlspecialchars(substr($config['client_secret'], 0, 10)) . '...' . ' (hidden for security)';
                } else {
                    echo '<span style="color: red;">NOT SET</span>';
                }
                ?>
            </div>
        </div>
        
        <h2>Test Google Login URL:</h2>
        <?php
        // Test the getGoogleLoginUrl function
        function getGoogleLoginUrl() {
            $config = require __DIR__ . '/config/google-oauth.php';
            
            if($config['client_id'] === 'YOUR_GOOGLE_CLIENT_ID_HERE' || empty($config['client_id'])) {
                return null;
            }
            
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
            $scriptPath = rtrim($scriptPath, '/');
            $redirectUri = $protocol . '://' . $host . $scriptPath . '/google-callback.php';
            
            $params = [
                'client_id' => $config['client_id'],
                'redirect_uri' => $redirectUri,
                'response_type' => 'code',
                'scope' => implode(' ', $config['scopes']),
                'access_type' => 'online',
                'prompt' => 'select_account'
            ];
            
            return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
        }
        
        $googleLoginUrl = getGoogleLoginUrl();
        
        if ($googleLoginUrl) {
            echo '<div class="success">✓ Google Login URL generated successfully!</div>';
            echo '<div class="info"><strong>Login URL:</strong><div class="code">' . htmlspecialchars($googleLoginUrl) . '</div></div>';
            echo '<div class="info"><strong>Redirect URI:</strong><div class="code">' . htmlspecialchars($protocol . '://' . $host . $scriptPath . '/google-callback.php') . '</div></div>';
        } else {
            echo '<div class="error">✗ Could not generate Google Login URL. Check your configuration.</div>';
        }
        ?>
        
        <div style="margin-top: 20px;">
            <a href="login.php" style="color: #2196F3; text-decoration: none; padding: 10px 20px; background: #e3f2fd; border-radius: 4px; display: inline-block;">← Back to Login</a>
        </div>
    </div>
</body>
</html>

