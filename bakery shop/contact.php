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


<div class="container contact-page my-5">
  <div class="row g-4">
    <div class="col-md-6">
      <div class="card shadow p-4">
        <h3><?php echo $page['pageTitle']; ?></h3>
        <hr>
        <div class="contact-info">
          <?php echo nl2br($page['pageDescription']); ?>
          <p><i class="fas fa-envelope"></i> <strong>Email:</strong> <?php echo $page['email']; ?></p>
          <p><i class="fas fa-phone"></i> <strong>Phone:</strong> <?php echo $page['mobileNumber']; ?></p>
        </div>
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
        <form method="POST" action="">
          <div class="mb-3">
            <input type="text" name="name" class="form-control" placeholder="Your Name" required>
          </div>
          <div class="mb-3">
            <input type="email" name="email" class="form-control" placeholder="Your Email" required>
          </div>
          <div class="mb-3">
            <input type="tel" name="mobileNumber" class="form-control" placeholder="Your Phone Number" required>
          </div>
          <div class="mb-3">
            <textarea name="message" class="form-control" rows="4" placeholder="Your Message" required></textarea>
          </div>
          <button type="submit" class="btn btn-send">
            <i class="fas fa-paper-plane me-2"></i>Send Message
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include("includes/footer.php"); ?>

