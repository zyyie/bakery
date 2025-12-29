<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/Email.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
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
            // Generate reset token (valid for 1 hour)
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Store token in database
            $updateQuery = "UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE userID = ?";
            if (executePreparedUpdate($updateQuery, "ssi", [$token, $expires, $user['userID']])) {
                // Generate reset link
                $resetLink = current_base_url() . "/reset-password.php?token=" . $token;
                
                // Send email
                $emailSender = new Email();
                if ($emailSender->sendPasswordReset($user['email'], $user['fullName'], $resetLink)) {
                    $message = 'Password reset link has been sent to your email. Please check your inbox.';
                } else {
                    $error = 'Failed to send email. Please try again later.';
                    error_log("Failed to send password reset email to: " . $user['email']);
                }
            } else {
                $error = 'Failed to process your request. Please try again later.';
            }
        } else {
            // Don't reveal if the email exists or not
            $message = 'If your email exists in our system, you will receive a password reset link.';
        }
    }
}

include("includes/header.php");
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow rounded-4 p-5">
                <h2 class="mb-4">Forgot Password</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <?php if (empty($message) || $error): ?>
                    <p>Enter your email address and we'll send you a link to reset your password.</p>
                    
                    <form method="post" class="mt-4">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required 
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-warning">Send Reset Link</button>
                        </div>
                    </form>
                <?php endif; ?>
                
                <div class="mt-4 text-center">
                    <p>Remember your password? <a href="login.php">Back to Login</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("includes/footer.php"); ?>
