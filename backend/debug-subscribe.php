<?php
echo "<h2>Newsletter Subscription Debug</h2>";

// Check if form was submitted
if ($_POST) {
    echo "<h3>POST Data Received:</h3>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    if (isset($_POST['email'])) {
        $email = trim($_POST['email']);
        echo "<p>Email received: " . htmlspecialchars($email) . "</p>";
        
        // Validate email
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "<p style='color:green;'>✓ Email format is valid</p>";
            
            // Test database connection
            require_once __DIR__ . '/includes/bootstrap.php';
            
            $checkQuery = "SELECT * FROM subscribers WHERE email = ?";
            $checkResult = executePreparedQuery($checkQuery, "s", [$email]);
            
            if ($checkResult && mysqli_num_rows($checkResult) > 0) {
                echo "<p style='color:orange;'>⚠ Email already subscribed</p>";
            } else {
                echo "<p style='color:green;'>✓ Email not subscribed yet</p>";
                
                // Test insertion
                $query = "INSERT INTO subscribers (email) VALUES (?)";
                $inserted = executePreparedUpdate($query, "s", [$email]);
                
                if ($inserted !== false) {
                    echo "<p style='color:green;'>✓ Successfully added to database</p>";
                    
                    // Test email sending
                    try {
                        require_once __DIR__ . '/../vendor/autoload.php';
                        require_once __DIR__ . '/includes/Email.php';
                        
                        $mailer = new Email();
                        if ($mailer->sendNewsletterWelcome($email)) {
                            echo "<p style='color:green;'>✓ Welcome email sent successfully</p>";
                        } else {
                            echo "<p style='color:red;'>✗ Failed to send welcome email</p>";
                        }
                    } catch (Exception $e) {
                        echo "<p style='color:red;'>✗ Email error: " . htmlspecialchars($e->getMessage()) . "</p>";
                    }
                } else {
                    echo "<p style='color:red;'>✗ Failed to add to database</p>";
                }
            }
        } else {
            echo "<p style='color:red;'>✗ Invalid email format</p>";
        }
    }
} else {
    echo "<p>No POST data received</p>";
}

echo "<hr>";
echo "<h3>Current Directory:</h3>";
echo "<p>" . __DIR__ . "</p>";

echo "<h3>File Paths:</h3>";
echo "<p>subscribe.php: " . __FILE__ . "</p>";
echo "<p>bootstrap.php: " . __DIR__ . '/includes/bootstrap.php' . "</p>";
echo "<p>Exists: " . (file_exists(__DIR__ . '/includes/bootstrap.php') ? 'Yes' : 'No') . "</p>";
?>

<form method="POST" style="margin-top: 20px;">
    <h3>Test Subscription Form</h3>
    <input type="email" name="email" placeholder="Enter your email" required>
    <button type="submit">Subscribe</button>
</form>
