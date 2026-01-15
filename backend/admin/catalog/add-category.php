<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
requireAdminLogin();
adminRegenerateSession();
include(dirname(__DIR__) . "/includes/header.php");

$success = "";
$error = "";

if(isset($_POST['categoryName'])){
  $categoryName = trim($_POST['categoryName']);
  
  if(empty($categoryName)){
    $error = "Category name is required!";
  } else {
    $query = "INSERT INTO categories (categoryName) VALUES (?)";
    
    $result = executePreparedUpdate($query, "s", [$categoryName]);
    
    if($result !== false){
      $success = "Category added successfully!";
    } else {
      $error = "Failed to add category!";
    }
  }
}
?>

<div class="header-bar">
  <h2 class="mb-0" style="color: #333; font-weight: 600;">Add Category</h2>
</div>

<?php if($success): ?>
<div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if($error): ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="card">
  <div class="card-body">
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Category Name</label>
        <input type="text" class="form-control" name="categoryName" required>
      </div>
      <button type="submit" class="btn btn-warning">Add Category</button>
    </form>
  </div>
</div>

<?php include(dirname(__DIR__) . "/includes/footer.php"); ?>

