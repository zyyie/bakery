<?php
session_start();
include("../config/connect.php");
include("includes/header.php");

$query = "SELECT * FROM orders WHERE orderStatus = 'Still Pending' ORDER BY orderDate DESC";
$result = executeQuery($query);
?>

<h2 class="mb-4">New Orders</h2>

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
          <td><span class="badge bg-warning"><?php echo $row['orderStatus']; ?></span></td>
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

<?php include("includes/footer.php"); ?>

