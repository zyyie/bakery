<?php
session_start();
include("connect.php");

$success = "";
$error = "";

if(isset($_POST['name'])){
  $name = $_POST['name'];
  $email = $_POST['email'];
  $mobileNumber = $_POST['mobileNumber'];
  $message = $_POST['message'];
  
  $query = "INSERT INTO enquiries (name, email, mobileNumber, message) 
            VALUES ('$name', '$email', '$mobileNumber', '$message')";
  
  if(executeQuery($query)){
    $success = "Your enquiry has been submitted successfully!";
  } else {
    $error = "Failed to submit enquiry!";
  }
}

$query = "SELECT * FROM pages WHERE pageType = 'contactus'";
$result = executeQuery($query);
$page = mysqli_fetch_assoc($result);

include("includes/header.php");
?>


<div class="container my-5">
  <div class="row">
    <div class="col-md-6">
      <div class="card shadow p-4">
        <h3><?php echo $page['pageTitle']; ?></h3>
        <hr>
        <p><?php echo nl2br($page['pageDescription']); ?></p>
        <p><strong>Email:</strong> <?php echo $page['email']; ?></p>
        <p><strong>Phone:</strong> <?php echo $page['mobileNumber']; ?></p>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card shadow p-4">
        <h3>Send Enquiry</h3>
        <?php if($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
          <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" class="form-control" name="name" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Mobile Number</label>
            <input type="text" class="form-control" name="mobileNumber" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Message</label>
            <textarea class="form-control" name="message" rows="5" required></textarea>
          </div>
          <button type="submit" class="btn btn-warning">Submit</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include("includes/footer.php"); ?>

