<?php
/**
 * Test page to verify reset link accessibility
 * Access this at: http://192.168.18.115/bakery/backend/test-reset-link.php
 */
require_once __DIR__ . '/../includes/bootstrap.php';

$baseUrl = get_reset_link_base_url();
$testLink = $baseUrl . '/backend/reset-password.php?token=TEST_TOKEN_123';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Link Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-link me-2"></i>Reset Link Test</h4>
                    </div>
                    <div class="card-body">
                        <h5>Configuration Status</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th>Base URL (from config):</th>
                                <td><code><?php echo htmlspecialchars($baseUrl); ?></code></td>
                            </tr>
                            <tr>
                                <th>Full Reset Link Format:</th>
                                <td><code><?php echo htmlspecialchars($testLink); ?></code></td>
                            </tr>
                            <tr>
                                <th>Server IP:</th>
                                <td><code><?php echo htmlspecialchars($_SERVER['SERVER_ADDR'] ?? 'Not set'); ?></code></td>
                            </tr>
                            <tr>
                                <th>HTTP Host:</th>
                                <td><code><?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'Not set'); ?></code></td>
                            </tr>
                            <tr>
                                <th>Current URL:</th>
                                <td><code><?php echo htmlspecialchars((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?></code></td>
                            </tr>
                        </table>
                        
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-2"></i>Testing Instructions:</h6>
                            <ol>
                                <li>Copy the "Full Reset Link Format" above</li>
                                <li>Replace <code>TEST_TOKEN_123</code> with an actual token from a password reset request</li>
                                <li>Open the link in your mobile browser (must be on same WiFi network)</li>
                                <li>If it doesn't work, check:
                                    <ul>
                                        <li>Windows Firewall - allow port 80</li>
                                        <li>XAMPP Apache is running</li>
                                        <li>Mobile device is on same WiFi network</li>
                                        <li>IP address is correct (run <code>ipconfig</code> to verify)</li>
                                    </ul>
                                </li>
                            </ol>
                        </div>
                        
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Common Issues:</h6>
                            <ul>
                                <li><strong>Link unreachable:</strong> Check Windows Firewall settings, allow Apache/HTTP on port 80</li>
                                <li><strong>Wrong page:</strong> Verify the path is correct: <code>/bakery/backend/reset-password.php</code></li>
                                <li><strong>Connection timeout:</strong> Make sure XAMPP Apache is running and listening on all interfaces (0.0.0.0:80)</li>
                            </ul>
                        </div>
                        
                        <a href="<?php echo htmlspecialchars($baseUrl . '/backend/reset-password.php'); ?>" class="btn btn-primary" target="_blank">
                            <i class="fas fa-external-link-alt me-2"></i>Test Reset Password Page (without token)
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
