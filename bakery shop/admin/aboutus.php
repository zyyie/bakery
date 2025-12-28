<?php
session_start();
include("../connect.php");
include("includes/header.php");

$success = "";
$error = "";

if(isset($_POST['pageTitle'])){
  $pageTitle = $_POST['pageTitle'];
  $pageDescription = $_POST['pageDescription'];
  
  $query = "UPDATE pages SET pageTitle = '$pageTitle', pageDescription = '$pageDescription' WHERE pageType = 'aboutus'";
  
  if(executeQuery($query)){
    $success = "Page updated successfully!";
  } else {
    $error = "Failed to update page!";
  }
}

$query = "SELECT * FROM pages WHERE pageType = 'aboutus'";
$result = executeQuery($query);
$page = mysqli_fetch_assoc($result);
?>

<h2 class="mb-4 text-warning">About Us</h2>

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
        <label class="form-label">About Us :</label>
      </div>
      <div class="mb-3">
        <label class="form-label">Page Title</label>
        <input type="text" class="form-control" name="pageTitle" value="<?php echo $page['pageTitle']; ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Page Description</label>
        <textarea class="form-control" name="pageDescription" rows="10" required><?php echo $page['pageDescription']; ?></textarea>
      </div>
      <button type="submit" class="btn btn-warning">UPDATE</button>
    </form>
  </div>
</div>

<?php include("includes/footer.php"); ?>

