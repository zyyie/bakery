<?php
require_once __DIR__ . '/includes/bootstrap.php';
requireAdminLogin();
adminRegenerateSession();
include(__DIR__ . "/includes/header.php");

$query = "SELECT * FROM orders WHERE orderStatus = 'On The Way' ORDER BY orderDate DESC";
$result = executeQuery($query);
?>

<h2 class="mb-4">On The Way Orders</h2>

<div class="card">
  <div class="card-body">
    <table class="table table-striped">
      <thead>
        <tr>
          <th>#</th>
          <th>Order Number</th>
          <th>Full Name</th>
          <th>Contact Number</th>
          <th>Order Date</th>
          <th>Status</th>
          <th>Delivery Date</th>
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
          <td><?php echo $row['orderNumber']; ?></td>
          <td><?php echo $row['fullName']; ?></td>
          <td><?php echo $row['contactNumber']; ?></td>
          <td><?php echo $row['orderDate']; ?></td>
          <td>
            <select class="form-select form-select-sm status-update" data-orderid="<?php echo $row['orderID']; ?>" style="width: auto; display: inline-block;">
              <option value="Still Pending" <?php echo $row['orderStatus'] == 'Still Pending' ? 'selected' : ''; ?>>Still Pending</option>
              <option value="Confirmed" <?php echo $row['orderStatus'] == 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
              <option value="On The Way" <?php echo $row['orderStatus'] == 'On The Way' ? 'selected' : ''; ?>>On The Way</option>
              <option value="Delivered" <?php echo $row['orderStatus'] == 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
              <option value="Cancelled" <?php echo $row['orderStatus'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
            </select>
          </td>
          <td>
            <?php if(!empty($row['deliveryDate'])): ?>
              <?php echo date('Y-m-d', strtotime($row['deliveryDate'])); ?>
            <?php else: ?>
              <span class="text-muted">Not set</span>
            <?php endif; ?>
          </td>
          <td>
            <a href="view-order-detail.php?viewid=<?php echo $row['orderID']; ?>" class="btn btn-primary btn-sm">
              <i class="fas fa-eye"></i>
            </a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include(__DIR__ . "/includes/footer.php"); ?>

<script>
// Quick status update via AJAX
document.querySelectorAll('.status-update').forEach(select => {
  select.addEventListener('change', function() {
    const orderID = this.getAttribute('data-orderid');
    const newStatus = this.value;
    const originalValue = this.getAttribute('data-original');
    
    if(confirm(`Change order status to "${newStatus}"?`)) {
      fetch('update-order-status-quick.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `orderID=${orderID}&orderStatus=${encodeURIComponent(newStatus)}`
      })
      .then(response => response.json())
      .then(data => {
        if(data.success) {
          location.reload();
        } else {
          alert('Error updating status: ' + (data.message || 'Unknown error'));
          this.value = originalValue;
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Error updating status. Please try again.');
        this.value = originalValue;
      });
    } else {
      this.value = originalValue || this.options[0].value;
    }
  });
  
  // Store original value
  select.setAttribute('data-original', select.value);
});
</script>

