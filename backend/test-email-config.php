<?php
require_once __DIR__ . '/includes/bootstrap.php';

// Test email configuration
$config = require __DIR__ . '/config/email.php';

echo "<h2>Email Configuration Test</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Setting</th><th>Value</th></tr>";
echo "<tr><td>Host</td><td>" . htmlspecialchars($config['host']) . "</td></tr>";
echo "<tr><td>Username</td><td>" . htmlspecialchars($config['username']) . "</td></tr>";
echo "<tr><td>Password</td><td>" . (empty($config['password']) ? '<span style="color:red">NOT SET</span>' : '<span style="color:green">SET</span>') . "</td></tr>";
echo "<tr><td>SMTP Secure</td><td>" . htmlspecialchars($config['smtp_secure']) . "</td></tr>";
echo "<tr><td>Port</td><td>" . htmlspecialchars($config['port']) . "</td></tr>";
echo "<tr><td>From Email</td><td>" . htmlspecialchars($config['from_email']) . "</td></tr>";
echo "<tr><td>From Name</td><td>" . htmlspecialchars($config['from_name']) . "</td></tr>";
echo "</table>";

// Check if PHPMailer is installed
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    echo "<p style='color:green;'>✓ PHPMailer is installed</p>";
} else {
    echo "<p style='color:red;'>✗ PHPMailer is NOT installed. Run: composer install</p>";
}

// Test connection if password is set
if (!empty($config['password'])) {
    echo "<h3>Testing SMTP Connection...</h3>";
    try {
        require_once __DIR__ . '/../vendor/autoload.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = $config['smtp_auth'];
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        $mail->SMTPSecure = $config['smtp_secure'];
        $mail->Port = $config['port'];
        $mail->SMTPDebug = PHPMailer\PHPMailer\SMTP::DEBUG_OFF;
        
        // Test connection
        if ($mail->smtpConnect()) {
            echo "<p style='color:green;'>✓ SMTP connection successful!</p>";
            $mail->smtpClose();
        } else {
            echo "<p style='color:red;'>✗ SMTP connection failed</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color:red;'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p style='color:red;'>Cannot test connection: Gmail password not set</p>";
    echo "<p>To set up Gmail SMTP:</p>";
    echo "<ol>";
    echo "<li>Enable 2-factor authentication on your Gmail account</li>";
    echo "<li>Generate an App Password: https://myaccount.google.com/apppasswords</li>";
    echo "<li>Use the 16-character App Password in the config file</li>";
    echo "</ol>";
}
?>
