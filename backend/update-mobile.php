<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/validation.php';

if(!isset($_SESSION['userID'])){
  header("Location: login.php");
  exit();
}

$userID = intval($_SESSION['userID']);
$error = "";
$success = "";

// Get user info
$userQuery = "SELECT userID, fullName, email, mobileNumber FROM users WHERE userID = ?";
$userResult = executePreparedQuery($userQuery, "i", [$userID]);
$user = mysqli_fetch_assoc($userResult);

if(!$user){
  header("Location: login.php");
  exit();
}

// Handle form submission
if(isset($_POST['updateMobile'])){
  $mobileNumber = trim($_POST['mobileNumber']);
  
  if(empty($mobileNumber)){
    $error = "Mobile number is required!";
  } elseif(!isValidMobileNumber($mobileNumber)){
    $error = "Please enter a valid mobile number. It should contain 7-15 digits and may include +, -, spaces, or parentheses. It should not be an email address.";
  } else {
    // Update mobile number
    $updateQuery = "UPDATE users SET mobileNumber = ? WHERE userID = ?";
    $result = executePreparedUpdate($updateQuery, "si", [$mobileNumber, $userID]);
    
    if($result !== false){
      $success = "Mobile number updated successfully!";
      // Refresh user data
      $userResult = executePreparedQuery($userQuery, "i", [$userID]);
      $user = mysqli_fetch_assoc($userResult);
      
      // Redirect if there's a stored redirect URL
      if(isset($_SESSION['redirect_after_mobile_update'])){
        $redirectUrl = $_SESSION['redirect_after_mobile_update'];
        unset($_SESSION['redirect_after_mobile_update']);
        header("Location: " . $redirectUrl);
        exit();
      }
    } else {
      $error = "Failed to update mobile number. Please try again.";
    }
  }
}

include(__DIR__ . "/includes/header.php");
?>

<div class="checkout-container">
  <div class="checkout-header">
    <h1><i class="fas fa-mobile-alt me-2"></i>Update Mobile Number</h1>
    <p>Please provide a valid mobile number to continue shopping</p>
  </div>

  <?php if($error): ?>
  <div class="alert alert-danger">
    <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
  </div>
  <?php endif; ?>

  <?php if($success): ?>
  <div class="alert alert-success">
    <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
    <div class="mt-3">
      <a href="checkout.php" class="btn btn-brown me-2">Proceed to Checkout</a>
      <a href="cart.php" class="btn btn-outline-brown">Back to Cart</a>
    </div>
  </div>
  <?php endif; ?>

  <div class="checkout-form-card">
    <div class="checkout-form-header">
      <h4><i class="fas fa-user me-2"></i>Account Information</h4>
    </div>
    <div class="checkout-form-body">
      <form method="POST">
        <div class="mb-3">
          <label class="form-label">Full Name</label>
          <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['fullName']); ?>" disabled>
        </div>
        
        <div class="mb-3">
          <label class="form-label">Email Address</label>
          <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
        </div>
        
        <div class="mb-3">
          <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
          <?php 
          // Only show current value if it's valid, otherwise show placeholder
          $mobileValue = '';
          if(!empty($user['mobileNumber']) && isValidMobileNumber($user['mobileNumber'])) {
            $mobileValue = htmlspecialchars($user['mobileNumber']);
          }
          ?>
          <input type="tel" class="form-control" name="mobileNumber" 
                 value="<?php echo $mobileValue; ?>" 
                 placeholder="e.g., 09123456789 or +63 912 345 6789" 
                 required pattern="[\d\s\-\+\(\)]{7,20}" minlength="7" maxlength="20">
          <div class="invalid-feedback">Please enter a valid mobile number (7-15 digits). It should not be an email address.</div>
          <small class="form-text text-muted">
            <i class="fas fa-info-circle me-1"></i>
            Format: 09123456789 or +63 912 345 6789 (7-15 digits, may include +, -, spaces, or parentheses)
          </small>
        </div>
        
        <div class="d-flex gap-2 flex-wrap">
          <button type="submit" name="updateMobile" class="btn btn-brown">
            <i class="fas fa-save me-2"></i>Update Mobile Number
          </button>
          <?php if(isset($_SESSION['redirect_after_mobile_update']) && $_SESSION['redirect_after_mobile_update'] === 'checkout.php'): ?>
            <a href="cart.php" class="btn btn-outline-brown">
              <i class="fas fa-arrow-left me-2"></i>Back to Cart
            </a>
          <?php else: ?>
            <a href="cart.php" class="btn btn-outline-brown">
              <i class="fas fa-shopping-cart me-2"></i>Cart
            </a>
          <?php endif; ?>
          <a href="user-dashboard.php" class="btn btn-outline-secondary">
            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
          </a>
        </div>
      </form>
    </div>
  </div>
  
  <div class="alert alert-info mt-4">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Why do we need your mobile number?</strong>
    <p class="mb-0 mt-2">Your mobile number is required for order delivery and communication. We'll use it to contact you regarding your orders and delivery updates.</p>
  </div>
</div>

<script>
// Client-side form validation
document.addEventListener('DOMContentLoaded', function() {
  const form = document.querySelector('form');
  const mobileInput = form.querySelector('input[name="mobileNumber"]');
  
  // Mobile number validation - should not contain @ symbol
  mobileInput.addEventListener('input', function() {
    const value = this.value.trim();
    
    // Remove previous validation classes
    this.classList.remove('is-invalid', 'is-valid');
    
    if(value.includes('@')) {
      this.setCustomValidity('Mobile number should not contain @ symbol. Please enter a valid phone number.');
      this.classList.add('is-invalid');
    } else if(value.length > 0 && value.length < 7) {
      this.setCustomValidity('Mobile number must be at least 7 digits long.');
      this.classList.add('is-invalid');
    } else if(value.length > 20) {
      this.setCustomValidity('Mobile number is too long (maximum 20 characters).');
      this.classList.add('is-invalid');
    } else {
      // Check if it matches the pattern
      const pattern = /^[\d\s\-\+\(\)]{7,20}$/;
      const digitsOnly = value.replace(/[\s\-\+\(\)]/g, '');
      
      if(digitsOnly.length > 0 && !pattern.test(value)) {
        this.setCustomValidity('Please enter a valid mobile number format (digits, +, -, spaces, or parentheses only).');
        this.classList.add('is-invalid');
      } else if(digitsOnly.length > 0 && (digitsOnly.length < 7 || digitsOnly.length > 15)) {
        this.setCustomValidity('Mobile number must contain 7-15 digits after removing formatting.');
        this.classList.add('is-invalid');
      } else if(value.length >= 7) {
        this.setCustomValidity('');
        this.classList.add('is-valid');
      } else {
        this.setCustomValidity('');
      }
    }
  });
  
  // Form submission validation
  form.addEventListener('submit', function(e) {
    if(!form.checkValidity()) {
      e.preventDefault();
      e.stopPropagation();
    }
    form.classList.add('was-validated');
  });
});
</script>

<?php include(__DIR__ . "/includes/footer.php"); ?>

