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

include(dirname(__DIR__) . "/includes/header.php");

if(isset($_POST['markRead'])){
  $enquiryID = intval($_POST['enquiryID']);
  if($enquiryID > 0){
    $query = "UPDATE enquiries SET status = 'Read' WHERE enquiryID = ?";
    executePreparedUpdate($query, "i", [$enquiryID]);
  }
  header("Location: read-enquiry.php");
  exit();
}

$query = "SELECT e.*, u.fullName as userName FROM enquiries e LEFT JOIN users u ON e.userID = u.userID ORDER BY e.enquiryDate DESC";
$result = executePreparedQuery($query, "", []);
?>

<h2 class="mb-4">Customer Messages</h2>

<div class="card">
  <div class="card-body">
    <table class="table table-striped">
      <thead>
        <tr>
          <th>#</th>
          <th>Name</th>
          <th>Email</th>
          <th>Mobile Number</th>
          <th>Message</th>
          <th>Message Date</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $count = 1;
        while($row = mysqli_fetch_assoc($result)):
        ?>
        <tr>
          <td><?php echo $count++; ?></td>
          <td>
            <?php echo $row['userName'] ? e($row['userName']) : e($row['name']); ?>
            <?php if($row['userName']): ?>
            <small class="text-muted">(Registered User)</small>
            <?php endif; ?>
          </td>
          <td><?php echo $row['email']; ?></td>
          <td><?php echo $row['mobileNumber']; ?></td>
          <td>
            <div class="message-preview" style="max-width: 300px; cursor: pointer;" onclick="toggleMessage(this)">
              <?php echo e(substr($row['message'], 0, 50)); ?>
              <?php echo strlen($row['message']) > 50 ? '...' : ''; ?>
              <div class="full-message" style="display: none; margin-top: 5px; padding: 10px; background: #f8f9fa; border-radius: 4px;">
                <?php echo e($row['message']); ?>
              </div>
            </div>
          </td>
          <td><?php echo $row['enquiryDate']; ?></td>
          <td>
            <span class="badge bg-<?php 
              echo $row['status'] == 'Read' ? 'success' : 
                   ($row['status'] == 'Replied' ? 'info' : 'warning'); 
            ?>">
              <?php echo $row['status']; ?>
            </span>
            <?php if($row['status'] == 'Unread'): ?>
            <form method="POST" class="d-inline">
              <input type="hidden" name="enquiryID" value="<?php echo $row['enquiryID']; ?>">
              <button type="submit" name="markRead" class="btn btn-sm btn-primary">Mark as Read</button>
            </form>
            <?php endif; ?>
            <a href="reply-enquiry.php?id=<?php echo $row['enquiryID']; ?>" class="btn btn-sm btn-info ms-2">
              <i class="fas fa-reply me-1"></i>Reply
            </a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include(dirname(__DIR__) . "/includes/footer.php"); ?>

<script>
function toggleMessage(element) {
  const fullMessage = element.querySelector('.full-message');
  if (fullMessage.style.display === 'none') {
    fullMessage.style.display = 'block';
  } else {
    fullMessage.style.display = 'none';
  }
}
</script>

