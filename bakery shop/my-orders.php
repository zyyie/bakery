<?php
require_once __DIR__ . '/includes/bootstrap.php';

if(!isset($_SESSION['userID'])){
  header("Location: login.php");
  exit();
}

$userID = intval($_SESSION['userID']);
$query = "SELECT * FROM orders WHERE userID = ? ORDER BY orderDate DESC";
$result = executePreparedQuery($query, "i", [$userID]);

include("includes/header.php");
?>


<div class="container my-5">
  <div class="card shadow">
    <div class="card-body">
      <h4 class="text-primary mb-4">Details of Order Placed</h4>
      <table class="table table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th>Order ID</th>
            <th>Order Date and Time</th>
            <th>Order Status</th>
            <th>Track Order</th>
            <th>View Details</th>
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
            <td><?php echo $row['orderDate']; ?></td>
            <td>
              <span class="badge 
                <?php 
                if($row['orderStatus'] == 'Delivered') echo 'bg-success';
                elseif($row['orderStatus'] == 'On The Way') echo 'bg-info';
                elseif($row['orderStatus'] == 'Confirmed') echo 'bg-primary';
                else echo 'bg-warning';
                ?>">
                <?php echo $row['orderStatus']; ?>
              </span>
            </td>
            <td>
              <a href="track-order.php?orderID=<?php echo $row['orderID']; ?>" class="btn btn-sm btn-info">
                <i class="fas fa-motorcycle"></i> Track Order
              </a>
            </td>
            <td>
              <a href="order-details.php?orderID=<?php echo $row['orderID']; ?>" class="btn btn-sm btn-primary">
                View Details
              </a>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include("includes/footer.php"); ?>

