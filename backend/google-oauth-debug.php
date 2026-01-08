<?php
/**
 * Debug script to check Google OAuth redirect URI
 * Use this to find the exact redirect URI to add in Google Cloud Console
 */

require_once __DIR__ . '/includes/bootstrap.php';

// Use the helper function for consistent redirect URI
$redirectUri = get_google_redirect_uri();

// Also get individual components for debugging
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$scriptPath = dirname($_SERVER['SCRIPT_NAME']);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Google OAuth Debug</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .debug-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        h1 {
            color: #333;
        }
        .info {
            background: #e3f2fd;
            padding: 15px;
            border-left: 4px solid #2196F3;
            margin: 15px 0;
        }
        .code {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 4px;
            font-family: monospace;
            word-break: break-all;
            margin: 10px 0;
        }
        .warning {
            background: #fff3cd;
            padding: 15px;
            border-left: 4px solid #ffc107;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="debug-box">
        <h1>Google OAuth Redirect URI Debug</h1>
        
        <div class="info">
            <strong>Copy this exact URL and add it to Google Cloud Console:</strong>
        </div>
        
        <div class="code">
            <?php echo htmlspecialchars($redirectUri); ?>
        </div>
        
        <div class="warning">
            <strong>Steps to fix:</strong>
            <ol>
                <li>Go to <a href="https://console.cloud.google.com/apis/credentials" target="_blank">Google Cloud Console > Credentials</a></li>
                <li>Click on your OAuth 2.0 Client ID</li>
                <li>Under "Authorized redirect URIs", click "+ Add URI"</li>
                <li>Paste the URL shown above exactly as it appears</li>
                <li>Click "Save"</li>
                <li>Wait a few minutes for changes to take effect</li>
            </ol>
        </div>
        
        <h2>Debug Information:</h2>
        <table style="width: 100%; border-collapse: collapse;">
            <tr style="background: #f5f5f5;">
                <td style="padding: 8px; border: 1px solid #ddd;"><strong>Protocol:</strong></td>
                <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars($protocol); ?></td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd;"><strong>Host:</strong></td>
                <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars($host); ?></td>
            </tr>
            <tr style="background: #f5f5f5;">
                <td style="padding: 8px; border: 1px solid #ddd;"><strong>Script Path:</strong></td>
                <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars($scriptPath); ?></td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd;"><strong>Full Redirect URI:</strong></td>
                <td style="padding: 8px; border: 1px solid #ddd;" class="code"><?php echo htmlspecialchars($redirectUri); ?></td>
            </tr>
        </table>
        
        <div style="margin-top: 20px;">
            <a href="login.php" style="color: #2196F3;">← Back to Login</a>
        </div>
    </div>
</body>
</html>

