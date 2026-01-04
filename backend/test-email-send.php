<?php
require_once __DIR__ . '/includes/bootstrap.php';

// Test sending an actual email
if ($_POST['send']) {
    $testEmail = $_POST['email'] ?? 'test@example.com';
    
    try {
        require_once __DIR__ . '/../vendor/autoload.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'karneekbakery@gmail.com';
        $mail->Password = ''; // This needs to be set in config
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        
        // Recipients
        $mail->setFrom('karneekbakery@gmail.com', 'KARNEEK Bakery');
        $mail->addAddress($testEmail);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Test Email from KARNEEK Bakery';
        
        $mail->Body = '
        <html>
        <body style="font-family: Arial, sans-serif;">
            <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                <h2 style="color: #8B4513;">KARNEEK Bakery - Test Email</h2>
                <p>This is a test email to verify Gmail SMTP integration is working.</p>
                <p>If you receive this, the email system is functioning correctly!</p>
                <hr>
                <p style="font-size: 12px; color: #777;">
                    Sent from: ' . date('Y-m-d H:i:s') . '<br>
                    IP: ' . $_SERVER['REMOTE_ADDR'] . '
                </p>
            </div>
        </body>
        </html>';
        
        $mail->AltBody = 'This is a test email from KARNEEK Bakery to verify Gmail SMTP integration.';
        
        $mail->send();
        
        echo "<div style='color: green; padding: 10px; border: 1px solid green; margin: 10px;'>";
        echo "✓ Test email sent successfully to: " . htmlspecialchars($testEmail);
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<div style='color: red; padding: 10px; border: 1px solid red; margin: 10px;'>";
        echo "✗ Email sending failed: " . htmlspecialchars($mail->ErrorInfo);
        echo "</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Test Email Sending</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-group { margin: 15px 0; }
        label { display: block; margin-bottom: 5px; }
        input[type="email"] { padding: 8px; width: 300px; }
        button { padding: 10px 20px; background: #8B4513; color: white; border: none; cursor: pointer; }
        button:hover { background: #6B3410; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; margin: 10px 0; }
    </style>
</head>
<body>
    <h2>Test Email Sending</h2>
    
    <div class="warning">
        <strong>Note:</strong> This will test sending an actual email. Make sure the Gmail password is configured in config/email.php
    </div>
    
    <form method="post">
        <div class="form-group">
            <label for="email">Send test email to:</label>
            <input type="email" id="email" name="email" value="test@example.com" required>
        </div>
        <button type="submit" name="send">Send Test Email</button>
    </form>
    
    <p><a href="test-email-config.php">← Back to Email Configuration Test</a></p>
</body>
</html>
