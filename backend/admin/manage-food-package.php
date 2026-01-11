<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdminLogin();
adminRegenerateSession();
include(__DIR__ . "/includes/header.php");

if(isset($_POST['deleteID'])){
  $deleteID = intval($_POST['deleteID']);
  if($deleteID > 0){
    $query = "DELETE FROM items WHERE itemID = ?";
    executePreparedUpdate($query, "i", [$deleteID]);
  }
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
            <?php 
            // Use depth 2 for admin pages (admin is 2 levels deep from root)
            $imageUrl = function_exists('product_image_url') ? product_image_url($row, 2) : '../../frontend/images/placeholder.jpg';
            ?>
            <img src="<?php echo $imageUrl; ?>" 
                 alt="<?php echo htmlspecialchars($row['packageName'], ENT_QUOTES, 'UTF-8'); ?>" 
                 style="width: 80px; height: 80px; object-fit: cover; border-radius: 4px;" 
                 class="border"
                 onerror="this.src='../../frontend/images/placeholder.jpg'">
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

<?php include(__DIR__ . "/includes/footer.php"); ?>

