<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
requireAdminLogin();
adminRegenerateSession();

// Define e() function if not available
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

$success = "";
$error = "";

if(isset($_POST['sendReply'])){
    $enquiryID = intval($_POST['enquiryID']);
    $replyMessage = trim($_POST['replyMessage']);
    
    if(empty($replyMessage)){
        $error = "Reply message is required!";
    } else {
        // Get enquiry details
        $query = "SELECT * FROM enquiries WHERE enquiryID = ?";
        $result = executePreparedQuery($query, "i", [$enquiryID]);
        $enquiry = mysqli_fetch_assoc($result);
        
        if($enquiry) {
            // Send email reply
            try {
                // Use PHPMailer directly instead of Email class
                require_once __DIR__ . '/../../vendor/autoload.php';
                
                // Create reply email content
                $replyContent = "
                <html>
                <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                    <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                        <div class='header' style='background-color: #f8f9fa; padding: 20px; text-align: center;'>
                            <h2 style='color: #8B4513; margin: 0;'>KARNEEK Bakery</h2>
                        </div>
                        <div class='content' style='padding: 20px;'>
                            <p>Dear " . e($enquiry['name']) . ",</p>
                            <p>Thank you for your inquiry. Here is our response:</p>
                            <div style='background-color: #f8f9fa; padding: 15px; border-left: 4px solid #8B4513; margin: 15px 0;'>
                                " . nl2br(e($replyMessage)) . "
                            </div>
                            <p>If you have any further questions, please don't hesitate to contact us.</p>
                            <p>Best regards,<br>KARNEEK Bakery Team</p>
                        </div>
                        <div class='footer' style='margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; font-size: 12px; color: #777;'>
                            <p>This is a response to your message sent on " . date('F d, Y', strtotime($enquiry['enquiryDate'])) . ":</p>
                            <div style='background-color: #f8f9fa; padding: 10px; font-style: italic;'>
                                " . nl2br(e($enquiry['message'])) . "
                            </div>
                        </div>
                    </div>
                </body>
                </html>";
                
                $subject = "Re: Your Inquiry - KARNEEK Bakery";
                
                // Send email using PHPMailer
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'karneekbakery@gmail.com';
                $mail->Password = 'hoqg spvx xzue xkub';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;
                $mail->CharSet = 'UTF-8';
                
                $mail->setFrom('karneekbakery@gmail.com', 'KARNEEK Bakery');
                $mail->addAddress($enquiry['email'], $enquiry['name']);
                $mail->addReplyTo('karneekbakery@gmail.com', 'KARNEEK Bakery');
                
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = $replyContent;
                $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $replyContent));
                
                $mail->send();
                
                // Mark enquiry as replied
                $updateQuery = "UPDATE enquiries SET status = 'Replied', replyMessage = ?, replyDate = NOW() WHERE enquiryID = ?";
                executePreparedUpdate($updateQuery, "si", [$replyMessage, $enquiryID]);
                
                $success = "Reply sent successfully to " . e($enquiry['email']);
                
            } catch (Exception $e) {
                $error = "Failed to send reply: " . $e->getMessage();
            }
        } else {
            $error = "Enquiry not found!";
        }
    }
}

// Get enquiry details
$enquiryID = isset($_GET['id']) ? intval($_GET['id']) : 0;
if($enquiryID <= 0) {
    header("Location: read-enquiry.php");
    exit();
}

$query = "SELECT * FROM enquiries WHERE enquiryID = ?";
$result = executePreparedQuery($query, "i", [$enquiryID]);
$enquiry = mysqli_fetch_assoc($result);

if(!$enquiry) {
    header("Location: read-enquiry.php");
    exit();
}

include(dirname(__DIR__) . "/includes/header.php");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Reply to Customer Message</h2>
    <a href="read-enquiry.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Messages
    </a>
</div>

<?php if($success): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if($error): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Original Message</h5>
    </div>
    <div class="card-body">
        <?php if($enquiry['email']): ?>
        <div class="row mb-3">
            <div class="col-md-6">
                <strong>Email:</strong> <?php echo e($enquiry['email']); ?>
            </div>
        </div>
        <?php endif; ?>
        <?php if($enquiry['mobileNumber']): ?>
        <div class="row mb-3">
            <div class="col-md-6">
                <strong>Mobile Number:</strong> <?php echo e($enquiry['mobileNumber']); ?>
            </div>
        </div>
        <?php endif; ?>
        <div class="mb-3">
            <strong>Message:</strong>
            <div class="mt-2 p-3 bg-light rounded">
                <?php echo nl2br(e($enquiry['message'])); ?>
            </div>
        </div>
        <?php if($enquiry['replyMessage']): ?>
        <div class="mb-3">
            <strong>Previous Reply:</strong>
            <div class="mt-2 p-3 bg-success bg-opacity-10 rounded border border-success">
                <small class="text-muted">Sent on <?php echo date('M d, Y H:i', strtotime($enquiry['replyDate'])); ?></small><br>
                <?php echo nl2br(e($enquiry['replyMessage'])); ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">Send Reply</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="enquiryID" value="<?php echo $enquiryID; ?>">
            <div class="mb-3">
                <label class="form-label">Reply Message</label>
                <textarea class="form-control" name="replyMessage" rows="6" placeholder="Type your reply here..." required><?php echo isset($_POST['replyMessage']) ? e($_POST['replyMessage']) : ''; ?></textarea>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" name="sendReply" class="btn btn-primary">
                    <i class="fas fa-paper-plane me-2"></i>Send Reply
                </button>
                <a href="read-enquiry.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include(dirname(__DIR__) . "/includes/footer.php"); ?>
