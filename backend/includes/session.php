<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Set secure session parameters
    $cookieParams = session_get_cookie_params();
    // Use 'Lax' instead of 'Strict' for SameSite to allow OAuth redirects
    // 'Lax' still provides good security while allowing cross-site redirects (like OAuth)
    session_set_cookie_params([
        'lifetime' => 86400, // 24 hours
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    session_start();
}

// Regenerate session ID periodically to prevent session fixation
function regenerateSession() {
    // Don't regenerate too frequently (at most once every 5 minutes)
    if (!isset($_SESSION['last_regeneration']) || 
        (time() - $_SESSION['last_regeneration']) > 300) {
        
        $_SESSION['last_regeneration'] = time();
        session_regenerate_id(true);
    }
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['userID']);
}

// Require login for protected pages
function requireLogin() {
    if (!isLoggedIn()) {
        // Store the current URL for redirecting after login
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: login.php');
        exit();
    }
    
    // Regenerate session ID for security
    regenerateSession();
}

// Require admin role
function requireAdmin() {
    requireLogin();
    
    // In a real application, you would check the user's role here
    // For now, we'll just check if they're the admin user
    if (!isset($_SESSION['email']) || $_SESSION['email'] !== 'admin@example.com') {
        header('HTTP/1.0 403 Forbidden');
        die('Access Denied: You do not have permission to access this page.');
    }
}

// CSRF Protection
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }
    return true;
}

// Flash message helper
function setFlashMessage($type, $message) {
    $_SESSION['flash_messages'][] = ['type' => $type, 'message' => $message];
}

function getFlashMessages() {
    if (isset($_SESSION['flash_messages'])) {
        $messages = $_SESSION['flash_messages'];
        unset($_SESSION['flash_messages']);
        return $messages;
    }
    return [];
}

// Display flash messages
function displayFlashMessages() {
    $messages = getFlashMessages();
    if (!empty($messages)) {
        foreach ($messages as $message) {
            echo '<div class="alert alert-' . htmlspecialchars($message['type']) . '">' . 
                 htmlspecialchars($message['message']) . '</div>';
        }
    }
}

// Initialize CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
    generateCsrfToken();
}
?>
