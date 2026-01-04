<?php
require_once __DIR__ . '/includes/bootstrap.php';

$error = '';
$success = '';
$validToken = false;
$token = isset($_GET['token']) ? $_GET['token'] : '';

// Verify token
if (!empty($token)) {
    $query = "SELECT userID FROM users WHERE reset_token = ? AND reset_token_expires > NOW()";
    $result = executePreparedQuery($query, "s", [$token]);
    
    if ($result && $user = $result->fetch_assoc()) {
        $validToken = true;
        $userID = $user['userID'];
        
        // Process form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
            $password = $_POST['password'];
            $confirmPassword = $_POST['confirm_password'];
            
            // Validate password
            if (empty($password) || empty($confirmPassword)) {
                $error = 'Please fill in all fields.';
            } elseif ($password !== $confirmPassword) {
                $error = 'Passwords do not match.';
            } elseif (strlen($password) < 8) {
                $error = 'Password must be at least 8 characters long.';
            } else {
                // Hash the new password
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                
                // Update password and clear reset token
                $updateQuery = "UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL, 
                               failed_login_attempts = 0 WHERE userID = ?";
                
                if (executePreparedUpdate($updateQuery, "si", [$hashedPassword, $userID])) {
                    $success = 'Your password has been reset successfully. You can now <a href="login.php">login</a> with your new password.';
                    $validToken = false; // Hide the form after successful reset
                } else {
                    $error = 'Failed to reset password. Please try again.';
                }
            }
        }
    } else {
        $error = 'Invalid or expired reset link. Please request a new one.';
    }
} else {
    $error = 'No reset token provided.';
}

include(__DIR__ . "/includes/header.php");
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow rounded-4 p-5">
                <h2 class="mb-4">Reset Your Password</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if ($validToken): ?>
                    <p>Please enter your new password below.</p>
                    
                    <form method="post" class="mt-4">
                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="password" name="password" required 
                                   minlength="8" placeholder="At least 8 characters">
                            <div class="form-text">Password must be at least 8 characters long.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" 
                                   name="confirm_password" required minlength="8" placeholder="Re-enter your password">
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-warning">Reset Password</button>
                        </div>
                    </form>
                <?php endif; ?>
                
                <div class="mt-4 text-center">
                    <p>Remember your password? <a href="login.php">Back to Login</a></p>
                    <p>Need a new reset link? <a href="forgot-password.php">Request another</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include(__DIR__ . "/includes/footer.php"); ?>
