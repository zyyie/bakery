<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
// Include main bootstrap to get product_image_url function
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
requireAdminLogin();
adminRegenerateSession();
include(dirname(__DIR__) . "/includes/header.php");

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

<div class="header-bar">
  <h2 class="mb-0" style="color: #333; font-weight: 600;">Manage Food Package</h2>
</div>

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
            // Use depth 3 for admin/catalog pages (3 levels deep from root: backend/admin/catalog/)
            $imageUrl = function_exists('product_image_url') ? product_image_url($row, 3) : '../../../frontend/images/placeholder.jpg';
            ?>
            <img src="<?php echo htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8'); ?>" 
                 alt="<?php echo htmlspecialchars($row['packageName'], ENT_QUOTES, 'UTF-8'); ?>" 
                 style="width: 80px; height: 80px; object-fit: cover; border-radius: 4px;" 
                 class="border"
                 onerror="this.src='../../../frontend/images/placeholder.jpg'">
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

<?php include(dirname(__DIR__) . "/includes/footer.php"); ?>

