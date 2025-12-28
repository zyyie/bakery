<?php
require_once __DIR__ . '/includes/bootstrap.php';
requireAdminLogin();
adminRegenerateSession();
include("includes/header.php");

if(isset($_POST['markRead'])){
  $enquiryID = intval($_POST['enquiryID']);
  if($enquiryID > 0){
    $query = "UPDATE enquiries SET status = 'Read' WHERE enquiryID = ?";
    executePreparedUpdate($query, "i", [$enquiryID]);
  }
  header("Location: read-enquiry.php");
  exit();
}

$query = "SELECT * FROM enquiries ORDER BY enquiryDate DESC";
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
          <td><?php echo $row['name']; ?></td>
          <td><?php echo $row['email']; ?></td>
          <td><?php echo $row['mobileNumber']; ?></td>
          <td><?php echo $row['enquiryDate']; ?></td>
          <td>
            <span class="badge bg-<?php echo $row['status'] == 'Read' ? 'success' : 'warning'; ?>">
              <?php echo $row['status']; ?>
            </span>
            <?php if($row['status'] == 'Unread'): ?>
            <form method="POST" class="d-inline">
              <input type="hidden" name="enquiryID" value="<?php echo $row['enquiryID']; ?>">
              <button type="submit" name="markRead" class="btn btn-sm btn-primary">Mark as Read</button>
            </form>
            <?php endif; ?>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include("includes/footer.php"); ?>

