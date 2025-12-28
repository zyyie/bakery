<?php
session_start();
include("../connect.php");
include("includes/header.php");

if(isset($_POST['deleteID'])){
  $deleteID = intval($_POST['deleteID']);
  $query = "DELETE FROM items WHERE itemID = $deleteID";
  executeQuery($query);
  header("Location: manage-food-package.php");
  exit();
}

$query = "SELECT items.*, categories.categoryName FROM items 
          LEFT JOIN categories ON items.categoryID = categories.categoryID 
          ORDER BY items.creationDate DESC";
$result = executeQuery($query);
?>

<h2 class="mb-4">Manage Food Package</h2>

<div class="card">
  <div class="card-body">
    <table class="table table-striped">
      <thead>
        <tr>
          <th>#</th>
          <th>Image</th>
          <th>Package Name</th>
          <th>Status</th>
          <th>Price</th>
          <th>Category</th>
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
            <img src="<?php echo $row['itemImage'] ? '../uploads/'.$row['itemImage'] : 'https://via.placeholder.com/80'; ?>" 
                 alt="<?php echo $row['packageName']; ?>" style="width: 80px; height: 80px; object-fit: cover;">
          </td>
          <td><?php echo $row['packageName']; ?></td>
          <td>
            <span class="badge bg-<?php echo $row['status'] == 'Active' ? 'success' : 'secondary'; ?>">
              <?php echo $row['status']; ?>
            </span>
          </td>
          <td>₱<?php echo $row['price']; ?></td>
          <td><?php echo $row['categoryName']; ?></td>
          <td>
            <a href="edit-food-package.php?itemID=<?php echo $row['itemID']; ?>" class="btn btn-info btn-sm">
              <i class="fas fa-edit"></i>
            </a>
            <form method="POST" class="d-inline">
              <input type="hidden" name="deleteID" value="<?php echo $row['itemID']; ?>">
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

