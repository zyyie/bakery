<?php
/**
 * Test script to debug Google OAuth login issue
 * This will show you what's happening during the OAuth flow
 */

require_once __DIR__ . '/includes/bootstrap.php';

// Check if we're coming from Google OAuth callback
if(isset($_GET['code'])) {
    echo "<h1>Google OAuth Callback Received</h1>";
    echo "<p>Authorization Code: " . htmlspecialchars(substr($_GET['code'], 0, 20)) . "...</p>";
    
    // Check session
    echo "<h2>Session Status:</h2>";
    echo "<pre>";
    echo "Session ID: " . session_id() . "\n";
    echo "Session Status: " . session_status() . "\n";
    echo "Session Variables:\n";
    print_r($_SESSION);
    echo "</pre>";
    
    echo "<h2>Next Steps:</h2>";
    echo "<p>Go back to <a href='google-callback.php?code=" . htmlspecialchars($_GET['code']) . "'>google-callback.php</a> to complete authentication.</p>";
} else {
    echo "<h1>Google OAuth Test</h1>";
    echo "<p>Current Session:</p>";
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
    
    echo "<p>Session ID: " . session_id() . "</p>";
    echo "<p>Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? "Active" : "Inactive") . "</p>";
    
    // Test setting session
    $_SESSION['test'] = 'Session is working!';
    echo "<p>✓ Test session variable set. Refresh to see if it persists.</p>";
    
    echo "<p><a href='login.php'>Go to Login</a></p>";
}

?>

