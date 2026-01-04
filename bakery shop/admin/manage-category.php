<?php
session_start();
include("../config/connect.php");
include("includes/header.php");

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

<h2 class="mb-4">Manage Category</h2>

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

<?php include("includes/footer.php"); ?>

