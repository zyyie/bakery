<?php
session_start();
include("../connect.php");
include("includes/header.php");

$success = "";
$error = "";

if(isset($_POST['pageTitle'])){
  $pageTitle = $_POST['pageTitle'];
  $pageDescription = $_POST['pageDescription'];
  $email = $_POST['email'];
  $mobileNumber = $_POST['mobileNumber'];
  
  $query = "UPDATE pages SET pageTitle = '$pageTitle', pageDescription = '$pageDescription', 
            email = '$email', mobileNumber = '$mobileNumber' WHERE pageType = 'contactus'";
  
  if(executeQuery($query)){
    $success = "Page updated successfully!";
  } else {
    $error = "Failed to update page!";
  }
}

$query = "SELECT * FROM pages WHERE pageType = 'contactus'";
$result = executeQuery($query);
$page = mysqli_fetch_assoc($result);
?>

<h2 class="mb-4 text-warning">Contact Us</h2>

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
        <label class="form-label">Page Title</label>
        <input type="text" class="form-control" name="pageTitle" value="<?php echo $page['pageTitle']; ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Page Description</label>
        <textarea class="form-control" name="pageDescription" rows="5" required><?php echo $page['pageDescription']; ?></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" name="email" value="<?php echo $page['email']; ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Mobile Number</label>
        <input type="text" class="form-control" name="mobileNumber" value="<?php echo $page['mobileNumber']; ?>" required>
      </div>
      <button type="submit" class="btn btn-warning">UPDATE</button>
    </form>
  </div>
</div>

<?php include("includes/footer.php"); ?>

