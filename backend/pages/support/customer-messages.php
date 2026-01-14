<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
include(__DIR__ . "/../../includes/header.php");

// Check if user is logged in
if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$success = "";
$error = "";

if(isset($_POST['sendMessage'])){
  $userID = $_SESSION['userID'];
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $mobileNumber = trim($_POST['mobileNumber']);
  $message = trim($_POST['message']);
  
  // Email-only validation and send
  if (empty($name) || empty($email) || empty($message)) {
    $error = "Name, email, and message are required!";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Invalid email format!";
  } else {
    $query = "INSERT INTO enquiries (userID, name, email, mobileNumber, message, enquiryDate, status) 
              VALUES (?, ?, ?, ?, ?, NOW(), 'Unread')";
    $result = executePreparedUpdate($query, "issss", [$userID, $name, $email, $mobileNumber, $message]);
    if($result !== false){
      require_once __DIR__ . '/../../includes/Email.php';
      $mailer = new Email();
      $mailer->sendContactMessage($name, $email, $mobileNumber, $message);
      $success = "Your inquiry has been submitted successfully! We'll get back to you soon.";
    } else {
      $error = "Failed to submit enquiry!";
    }
  }
}

// Get user's previous messages
$userID = $_SESSION['userID'];
$query = "SELECT * FROM enquiries WHERE userID = ? ORDER BY enquiryDate DESC";
$result = executePreparedQuery($query, "i", [$userID]);
?>

<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card shadow">
        <div class="card-header bg-brown text-white">
          <h4 class="mb-0"><i class="fas fa-envelope me-2"></i>Customer Messages</h4>
        </div>
        <div class="card-body p-4">
          
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

          <!-- Send New Message Form -->
          <div class="mb-5">
            <h5 class="text-brown mb-3">Send a New Message</h5>
            <form method="POST">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Your Name</label>
                  <input type="text" class="form-control" name="name" value="<?php echo e($_SESSION['fullName']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Email Address</label>
                  <input type="email" class="form-control" name="email" value="<?php echo e($_SESSION['email']); ?>" required>
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label">Mobile Number</label>
                <input type="tel" class="form-control" name="mobileNumber" placeholder="Your phone number">
              </div>
              <div class="mb-3">
                <label class="form-label">Message</label>
                <textarea class="form-control" name="message" rows="4" placeholder="Type your message here..." required></textarea>
              </div>
              <button type="submit" name="sendMessage" class="btn btn-brown">
                <i class="fas fa-paper-plane me-2"></i>Send Message
              </button>
            </form>
          </div>

          <!-- Previous Messages -->
          <div>
            <h5 class="text-brown mb-3">Your Previous Messages</h5>
            <?php if($result && mysqli_num_rows($result) > 0): ?>
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead class="table-light">
                    <tr>
                      <th>Date</th>
                      <th>Message</th>
                      <th>Status</th>
                      <th>Admin Reply</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                      <td>
                        <small class="text-muted"><?php echo date('M d, Y H:i', strtotime($row['enquiryDate'])); ?></small>
                      </td>
                      <td>
                        <div class="message-preview">
                          <?php echo e(substr($row['message'], 0, 100)); ?>
                          <?php echo strlen($row['message']) > 100 ? '...' : ''; ?>
                        </div>
                      </td>
                      <td>
                        <span class="badge bg-<?php 
                          echo $row['status'] == 'Read' ? 'success' : 
                               ($row['status'] == 'Replied' ? 'info' : 'warning'); 
                        ?>">
                          <?php echo $row['status']; ?>
                        </span>
                      </td>
                      <td>
                        <?php if($row['replyMessage']): ?>
                          <button class="btn btn-sm btn-outline-info" onclick="toggleReply(<?php echo $row['enquiryID']; ?>)">
                            <i class="fas fa-eye me-1"></i>View Reply
                          </button>
                          <div id="reply-<?php echo $row['enquiryID']; ?>" style="display: none; margin-top: 10px; padding: 10px; background: #e7f3ff; border-radius: 4px; border-left: 4px solid #17a2b8;">
                            <small class="text-muted">Admin replied on <?php echo date('M d, Y H:i', strtotime($row['replyDate'])); ?></small><br>
                            <?php echo nl2br(e($row['replyMessage'])); ?>
                          </div>
                        <?php else: ?>
                          <span class="text-muted">No reply yet</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                    <?php endwhile; ?>
                  </tbody>
                </table>
              </div>
            <?php else: ?>
              <div class="text-center py-4">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted">You haven't sent any messages yet.</p>
              </div>
            <?php endif; ?>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<?php include(__DIR__ . "/../../includes/footer.php"); ?>

<script>
function toggleReply(enquiryID) {
  const replyDiv = document.getElementById('reply-' + enquiryID);
  if (replyDiv.style.display === 'none') {
    replyDiv.style.display = 'block';
  } else {
    replyDiv.style.display = 'none';
  }
}
</script>
