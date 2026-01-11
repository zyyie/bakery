<?php
require_once __DIR__ . '/includes/bootstrap.php';

$error = "";

// Check if user is already logged in
if(isset($_SESSION['userID'])) {
    header("Location: index.php");
    exit();
}

// Function to get Google OAuth login URL
function getGoogleLoginUrl() {
    $config = require __DIR__ . '/config/google-oauth.php';
    
    // Check if Google OAuth is configured
    if($config['client_id'] === 'YOUR_GOOGLE_CLIENT_ID_HERE' || empty($config['client_id'])) {
        return null; // Google OAuth not configured
    }
    // Hide button if composer dependencies are not installed
    $autoloadPath = __DIR__ . '/../vendor/autoload.php';
    if (!file_exists($autoloadPath)) {
        return null;
    }
    
    // Use the helper function for consistent redirect URI
    $redirectUri = get_google_redirect_uri();
    
    // Build Google OAuth URL
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

if(isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $remember = isset($_POST['remember']) ? true : false;
    
    // Input validation
    if(empty($email) || empty($password)) {
        $error = "Both email and password are required!";
    } else {
        // Get user from database using prepared statement
        $query = "SELECT userID, fullName, email, password, failed_login_attempts, last_failed_login 
                 FROM users WHERE email = ?";
        $result = executePreparedQuery($query, "s", [$email]);
        if ($result === false) {
            $query = "SELECT userID, fullName, email, password FROM users WHERE email = ?";
            $result = executePreparedQuery($query, "s", [$email]);
        }
        
        if($result && $user = $result->fetch_assoc()) {
            $user['failed_login_attempts'] = isset($user['failed_login_attempts']) ? (int)$user['failed_login_attempts'] : 0;
            $user['last_failed_login'] = isset($user['last_failed_login']) ? $user['last_failed_login'] : null;

            // Check for too many failed attempts
            if($user['failed_login_attempts'] >= 5) {
                $lastAttempt = strtotime($user['last_failed_login']);
                $waitTime = 15 * 60; // 15 minutes in seconds
                
                if(time() - $lastAttempt < $waitTime) {
                    $remainingTime = ceil(($waitTime - (time() - $lastAttempt)) / 60);
                    $error = "Too many failed login attempts. Please try again in $remainingTime minutes.";
                } else {
                    // Reset failed attempts after waiting period
                    $resetQuery = "UPDATE users SET failed_login_attempts = 0 WHERE userID = ?";
                    executePreparedUpdate($resetQuery, "i", [$user['userID']]);
                }
            }
            
            // If no error, verify password
            if(empty($error)) {
                // Check if password is MD5 (legacy) or bcrypt (new)
                $passwordValid = false;
                $needsUpgrade = false;
                
                // Check if password is MD5
                if(strlen($user['password']) == 32 && ctype_xdigit($user['password'])) {
                    // Verify MD5 password
                    if(md5($password) === $user['password']) {
                        $passwordValid = true;
                        $needsUpgrade = true;
                    }
                } else {
                    // Verify bcrypt password
                    if(password_verify($password, $user['password'])) {
                        $passwordValid = true;
                        // Check if needs rehashing
                        if(password_needs_rehash($user['password'], PASSWORD_BCRYPT)) {
                            $needsUpgrade = true;
                        }
                    }
                }
                
                if($passwordValid) {
                    // Upgrade password hash if needed
                    if($needsUpgrade) {
                        $newHash = password_hash($password, PASSWORD_BCRYPT);
                        $updateQuery = "UPDATE users SET password = ? WHERE userID = ?";
                        executePreparedUpdate($updateQuery, "si", [$newHash, $user['userID']]);
                    }
                    
                    // Reset failed login attempts
                    $resetQuery = "UPDATE users SET failed_login_attempts = 0, last_login = NOW() WHERE userID = ?";
                    executePreparedUpdate($resetQuery, "i", [$user['userID']]);
                    
                    // Set session variables
                    $_SESSION['userID'] = $user['userID'];
                    $_SESSION['fullName'] = $user['fullName'];
                    $_SESSION['email'] = $user['email'];
                    
                    // Set remember me cookie if checked
                    if($remember) {
                        $token = bin2hex(random_bytes(32));
                        $tokenHash = hash('sha256', $token);
                        $expires = time() + (86400 * 30); // 30 days
                        set_app_cookie('remember_token', $token, $expires);
                        
                        // Store token in database
                        $expiryDate = date('Y-m-d H:i:s', $expires);
                        $updateToken = "UPDATE users SET remember_token = ?, token_expires = ? WHERE userID = ?";
                        executePreparedUpdate($updateToken, "ssi", [$tokenHash, $expiryDate, $user['userID']]);
                    }
                    
                    // Redirect to intended page or home
                    $redirect = isset($_SESSION['redirect_after_login']) ? 
                        $_SESSION['redirect_after_login'] : 'index.php';
                    unset($_SESSION['redirect_after_login']);
                    header("Location: " . $redirect);
                    exit();
                } else {
                    // Increment failed login attempts
                    $attempts = $user['failed_login_attempts'] + 1;
                    $updateQuery = "UPDATE users SET failed_login_attempts = ?, last_failed_login = NOW() WHERE userID = ?";
                    executePreparedUpdate($updateQuery, "ii", [$attempts, $user['userID']]);
                    
                    $remainingAttempts = 5 - $attempts;
                    if($remainingAttempts > 0) {
                        $error = "Invalid email or password! $remainingAttempts attempts remaining.";
                    } else {
                        $error = "Account locked for 15 minutes due to too many failed attempts.";
                    }
                }
            }
        } else {
            // User not found, but don't reveal that to prevent user enumeration
            $error = "Invalid email or password!";
        }
    }
}

include("includes/login-header.php");
?>

<div class="auth-container">
  <div class="auth-card">
    <div class="text-center mb-4">
      <img src="../frontend/images/logo.png" alt="Bakery Logo" class="auth-logo mb-3">
      <h2 class="auth-title mb-1">Welcome Back</h2>
      <div class="auth-subtitle">Login to continue shopping</div>
    </div>

    <?php if($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" class="auth-form" id="loginForm">
      <div class="mb-3">
        <label class="form-label">Email <span class="text-danger">*</span></label>
        <input type="email" class="form-control" name="email" placeholder="Enter Email" required 
               maxlength="255"
               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
        <div class="invalid-feedback">Please enter a valid email address.</div>
      </div>
      <div class="mb-2">
        <label class="form-label">Password <span class="text-danger">*</span></label>
        <input type="password" class="form-control" name="password" placeholder="Password" required
               maxlength="255">
        <div class="invalid-feedback">Please enter your password.</div>
      </div>

      <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="form-check m-0">
          <input type="checkbox" class="form-check-input" id="remember" name="remember">
          <label class="form-check-label" for="remember">Remember me</label>
        </div>
        <a href="forgot-password.php" class="auth-link">Forgot password?</a>
      </div>

      <button type="submit" name="login" class="btn btn-brown w-100">Login</button>
    </form>

    <script>
    // Client-side form validation for login
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('loginForm');
      
      // Form submission validation
      form.addEventListener('submit', function(e) {
        if(!form.checkValidity()) {
          e.preventDefault();
          e.stopPropagation();
        }
        form.classList.add('was-validated');
      });
      
      // Real-time validation feedback
      const inputs = form.querySelectorAll('input[required]');
      inputs.forEach(input => {
        input.addEventListener('blur', function() {
          if(!this.checkValidity()) {
            this.classList.add('is-invalid');
          } else {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
          }
        });
        
        input.addEventListener('input', function() {
          if(this.classList.contains('is-invalid') && this.checkValidity()) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
          }
        });
      });
    });
    </script>

    <?php if($googleLoginUrl): ?>
    <div class="text-center my-3">
      <div class="d-flex align-items-center mb-3">
        <hr class="flex-grow-1">
        <span class="mx-2 text-muted">OR</span>
        <hr class="flex-grow-1">
      </div>
      <a href="<?php echo htmlspecialchars($googleLoginUrl); ?>" class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center">
        <svg width="18" height="18" viewBox="0 0 18 18" class="me-2">
          <path fill="#4285F4" d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.874 2.684-6.615z"/>
          <path fill="#34A853" d="M9 18c2.43 0 4.467-.806 5.96-2.184l-2.908-2.258c-.806.54-1.837.86-3.052.86-2.347 0-4.33-1.584-5.036-3.711H.957v2.332C2.438 15.983 5.482 18 9 18z"/>
          <path fill="#FBBC05" d="M3.964 10.707c-.18-.54-.282-1.117-.282-1.707s.102-1.167.282-1.707V4.951H.957C.348 6.173 0 7.55 0 9s.348 2.827.957 4.049l3.007-2.342z"/>
          <path fill="#EA4335" d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0 5.482 0 2.438 2.017.957 4.951L3.964 7.293C4.67 5.163 6.653 3.58 9 3.58z"/>
        </svg>
        Sign in with Google
      </a>
    </div>
    <?php endif; ?>

    <div class="auth-footer text-center mt-3">
      <div>Don't have an account? <a class="auth-link" href="signup.php">Sign Up</a></div>
      <a class="auth-link d-inline-block mt-2" href="index.php"><i class="fas fa-arrow-left me-1"></i>Back to Home</a>
    </div>
  </div>
</div>

<?php include("includes/login-footer.php"); ?>
