<?php
session_start();
include("../connect.php");
include("includes/header.php");

$success = "";
$error = "";

if(isset($_POST['categoryName'])){
  $categoryName = $_POST['categoryName'];
  $query = "INSERT INTO categories (categoryName) VALUES ('$categoryName')";
  
  if(executeQuery($query)){
    $success = "Category added successfully!";
  } else {
    $error = "Failed to add category!";
  }
}
?>

<h2 class="mb-4">Add Category</h2>

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

<?php include("includes/footer.php"); ?>

