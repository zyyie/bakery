<?php
require_once __DIR__ . '/../../includes/bootstrap.php';

$success = "";
$error = "";

if(isset($_POST['name'])){
  $name = trim($_POST['name']);
  $message = trim($_POST['message']);
  
  // Input validation
  if(empty($name) || empty($message)){
    $error = "Name and message are required!";
  } else {
    // Check if user is logged in
    $userID = isset($_SESSION['userID']) ? $_SESSION['userID'] : null;
    
    $query = "INSERT INTO enquiries (userID, name, email, mobileNumber, message, enquiryDate, status) 
              VALUES (?, ?, '', '', ?, NOW(), 'Unread')";
    
    $result = executePreparedUpdate($query, "iss", [$userID, $name, $message]);
    
    if($result !== false){
      $success = "Your inquiry has been submitted successfully! We'll get back to you soon.";
    } else {
      $error = "Failed to submit enquiry!";
    }
  }
}

$query = "SELECT * FROM pages WHERE pageType = ?";
$result = executePreparedQuery($query, "s", ['contactus']);
$page = mysqli_fetch_assoc($result);

include(__DIR__ . "/../../includes/header.php");
?>


<div class="container contact-page my-5">
  <div class="mb-3">
    <a href="index.php" class="btn btn-outline-secondary btn-sm">
      <i class="fas fa-arrow-left me-2"></i>Back to Home
    </a>
  </div>
  
  <div class="row g-4">
    <div class="col-md-6">
      <div class="card shadow contact-card h-100">
        <div class="card-body p-5">
          <div class="text-center mb-4">
            <h2 class="text-brown fw-bold">Contact Information</h2>
            <div class="divider bg-brown mx-auto"></div>
            <p class="text-muted mt-2">3rd Year IT Project | Polytechnic University of the Philippines - Sto. Tomas Campus</p>
          </div>
          <div class="contact-info">
            <?php echo nl2br(e($page['pageDescription'])); ?>
            <div class="contact-item mt-4">
              <div class="d-flex align-items-center mb-3">
                <div class="contact-icon me-3">
                  <i class="fas fa-map-marker-alt fa-2x text-brown"></i>
                </div>
                <div>
                  <h5 class="mb-1">Project Location</h5>
                  <p class="mb-0 text-muted">PUP Sto. Tomas Campus, Batangas, Philippines</p>
                </div>
              </div>
            </div>
            <div class="contact-item">
              <div class="d-flex align-items-center mb-3">
                <div class="contact-icon me-3">
                  <i class="fas fa-envelope fa-2x text-brown"></i>
                </div>
                <div>
                  <h5 class="mb-1">Project Email</h5>
                  <p class="mb-0 text-muted"><?php echo e($page['email']); ?></p>
                </div>
              </div>
            </div>
            <div class="contact-item">
              <div class="d-flex align-items-center">
                <div class="contact-icon me-3">
                  <i class="fas fa-phone fa-2x text-brown"></i>
                </div>
                <div>
                  <h5 class="mb-1">Contact Number</h5>
                  <p class="mb-0 text-muted"><?php echo e($page['mobileNumber']); ?></p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="row mt-5">
    <div class="col-12">
      <div class="card shadow map-card">
        <div class="card-body p-0">
          <div class="map-placeholder bg-light text-center p-5">
            <i class="fas fa-university fa-4x text-brown mb-3"></i>
            <h4 class="text-brown">Polytechnic University of the Philippines - Sto. Tomas Campus</h4>
            <p class="text-muted">3rd Year IT Project Showcase</p>
            <div class="mt-3">
              <span class="badge bg-brown me-2">PHP</span>
              <span class="badge bg-brown me-2">MySQL</span>
              <span class="badge bg-brown me-2">Bootstrap</span>
              <span class="badge bg-brown">PayPal</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include(__DIR__ . "/../../includes/footer.php"); ?>

