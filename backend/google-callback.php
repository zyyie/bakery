<?php
require_once __DIR__ . '/includes/bootstrap.php';
// Vendor autoload is in the root directory, not backend
require_once __DIR__ . '/../vendor/autoload.php';

use Google\Client;
use Google\Service\Oauth2;

$error = "";

// Check if user is already logged in
if(isset($_SESSION['userID'])) {
    header("Location: index.php");
    exit();
}

// Get Google OAuth config
$config = require __DIR__ . '/config/google-oauth.php';

// Use the helper function for consistent redirect URI
$config['redirect_uri'] = get_google_redirect_uri();

// Check if we have the authorization code
if(isset($_GET['code'])) {
    try {
        // Initialize Google Client
        $client = new Client();
        $client->setClientId($config['client_id']);
        $client->setClientSecret($config['client_secret']);
        $client->setRedirectUri($config['redirect_uri']);
        $client->addScope($config['scopes']);
        
        // Exchange authorization code for access token
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        
        if(isset($token['error'])) {
            $error = "Google authentication failed: " . $token['error_description'];
            error_log("Google OAuth Token Error: " . print_r($token, true));
        } else {
            // Get user info
            $client->setAccessToken($token);
            $oauth2 = new Oauth2($client);
            $userInfo = $oauth2->userinfo->get();
            
            $googleId = $userInfo->getId();
            $email = $userInfo->getEmail();
            $fullName = $userInfo->getName();
            $picture = $userInfo->getPicture();
            
            if(empty($email)) {
                $error = "Unable to retrieve email from Google account.";
                error_log("Google OAuth: No email retrieved");
            } else {
                // Check if user exists with this Google ID or email
                $query = "SELECT userID, fullName, email, google_id FROM users WHERE google_id = ? OR email = ? LIMIT 1";
                $result = executePreparedQuery($query, "ss", [$googleId, $email]);
                
                if($result && $user = $result->fetch_assoc()) {
                    // User exists - update Google ID if not set and log them in
                    if(empty($user['google_id'])) {
                        $updateQuery = "UPDATE users SET google_id = ? WHERE userID = ?";
                        executePreparedUpdate($updateQuery, "si", [$googleId, $user['userID']]);
                    }
                    
                    // Set session variables (session is already started by bootstrap.php)
                    $_SESSION['userID'] = (int)$user['userID'];
                    $_SESSION['fullName'] = $user['fullName'];
                    $_SESSION['email'] = $user['email'];
                    
                    // Reset failed login attempts
                    $resetQuery = "UPDATE users SET failed_login_attempts = 0, last_login = NOW() WHERE userID = ?";
                    executePreparedUpdate($resetQuery, "i", [$user['userID']]);
                    
                    // Get redirect URL before unsetting
                    $redirect = isset($_SESSION['redirect_after_login']) ? 
                        $_SESSION['redirect_after_login'] : 'index.php';
                    unset($_SESSION['redirect_after_login']);
                    
                    // Ensure session is saved
                    session_write_close();
                    
                    // Redirect
                    header("Location: " . $redirect);
                    exit();
                } else {
                    // New user - create account
                    // Generate a random password (user won't need it for Google login)
                    $randomPassword = bin2hex(random_bytes(16));
                    $hashedPassword = password_hash($randomPassword, PASSWORD_BCRYPT);
                    
                    // Set mobile number to empty - user must provide it before checkout
                    $mobileNumber = '';
                    
                    $insertQuery = "INSERT INTO users (fullName, email, mobileNumber, password, google_id) 
                                   VALUES (?, ?, ?, ?, ?)";
                    $insertResult = executePreparedUpdate($insertQuery, "sssss", [
                        $fullName, 
                        $email, 
                        $mobileNumber, 
                        $hashedPassword, 
                        $googleId
                    ]);
                    
                    if($insertResult !== false) {
                        // Get the newly created user
                        $newUserQuery = "SELECT userID, fullName, email FROM users WHERE google_id = ? LIMIT 1";
                        $newUserResult = executePreparedQuery($newUserQuery, "s", [$googleId]);
                        
                        if($newUserResult && $newUser = $newUserResult->fetch_assoc()) {
                            // Set session variables (session is already started by bootstrap.php)
                            $_SESSION['userID'] = (int)$newUser['userID'];
                            $_SESSION['fullName'] = $newUser['fullName'];
                            $_SESSION['email'] = $newUser['email'];
                            
                            // Get redirect URL before unsetting
                            $redirect = isset($_SESSION['redirect_after_login']) ? 
                                $_SESSION['redirect_after_login'] : 'index.php';
                            unset($_SESSION['redirect_after_login']);
                            
                            // Ensure session is saved
                            session_write_close();
                            
                            // Redirect
                            header("Location: " . $redirect);
                            exit();
                        } else {
                            $error = "Account created but failed to log in. Please try logging in manually.";
                            error_log("Google OAuth: Failed to retrieve newly created user with google_id: " . $googleId);
                        }
                    } else {
                        $error = "Failed to create account. Please try again.";
                        error_log("Google OAuth: Failed to insert new user. DB Error: " . ($GLOBALS['db_last_error'] ?? 'Unknown'));
                    }
                }
            }
        }
    } catch (Exception $e) {
        $error = "Google authentication error: " . $e->getMessage();
        error_log("Google OAuth Exception: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
    }
} else if(isset($_GET['error'])) {
    $error = "Google authentication was cancelled.";
} else {
    $error = "Invalid request. Please try again.";
}

// If we reach here, there was an error
include("includes/login-header.php");
?>

<div class="auth-container">
  <div class="auth-card">
    <div class="text-center mb-4">
      <img src="../frontend/images/logo.png" alt="Bakery Logo" class="auth-logo mb-3">
      <h2 class="auth-title mb-1">Authentication Error</h2>
    </div>

    <?php if($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="text-center mt-3">
      <a href="login.php" class="btn btn-brown">Back to Login</a>
    </div>
  </div>
</div>

<?php include("includes/login-footer.php"); ?>

