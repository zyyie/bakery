<?php
$__autoload = __DIR__ . '/../../vendor/autoload.php';
if (file_exists($__autoload)) {
    require_once $__autoload;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Email {
    private $mailer;
    private $config;
    private $enabled = true;

    public function __construct() {
        $this->config = require __DIR__ . '/../config/email.php';
        // If PHPMailer is not available (composer not installed), disable gracefully
        if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
            $this->enabled = false;
            error_log('Email: PHPMailer not installed. Run "composer install" to enable email sending.');
            return;
        }
        $this->mailer = new PHPMailer(true);
        $this->configureMailer();
    }

    private function configureMailer() {
        if (!$this->enabled) { return; }
        // Server settings
        $this->mailer->isSMTP();
        $this->mailer->Host = $this->config['host'];
        $this->mailer->SMTPAuth = $this->config['smtp_auth'];
        $this->mailer->Username = $this->config['username'];
        $this->mailer->Password = $this->config['password'];
        $this->mailer->SMTPSecure = $this->config['smtp_secure'];
        $this->mailer->Port = $this->config['port'];
        $this->mailer->CharSet = $this->config['charset'];
        
        // Additional Gmail settings for better reliability
        $this->mailer->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Enable keep-alive
        $this->mailer->SMTPKeepAlive = false;
        
        // Timeout settings
        $this->mailer->Timeout = 30;
        
        // Debugging
        $this->mailer->SMTPDebug = $this->config['debug'];
        $this->mailer->Debugoutput = function($str, $level) {
            error_log("PHPMailer [Level $level]: $str");
        };
    }

    public function sendContactMessage($fromName, $fromEmail, $mobileNumber, $userMessage) {
        if (!$this->enabled) { return false; }
        try {
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();

            $this->mailer->setFrom($this->config['from_email'], $this->config['from_name']);
            // send to the shop inbox configured in email.php
            $this->mailer->addAddress($this->config['from_email'], $this->config['from_name']);

            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'New Customer Message';

            $safeName = htmlspecialchars($fromName, ENT_QUOTES, 'UTF-8');
            $safeEmail = htmlspecialchars($fromEmail, ENT_QUOTES, 'UTF-8');
            $safeMobile = htmlspecialchars($mobileNumber, ENT_QUOTES, 'UTF-8');
            $safeMsg = nl2br(htmlspecialchars($userMessage, ENT_QUOTES, 'UTF-8'));

            $body = "<div style='font-family:Arial,sans-serif;font-size:14px;color:#333'>"
                  . "<h3 style='margin-top:0'>New Customer Message</h3>"
                  . "<p><strong>Name:</strong> {$safeName}</p>"
                  . "<p><strong>Email:</strong> {$safeEmail}</p>"
                  . "<p><strong>Mobile:</strong> {$safeMobile}</p>"
                  . "<p><strong>Message:</strong><br>{$safeMsg}</p>"
                  . "</div>";

            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $body));

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Contact email could not be sent. Mailer Error: {$this->mailer->ErrorInfo}");
            return false;
        }
    }

    public function sendPasswordReset($toEmail, $toName, $resetLink) {
        if (!$this->enabled) { 
            error_log("Email sending disabled - PHPMailer not installed");
            return false; 
        }
        try {
            // Clear previous recipients
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            
            // Recipients
            $this->mailer->setFrom($this->config['from_email'], $this->config['from_name']);
            $this->mailer->addAddress($toEmail, $toName);
            
            // Content
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Password Reset Request - KARNEEK Bakery';
            
            $message = $this->getPasswordResetTemplate($toName, $resetLink);
            $this->mailer->Body = $message;
            $this->mailer->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $message));

            $result = $this->mailer->send();
            if ($result) {
                error_log("Password reset email sent successfully to: $toEmail");
            } else {
                error_log("Failed to send password reset email. Error: {$this->mailer->ErrorInfo}");
            }
            return $result;
        } catch (Exception $e) {
            error_log("Exception sending password reset email to $toEmail: " . $e->getMessage());
            error_log("PHPMailer Error Info: {$this->mailer->ErrorInfo}");
            return false;
        }
    }

    public function sendPasswordResetCode($toEmail, $toName, $resetCode) {
        if (!$this->enabled) { 
            error_log("Email sending disabled - PHPMailer not installed");
            return false; 
        }
        try {
            // Clear previous recipients
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            
            // Recipients
            $this->mailer->setFrom($this->config['from_email'], $this->config['from_name']);
            $this->mailer->addAddress($toEmail, $toName);
            
            // Content
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Password Reset Code - KARNEEK Bakery';
            
            $message = $this->getPasswordResetCodeTemplate($toName, $resetCode);
            $this->mailer->Body = $message;
            $this->mailer->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $message));

            // Enable verbose error reporting
            $result = $this->mailer->send();
            
            if ($result) {
                error_log("Password reset code sent successfully to: $toEmail");
                error_log("PHPMailer SMTP Server: {$this->mailer->Host}:{$this->mailer->Port}");
                error_log("PHPMailer SMTP Secure: {$this->mailer->SMTPSecure}");
            } else {
                error_log("Failed to send password reset code to: $toEmail");
                error_log("PHPMailer Error Info: {$this->mailer->ErrorInfo}");
            }
            return $result;
        } catch (Exception $e) {
            error_log("Exception sending password reset code to $toEmail: " . $e->getMessage());
            error_log("PHPMailer Error Info: {$this->mailer->ErrorInfo}");
            error_log("Exception Trace: " . $e->getTraceAsString());
            return false;
        }
    }

    public function sendNewsletterWelcome($toEmail) {
        if (!$this->enabled) { return false; }
        try {
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();

            $this->mailer->setFrom($this->config['from_email'], $this->config['from_name']);
            $this->mailer->addAddress($toEmail);

            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Welcome to KARNEEK Bakery Newsletter';

            $message = $this->getNewsletterWelcomeTemplate($toEmail);
            $this->mailer->Body = $message;
            $this->mailer->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $message));

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Newsletter welcome email could not be sent. Mailer Error: {$this->mailer->ErrorInfo}");
            return false;
        }
    }

    private function getPasswordResetTemplate($name, $resetLink) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #f8f9fa; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .button {
                    display: inline-block;
                    padding: 10px 20px;
                    background-color: #8B4513;
                    color: #fff !important;
                    text-decoration: none;
                    border-radius: 5px;
                    margin: 20px 0;
                }
                .footer { 
                    margin-top: 20px; 
                    padding-top: 20px;
                    border-top: 1px solid #eee;
                    font-size: 12px;
                    color: #777;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Bakery Shop</h2>
                </div>
                <div class='content'>
                    <p>Hello $name,</p>
                    <p>We received a request to reset your password. Click the button below to set a new password:</p>
                    <p style='text-align: center;'>
                        <a href='$resetLink' class='button'>Reset Password</a>
                    </p>
                    <p>Or copy and paste this link into your browser:</p>
                    <p><a href='$resetLink'>$resetLink</a></p>
                    <p>This link will expire in 1 hour.</p>
                    <p>If you didn't request a password reset, you can safely ignore this email.</p>
                </div>
                <div class='footer'>
                    <p>This is an automated message, please do not reply directly to this email.</p>
                    <p>&copy; " . date('Y') . " Bakery Shop. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    private function getPasswordResetCodeTemplate($name, $resetCode) {
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #f8f9fa; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .code-box {
                    background-color: #f8f9fa;
                    border: 2px solid #8B4513;
                    border-radius: 8px;
                    padding: 20px;
                    text-align: center;
                    margin: 20px 0;
                    font-size: 32px;
                    font-weight: bold;
                    letter-spacing: 8px;
                    color: #8B4513;
                    font-family: 'Courier New', monospace;
                }
                .button {
                    display: inline-block;
                    padding: 12px 24px;
                    background-color: #8B4513;
                    color: #fff !important;
                    text-decoration: none;
                    border-radius: 5px;
                    margin: 20px 0;
                }
                .footer { 
                    margin-top: 20px; 
                    padding-top: 20px;
                    border-top: 1px solid #eee;
                    font-size: 12px;
                    color: #777;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>KARNEEK Bakery</h2>
                </div>
                <div class='content'>
                    <p>Hello $name,</p>
                    <p>We received a request to reset your password. Use the code below to reset your password:</p>
                    <div class='code-box'>$resetCode</div>
                    <p><strong>Steps to reset your password:</strong></p>
                    <ol>
                        <li>Go to the password reset page</li>
                        <li>Enter the code above: <strong>$resetCode</strong></li>
                        <li>Set your new password</li>
                    </ol>
                    <p><strong>This code will expire in 15 minutes.</strong></p>
                    <p>If you didn't request a password reset, you can safely ignore this email.</p>
                </div>
                <div class='footer'>
                    <p>This is an automated message, please do not reply directly to this email.</p>
                    <p>&copy; " . date('Y') . " KARNEEK Bakery. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    private function getNewsletterWelcomeTemplate($email) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #f8f9fa; padding: 20px; text-align: center; }
                .title { color: #8B4513; margin: 0; }
                .content { padding: 20px; }
                .footer { 
                    margin-top: 20px; 
                    padding-top: 20px;
                    border-top: 1px solid #eee;
                    font-size: 12px;
                    color: #777;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2 class='title'>KARNEEK Bakery</h2>
                </div>
                <div class='content'>
                    <p>Hi!</p>
                    <p>Thanks for subscribing to our newsletter using <strong>$email</strong>.</p>
                    <p>You’ll now receive updates about our latest baked goods and special offers.</p>
                </div>
                <div class='footer'>
                    <p>This is an automated message, please do not reply directly to this email.</p>
                    <p>&copy; " . date('Y') . " KARNEEK Bakery. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";
    }
}
