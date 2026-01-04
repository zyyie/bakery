<?php
require_once __DIR__ . '/includes/bootstrap.php';
requireAdminLogin();
adminRegenerateSession();
include(__DIR__ . "/includes/header.php");

$query = "SELECT * FROM users ORDER BY regDate DESC";
$result = executeQuery($query);
?>

<h2 class="mb-4">Registered Users</h2>

<div class="card">
  <div class="card-body">
    <table class="table table-striped">
      <thead>
        <tr>
          <th>#</th>
          <th>Name</th>
          <th>Mobile Number</th>
          <th>Email</th>
          <th>Reg Date</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $count = 1;
        while($row = mysqli_fetch_assoc($result)):
        ?>
        <tr>
          <td><?php echo $count++; ?></td>
          <td><?php echo $row['fullName']; ?></td>
          <td><?php echo $row['mobileNumber']; ?></td>
          <td><?php echo $row['email']; ?></td>
          <td><?php echo $row['regDate']; ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include(__DIR__ . "/includes/footer.php"); ?>

