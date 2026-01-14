<?php
header('Location: ../api-sales-report.php');
exit();

__halt_compiler();
?>

<h2 class="mb-4">Sales Reports</h2>

<div class="card mb-4">
  <div class="card-body">
    <form method="POST">
      <div class="row">
        <div class="col-md-4 mb-3">
          <label class="form-label">From Date:</label>
          <input type="date" class="form-control" name="fromDate" value="<?php echo isset($_POST['fromDate']) ? $_POST['fromDate'] : date('Y-m-01'); ?>" required>
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">To Date:</label>
          <input type="date" class="form-control" name="toDate" value="<?php echo isset($_POST['toDate']) ? $_POST['toDate'] : date('Y-m-d'); ?>" required>
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">Request Type:</label>
          <div>
            <input type="radio" name="requestType" value="Month wise" <?php echo (!isset($_POST['requestType']) || $_POST['requestType'] == 'Month wise') ? 'checked' : ''; ?>> Month wise
            <input type="radio" name="requestType" value="Year wise" <?php echo (isset($_POST['requestType']) && $_POST['requestType'] == 'Year wise') ? 'checked' : ''; ?>> Year wise
          </div>
        </div>
      </div>
      <button type="submit" class="btn btn-warning">SUBMIT</button>
    </form>
  </div>
</div>

<?php if(!empty($results)): ?>
<div class="card">
  <div class="card-body">
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Period</th>
          <th>Total Orders</th>
          <th>Total Sales</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($results as $row): ?>
        <tr>
          <td><?php echo $row['period']; ?></td>
          <td><?php echo $row['totalOrders']; ?></td>
          <td>₱<?php echo number_format($row['totalSales'], 2); ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php include(dirname(__DIR__) . "/includes/footer.php"); ?>

