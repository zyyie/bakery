<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/Email.php';

$message = '';
$error = '';
$showEmailForm = true;
$showCodeForm = false;
$showPasswordForm = false;
$userID = null;
$emailSent = false;

// Step 1: Request reset code (email submission)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && !isset($_POST['reset_code']) && !isset($_POST['password'])) {
    $email = trim($_POST['email']);
    
    if (empty($email)) {
        $error = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Check if email exists
        $query = "SELECT userID, fullName, email FROM users WHERE email = ?";
        $result = executePreparedQuery($query, "s", [$email]);
        
        if ($result && $user = $result->fetch_assoc()) {
            // Generate 6-digit reset code (valid for 15 minutes)
            $resetCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            
            // Store code in database (using reset_token column)
            $updateQuery = "UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE userID = ?";
            if (executePreparedUpdate($updateQuery, "ssi", [$resetCode, $expires, $user['userID']])) {
                // Send email with code
                try {
                $emailSender = new Email();
                    if ($emailSender->sendPasswordResetCode($user['email'], $user['fullName'], $resetCode)) {
                        $message = 'A 6-digit password reset code has been sent to your email. Please check your inbox and spam folder.';
                        $showEmailForm = false;
                        $showCodeForm = true;
                        $emailSent = true;
                } else {
                    $error = 'Failed to send email. Please try again later.';
                    }
                } catch (Exception $e) {
                    $error = 'Error sending email. Please try again later.';
                }
            } else {
                $error = 'Failed to process your request. Please try again later.';
            }
        } else {
            // Don't reveal if email exists
            $message = 'If your email exists in our system, you will receive a password reset code.';
            $showEmailForm = false;
            $showCodeForm = true;
        }
    }
}

// Step 2: Verify reset code
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_code']) && !isset($_POST['password'])) {
    $resetCode = trim($_POST['reset_code']);
    
    if (empty($resetCode)) {
        $error = 'Please enter the reset code.';
        $showEmailForm = false;
        $showCodeForm = true;
    } elseif (!preg_match('/^\d{6}$/', $resetCode)) {
        $error = 'Please enter a valid 6-digit code.';
        $showEmailForm = false;
        $showCodeForm = true;
    } else {
        // Find user with this code
        $query = "SELECT userID, reset_token_expires FROM users WHERE reset_token = ?";
        $result = executePreparedQuery($query, "s", [$resetCode]);
        
        if ($result && $user = $result->fetch_assoc()) {
            // Check if code is still valid (not expired)
            $expires = strtotime($user['reset_token_expires']);
            if ($expires > time()) {
                $showEmailForm = false;
                $showCodeForm = false;
                $showPasswordForm = true;
                $userID = $user['userID'];
                $message = 'Code verified! Please enter your new password.';
            } else {
                $error = 'This reset code has expired. Please request a new one.';
                $showEmailForm = true;
                $showCodeForm = false;
            }
        } else {
            $error = 'Invalid reset code. Please check and try again.';
            $showEmailForm = false;
            $showCodeForm = true;
        }
    }
}

// Step 3: Process password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password']) && isset($_POST['user_id'])) {
    $userID = (int)$_POST['user_id'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Validate
    if (empty($password) || empty($confirmPassword)) {
        $error = 'Please fill in all fields.';
        $showEmailForm = false;
        $showCodeForm = false;
        $showPasswordForm = true;
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
        $showEmailForm = false;
        $showCodeForm = false;
        $showPasswordForm = true;
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
        $showEmailForm = false;
        $showCodeForm = false;
        $showPasswordForm = true;
    } else {
        // Update password and clear reset code
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $updateQuery = "UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL, failed_login_attempts = 0 WHERE userID = ?";
        
        if (executePreparedUpdate($updateQuery, "si", [$hashedPassword, $userID])) {
            $message = 'Your password has been reset successfully. You can now <a href="login.php">login</a> with your new password.';
            $showEmailForm = false;
            $showCodeForm = false;
            $showPasswordForm = false;
        } else {
            $error = 'Failed to reset password. Please try again.';
            $showEmailForm = false;
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
                
                <?php if ($message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i><?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Step 1: Email Form -->
                <?php if ($showEmailForm): ?>
                    <p class="mb-3">Enter your email address and we'll send you a 6-digit code to reset your password.</p>
                    
                    <form method="post" class="mt-4">
                        <div class="mb-3">
                            <label for="email" class="form-label fw-bold">Email Address</label>
                            <input type="email" class="form-control form-control-lg" id="email" name="email" required 
                                   placeholder="Enter your email address"
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                   autocomplete="email">
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-brown btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>Send Reset Code
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
                
                <!-- Step 2: Code Verification Form -->
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
                    
                    <div class="mt-3 text-center">
                        <a href="forgot-password.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Email
                        </a>
                    </div>
                    
                    <script>
                    // Auto-format code input
                    document.getElementById('reset_code').addEventListener('input', function(e) {
                        this.value = this.value.replace(/\D/g, '').slice(0, 6);
                    });
                    </script>
                <?php endif; ?>
                
                <!-- Step 3: Password Reset Form -->
                <?php if ($showPasswordForm): ?>
                    <div class="mb-3">
                        <a href="forgot-password.php" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-2"></i>Back to Code Entry
                        </a>
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
                </div>
            </div>
        </div>
    </div>
</div>

<?php include(__DIR__ . "/../../includes/footer.php"); ?>
