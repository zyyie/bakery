<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
requireAdminLogin();
adminRegenerateSession();
include(dirname(__DIR__) . "/includes/header.php");

if(isset($_POST['deleteID'])){
  $deleteID = intval($_POST['deleteID']);
  if($deleteID > 0){
    $query = "DELETE FROM categories WHERE categoryID = ?";
    executePreparedUpdate($query, "i", [$deleteID]);
  }
  header("Location: manage-category.php");
  exit();
}

$query = "SELECT * FROM categories ORDER BY creationDate DESC";
$result = executeQuery($query);
?>

<div class="header-bar">
  <h2 class="mb-0" style="color: #333; font-weight: 600;">Manage Category</h2>
</div>

<div class="card">
  <div class="card-body">
    <table class="table table-striped">
      <thead>
        <tr>
          <th>#</th>
          <th>Category Name</th>
          <th>Creation Date</th>
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
          <td><?php echo $row['categoryName']; ?></td>
          <td><?php echo $row['creationDate']; ?></td>
          <td>
            <form method="POST" class="d-inline">
              <input type="hidden" name="deleteID" value="<?php echo $row['categoryID']; ?>">
              <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
                <i class="fas fa-trash"></i>
              </button>
            </form>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include(dirname(__DIR__) . "/includes/footer.php"); ?>

