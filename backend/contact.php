<?php
require_once __DIR__ . '/includes/bootstrap.php';

$success = "";
$error = "";

if(isset($_POST['name'])){
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $mobileNumber = trim($_POST['mobileNumber']);
  $message = trim($_POST['message']);
  
  // Input validation
  if(empty($name) || empty($email) || empty($message)){
    $error = "Name, email, and message are required!";
  } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    $error = "Invalid email format!";
  } else {
    // Check if user is logged in
    $userID = isset($_SESSION['userID']) ? $_SESSION['userID'] : null;
    
    $query = "INSERT INTO enquiries (userID, name, email, mobileNumber, message, enquiryDate, status) 
              VALUES (?, ?, ?, ?, ?, NOW(), 'Unread')";
    
    $result = executePreparedUpdate($query, "issss", [$userID, $name, $email, $mobileNumber, $message]);
    
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

include(__DIR__ . "/includes/header.php");
?>


<div class="container contact-page my-5">
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
    <div class="col-md-6">
      <div class="card shadow contact-form-card h-100">
        <div class="card-body p-5">
          <div class="text-center mb-4">
            <h2 class="text-brown fw-bold">Send Project Inquiry</h2>
            <div class="divider bg-brown mx-auto"></div>
            <p class="text-muted mt-2">We welcome feedback and collaboration</p>
          </div>
          <?php if($success): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
          <?php endif; ?>
          <?php if($error): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
          <?php endif; ?>
          <form method="POST" action="">
            <div class="mb-3">
              <div class="input-group">
                <span class="input-group-text bg-brown text-white"><i class="fas fa-user"></i></span>
                <input type="text" class="form-control" name="name" placeholder="Your Full Name" value="<?php echo isset($_SESSION['fullName']) ? e($_SESSION['fullName']) : ''; ?>" required>
              </div>
            </div>
            <div class="mb-3">
              <div class="input-group">
                <span class="input-group-text bg-brown text-white"><i class="fas fa-envelope"></i></span>
                <input type="email" class="form-control" name="email" placeholder="Your Email Address" value="<?php echo isset($_SESSION['email']) ? e($_SESSION['email']) : ''; ?>" required>
              </div>
            </div>
            <div class="mb-3">
              <div class="input-group">
                <span class="input-group-text bg-brown text-white"><i class="fas fa-phone"></i></span>
                <input type="tel" name="mobileNumber" class="form-control" placeholder="Your Phone Number" required>
              </div>
            </div>
            <div class="mb-3">
              <textarea name="message" class="form-control" rows="5" placeholder="Your Message or Inquiry" required></textarea>
            </div>
            <button type="submit" class="btn btn-brown btn-lg w-100">
              <i class="fas fa-paper-plane me-2"></i>Send Inquiry
            </button>
          </form>
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

<?php include(__DIR__ . "/includes/footer.php"); ?>

