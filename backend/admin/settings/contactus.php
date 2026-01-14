<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
requireAdminLogin();
adminRegenerateSession();
include(dirname(__DIR__) . "/includes/header.php");

$success = "";
$error = "";

if(isset($_POST['pageTitle'])){
  $pageTitle = trim($_POST['pageTitle']);
  $pageDescription = trim($_POST['pageDescription']);
  $email = trim($_POST['email']);
  $mobileNumber = trim($_POST['mobileNumber']);
  
  if(empty($pageTitle) || empty($pageDescription) || empty($email) || empty($mobileNumber)){
    $error = "All fields are required!";
  } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    $error = "Invalid email format!";
  } else {
    $query = "UPDATE pages SET pageTitle = ?, pageDescription = ?, 
              email = ?, mobileNumber = ? WHERE pageType = 'contactus'";
    
    $result = executePreparedUpdate($query, "ssss", [$pageTitle, $pageDescription, $email, $mobileNumber]);
    
    if($result !== false){
      $success = "Page updated successfully!";
    } else {
      $error = "Failed to update page!";
    }
  }
}

$query = "SELECT * FROM pages WHERE pageType = ?";
$result = executePreparedQuery($query, "s", ['contactus']);
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

<?php include(dirname(__DIR__) . "/includes/footer.php"); ?>

