<?php
require_once __DIR__ . '/../../includes/bootstrap.php';

$error = "";
$success = "";

if(isset($_POST['fullName'])){
  $fullName = trim($_POST['fullName']);
  $email = trim($_POST['email']);
  $mobileNumber = trim($_POST['mobileNumber']);
  $password = $_POST['password'];
  $repeatPassword = $_POST['repeatPassword'];
  
  // Input validation
  if(empty($fullName) || empty($email) || empty($mobileNumber) || empty($password)){
    $error = "All fields are required!";
  } elseif(!preg_match('/^[A-Za-z\s]{2,255}$/', trim($fullName))){
    $error = "Full name should only contain letters and spaces (2-255 characters).";
  } elseif(strlen(trim($fullName)) < 2){
    $error = "Full name must be at least 2 characters long.";
  } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    $error = "Invalid email format!";
  } elseif(strlen($email) > 255){
    $error = "Email address is too long (maximum 255 characters).";
  } elseif(!isValidMobileNumber($mobileNumber)){
    $error = "Please enter a valid mobile number (7-15 digits). It may include +, -, spaces, or parentheses. It should not be an email address.";
  } elseif($password != $repeatPassword){
    $error = "Passwords do not match!";
  } elseif(strlen($password) < 6){
    $error = "Password must be at least 6 characters long!";
  } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    $error = "Invalid email format!";
  } else {
    // Require OTP verification for the provided mobile number (normalize to digits-only for consistency)
    $normalizedMobile = preg_replace('/\D+/', '', $mobileNumber);
    $isVerified = isset($_SESSION['otp_verified'][$normalizedMobile]) && $_SESSION['otp_verified'][$normalizedMobile] === true;
    if (!$isVerified) {
      $error = "Please verify your mobile number with the OTP.";
    } else {
    // Check if email already exists using prepared statement
    $checkQuery = "SELECT * FROM users WHERE email = ?";
    $checkResult = executePreparedQuery($checkQuery, "s", [$email]);
    
    if($checkResult && mysqli_num_rows($checkResult) > 0){
      $error = "Email already registered!";
    } else {
      // Use secure password hashing
      $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
      $query = "INSERT INTO users (fullName, email, mobileNumber, password) 
                VALUES (?, ?, ?, ?)";
      
      $result = executePreparedUpdate($query, "ssss", [$fullName, $email, $mobileNumber, $hashedPassword]);
      
      if($result !== false){
        $success = "Registration successful! Please login.";
        // Clear verification state after successful registration
        unset($_SESSION['otp_verified'][$normalizedMobile]);
      } else {
        $error = "Registration failed! Please try again.";
      }
    }
    }
  }
}

include(__DIR__ . "/../../includes/login-header.php");
?>


<div class="auth-container">
  <div class="auth-card auth-card-lg">
    <div class="text-center mb-4">
      <img src="../frontend/images/logo.png" alt="Bakery Logo" class="auth-logo mb-3">
      <h2 class="auth-title mb-1">Create Account</h2>
      <div class="auth-subtitle">Join us and enjoy fresh baked goods</div>
    </div>

    <?php if($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if($success): ?>
    <div class="alert alert-success"><?php echo $success; ?> <a href="login.php">Login here</a></div>
    <?php endif; ?>

    <form method="POST" class="auth-form" id="signupForm">
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Full Name <span class="text-danger">*</span></label>
          <input type="text" class="form-control" name="fullName" placeholder="Enter your full name" 
                 required minlength="2" maxlength="255" pattern="[A-Za-z\s]+"
                 value="<?php echo isset($_POST['fullName']) ? htmlspecialchars($_POST['fullName']) : ''; ?>">
          <div class="invalid-feedback">Please enter a valid full name (letters and spaces only, 2-255 characters).</div>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Email <span class="text-danger">*</span></label>
          <input type="email" class="form-control" name="email" placeholder="your.email@example.com" 
                 required maxlength="255"
                 value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
          <div class="invalid-feedback">Please enter a valid email address.</div>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Mobile Number</label>
          <div class="input-group">
            <input type="text" class="form-control" name="mobileNumber" id="mobileNumber" placeholder="Mobile Number" required>
            <button type="button" class="btn btn-outline-secondary" id="sendOtpBtn">Send OTP</button>
          </div>
          <div class="form-text" id="sendOtpStatus"></div>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Enter OTP</label>
          <div class="input-group">
            <input type="text" class="form-control" id="otpCode" placeholder="6-digit code">
            <button type="button" class="btn btn-outline-secondary" id="verifyOtpBtn">Verify OTP</button>
          </div>
          <div class="form-text" id="verifyOtpStatus"></div>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Password <span class="text-danger">*</span></label>
          <input type="password" class="form-control" name="password" placeholder="Enter password" 
                 required minlength="6" maxlength="255">
          <div class="invalid-feedback">Password must be at least 6 characters long.</div>
          <small class="form-text text-muted">Minimum 6 characters</small>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Repeat Password <span class="text-danger">*</span></label>
          <input type="password" class="form-control" name="repeatPassword" placeholder="Repeat password" 
                 required minlength="6" maxlength="255">
          <div class="invalid-feedback">Passwords do not match.</div>
        </div>
      </div>
      <button type="submit" class="btn btn-brown w-100">Sign Up</button>
    </form>

    <script>
    // Client-side form validation
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('signupForm');
      const password = form.querySelector('input[name="password"]');
      const repeatPassword = form.querySelector('input[name="repeatPassword"]');
      
      // Real-time password match validation
      repeatPassword.addEventListener('input', function() {
        if(this.value !== password.value) {
          this.setCustomValidity('Passwords do not match');
          this.classList.add('is-invalid');
        } else {
          this.setCustomValidity('');
          this.classList.remove('is-invalid');
        }
      });
      
      password.addEventListener('input', function() {
        if(repeatPassword.value && repeatPassword.value !== this.value) {
          repeatPassword.setCustomValidity('Passwords do not match');
          repeatPassword.classList.add('is-invalid');
        } else {
          repeatPassword.setCustomValidity('');
          repeatPassword.classList.remove('is-invalid');
        }
      });
      
      // Mobile number validation (should not contain @ symbol)
      const mobileNumber = form.querySelector('input[name="mobileNumber"]');
      mobileNumber.addEventListener('input', function() {
        if(this.value.includes('@')) {
          this.setCustomValidity('Mobile number should not contain @ symbol');
          this.classList.add('is-invalid');
        } else {
          this.setCustomValidity('');
          this.classList.remove('is-invalid');
        }
      });
      
      // Full name validation
      const fullName = form.querySelector('input[name="fullName"]');
      fullName.addEventListener('input', function() {
        const namePattern = /^[A-Za-z\s]+$/;
        if(this.value && !namePattern.test(this.value.trim())) {
          this.setCustomValidity('Name should only contain letters and spaces');
          this.classList.add('is-invalid');
        } else {
          this.setCustomValidity('');
          this.classList.remove('is-invalid');
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

    <div class="auth-footer text-center mt-3">
      <div>Already have an account? <a class="auth-link" href="login.php">Login</a></div>
      <a class="auth-link d-inline-block mt-2" href="index.php"><i class="fas fa-arrow-left me-1"></i>Back to Home</a>
    </div>
  </div>
</div>

<?php include(__DIR__ . "/../../includes/login-footer.php"); ?>

<script>
(function(){
  const sendBtn = document.getElementById('sendOtpBtn');
  const verifyBtn = document.getElementById('verifyOtpBtn');
  const phoneInput = document.getElementById('mobileNumber');
  const codeInput = document.getElementById('otpCode');
  const sendStatus = document.getElementById('sendOtpStatus');
  const verifyStatus = document.getElementById('verifyOtpStatus');

  async function postJson(url, data){
    const res = await fetch(url, {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify(data),
      credentials: 'include'
    });
    const txt = await res.text();
    try { return { ok: res.ok, status: res.status, data: JSON.parse(txt) }; }
    catch(e){ return { ok: res.ok, status: res.status, data: { raw: txt } }; }
  }

  if (sendBtn) {
    sendBtn.addEventListener('click', async function(){
      const phone = (phoneInput.value || '').trim();
      if(!phone){
        sendStatus.textContent = 'Please enter your mobile number.';
        sendStatus.className = 'form-text text-danger';
        return;
      }
      sendBtn.disabled = true; sendStatus.textContent = 'Sending...'; sendStatus.className = 'form-text';
      const resp = await postJson('api/sms_send_otp.php', { phone });
      if(resp.ok && resp.data && resp.data.ok){
        sendStatus.textContent = 'OTP sent.';
        sendStatus.className = 'form-text text-success';
      } else {
        sendStatus.textContent = 'Failed to send OTP.';
        sendStatus.className = 'form-text text-danger';
      }
      sendBtn.disabled = false;
    });
  }

  if (verifyBtn) {
    verifyBtn.addEventListener('click', async function(){
      const phone = (phoneInput.value || '').trim();
      const code = (codeInput.value || '').trim();
      if(!phone || !code){
        verifyStatus.textContent = 'Enter phone and OTP code.';
        verifyStatus.className = 'form-text text-danger';
        return;
      }
      verifyBtn.disabled = true; verifyStatus.textContent = 'Verifying...'; verifyStatus.className = 'form-text';
      const resp = await postJson('api/verify_otp.php', { phone, code });
      if(resp.ok && resp.data && resp.data.ok){
        verifyStatus.textContent = 'Phone verified.';
        verifyStatus.className = 'form-text text-success';
      } else {
        const msg = resp.data && resp.data.error ? resp.data.error : 'Verification failed.';
        verifyStatus.textContent = msg;
        verifyStatus.className = 'form-text text-danger';
      }
      verifyBtn.disabled = false;
    });
  }
})();
</script>
