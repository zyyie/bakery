<?php
require_once __DIR__ . '/../../includes/bootstrap.php';

$error = '';
$success = '';
$showCodeForm = true;
$showPasswordForm = false;
$userID = null;

// Step 1: Verify reset code
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_code'])) {
    $resetCode = trim($_POST['reset_code']);
    
    if (empty($resetCode)) {
        $error = 'Please enter the reset code.';
    } elseif (!preg_match('/^\d{6}$/', $resetCode)) {
        $error = 'Please enter a valid 6-digit code.';
    } else {
        // Find user with this code
        $query = "SELECT userID, reset_token_expires FROM users WHERE reset_token = ?";
        $result = executePreparedQuery($query, "s", [$resetCode]);
        
        if ($result && $user = $result->fetch_assoc()) {
            // Check if code is still valid (not expired)
            $expires = strtotime($user['reset_token_expires']);
            if ($expires > time()) {
                $showCodeForm = false;
                $showPasswordForm = true;
                $userID = $user['userID'];
            } else {
                $error = 'This reset code has expired. Please <a href="forgot-password.php">request a new one</a>.';
            }
        } else {
            $error = 'Invalid reset code. Please check and try again.';
        }
    }
}

// Step 2: Process password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password']) && isset($_POST['user_id'])) {
    $userID = (int)$_POST['user_id'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Validate
    if (empty($password) || empty($confirmPassword)) {
        $error = 'Please fill in all fields.';
        $showCodeForm = false;
        $showPasswordForm = true;
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
        $showCodeForm = false;
        $showPasswordForm = true;
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
        $showCodeForm = false;
        $showPasswordForm = true;
    } else {
        // Update password and clear reset code
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $updateQuery = "UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL, failed_login_attempts = 0 WHERE userID = ?";
        
        if (executePreparedUpdate($updateQuery, "si", [$hashedPassword, $userID])) {
            $success = 'Your password has been reset successfully. You can now <a href="login.php">login</a> with your new password.';
            $showCodeForm = false;
            $showPasswordForm = false;
        } else {
            $error = 'Failed to reset password. Please try again.';
            $showCodeForm = false;
            $showPasswordForm = true;
        }
    }
}

include(__DIR__ . "/../../includes/header.php");
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow rounded-4 p-5">
                <div class="mb-3">
                    <a href="login.php" class="text-decoration-none">
                        <i class="fas fa-arrow-left me-2"></i>Back to Login
                    </a>
                </div>
                
                <h2 class="mb-4">Reset Your Password</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($showCodeForm): ?>
                    <p class="mb-3">Enter the 6-digit code sent to your email address.</p>
                    
                    <form method="post" class="mt-4">
                        <div class="mb-3">
                            <label for="reset_code" class="form-label fw-bold">Reset Code</label>
                            <input type="text" class="form-control form-control-lg text-center" id="reset_code" 
                                   name="reset_code" required maxlength="6" pattern="[0-9]{6}"
                                   placeholder="000000" style="font-size: 2rem; letter-spacing: 0.5rem; font-weight: bold;"
                                   autocomplete="off" inputmode="numeric">
                            <div class="form-text text-center">Enter the 6-digit code from your email</div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-brown btn-lg">
                                <i class="fas fa-check me-2"></i>Verify Code
                            </button>
                        </div>
                    </form>
                    
                    <script>
                    // Auto-format code input
                    document.getElementById('reset_code').addEventListener('input', function(e) {
                        this.value = this.value.replace(/\D/g, '').slice(0, 6);
                    });
                    </script>
                <?php endif; ?>
                
                <?php if ($showPasswordForm): ?>
                    <div class="mb-3">
                        <a href="reset-password.php" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-2"></i>Back to Code Entry
                        </a>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Code verified!</strong> Please enter your new password below.
                    </div>
                    
                    <form method="post" class="mt-4" id="resetPasswordForm">
                        <input type="hidden" name="user_id" value="<?php echo $userID; ?>">
                        
                        <div class="mb-3">
                            <label for="password" class="form-label fw-bold">New Password</label>
                            <input type="password" class="form-control form-control-lg" id="password" name="password" 
                                   required minlength="8" placeholder="Enter new password (min. 8 characters)" 
                                   autocomplete="new-password">
                            <div class="form-text">Password must be at least 8 characters long.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label fw-bold">Confirm New Password</label>
                            <input type="password" class="form-control form-control-lg" id="confirm_password" 
                                   name="confirm_password" required minlength="8" placeholder="Re-enter your new password" 
                                   autocomplete="new-password">
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-brown btn-lg">
                                <i class="fas fa-key me-2"></i>Reset Password
                            </button>
                        </div>
                    </form>
                    
                    <script>
                    document.getElementById('resetPasswordForm').addEventListener('submit', function(e) {
                        const password = document.getElementById('password').value;
                        const confirmPassword = document.getElementById('confirm_password').value;
                        
                        if (password !== confirmPassword) {
                            e.preventDefault();
                            alert('Passwords do not match. Please try again.');
                            return false;
                        }
                        
                        if (password.length < 8) {
                            e.preventDefault();
                            alert('Password must be at least 8 characters long.');
                            return false;
                        }
                    });
                    </script>
                <?php endif; ?>
                
                <div class="mt-4 text-center">
                    <p>Remember your password? <a href="login.php">Back to Login</a></p>
                    <p>Need a new reset code? <a href="forgot-password.php">Request another</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include(__DIR__ . "/../../includes/footer.php"); ?>
