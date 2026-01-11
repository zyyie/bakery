<?php
/**
 * Quick fix script - Shows the exact redirect URI you need to add to Google Cloud Console
 * This will help you fix the redirect_uri_mismatch error
 */

require_once __DIR__ . '/includes/bootstrap.php';

$redirectUri = get_google_redirect_uri();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix Redirect URI Mismatch</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 700px;
            width: 100%;
            padding: 40px;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 16px;
        }
        .alert {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .alert.error {
            background: #f8d7da;
            border-left-color: #dc3545;
        }
        .alert.success {
            background: #d4edda;
            border-left-color: #28a745;
        }
        .uri-box {
            background: #f8f9fa;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            position: relative;
        }
        .uri-box code {
            font-size: 16px;
            word-break: break-all;
            color: #212529;
            font-family: 'Courier New', monospace;
        }
        .copy-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 15px;
            width: 100%;
            transition: background 0.3s;
        }
        .copy-btn:hover {
            background: #0056b3;
        }
        .copy-btn.copied {
            background: #28a745;
        }
        .steps {
            background: #e7f3ff;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .steps ol {
            margin-left: 20px;
            margin-top: 10px;
        }
        .steps li {
            margin: 10px 0;
            line-height: 1.6;
        }
        .steps a {
            color: #007bff;
            text-decoration: none;
        }
        .steps a:hover {
            text-decoration: underline;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #007bff;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Fix Redirect URI Mismatch</h1>
        <p class="subtitle">Copy the redirect URI below and add it to Google Cloud Console</p>
        
        <div class="alert error">
            <strong>⚠️ Error 400: redirect_uri_mismatch</strong><br>
            The redirect URI in your code doesn't match what's configured in Google Cloud Console.
        </div>
        
        <div class="uri-box">
            <strong style="display: block; margin-bottom: 10px; color: #495057;">Copy this exact URL:</strong>
            <code id="redirectUri"><?php echo htmlspecialchars($redirectUri); ?></code>
            <button class="copy-btn" onclick="copyToClipboard()">📋 Copy to Clipboard</button>
        </div>
        
        <div class="steps">
            <strong style="display: block; margin-bottom: 10px; color: #004085;">Steps to fix:</strong>
            <ol>
                <li>Go to <a href="https://console.cloud.google.com/apis/credentials" target="_blank">Google Cloud Console → Credentials</a></li>
                <li>Click on your OAuth 2.0 Client ID (the one you created for KARNEEK Bakery)</li>
                <li>Scroll down to <strong>"Authorized redirect URIs"</strong></li>
                <li>Click <strong>"+ Add URI"</strong></li>
                <li>Paste the URL shown above (the one you just copied)</li>
                <li>Click <strong>"Save"</strong> at the bottom</li>
                <li><strong>Wait 2-5 minutes</strong> for Google to update the settings</li>
                <li>Try logging in with Google again</li>
            </ol>
        </div>
        
        <div class="alert">
            <strong>💡 Tip:</strong> Make sure the URL matches <strong>exactly</strong> - including:
            <ul style="margin-left: 20px; margin-top: 10px;">
                <li>Protocol (http or https)</li>
                <li>Host (localhost or your domain)</li>
                <li>Path (/bakery/backend/google-callback.php)</li>
                <li>No trailing slash</li>
            </ul>
        </div>
        
        <a href="login.php" class="back-link">← Back to Login</a>
    </div>
    
    <script>
        function copyToClipboard() {
            const uri = document.getElementById('redirectUri').textContent;
            navigator.clipboard.writeText(uri).then(function() {
                const btn = document.querySelector('.copy-btn');
                const originalText = btn.textContent;
                btn.textContent = '✓ Copied!';
                btn.classList.add('copied');
                setTimeout(function() {
                    btn.textContent = originalText;
                    btn.classList.remove('copied');
                }, 2000);
            });
        }
    </script>
</body>
</html>

