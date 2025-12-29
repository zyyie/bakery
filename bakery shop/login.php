<?php
require_once __DIR__ . '/includes/bootstrap.php';

$error = "";

// Check if user is already logged in
if(isset($_SESSION['userID'])) {
    header("Location: index.php");
    exit();
}

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
<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card shadow rounded-4 p-5">
        <h2 class="mb-4">Login to your account</h2>
        
        <?php if($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
          <div class="mb-3">
            <label class="form-label">Email*</label>
            <input type="email" class="form-control" name="email" placeholder="Enter Email" required 
                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Password*</label>
            <input type="password" class="form-control" name="password" placeholder="Password" required>
          </div>
          <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="remember" name="remember">
            <label class="form-check-label" for="remember">Remember me</label>
          </div>
          <div class="mb-3">
            <a href="forgot-password.php" class="text-primary">Forgot password?</a>
          </div>
          <button type="submit" name="login" class="btn btn-warning w-100">Login</button>
        </form>
        
        <div class="mt-3 text-center">
          <p>Don't have an account? <a href="signup.php">Sign Up</a></p>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include("includes/login-footer.php"); ?>
