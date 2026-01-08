<?php
require_once __DIR__ . '/includes/bootstrap.php';

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
  } elseif($password != $repeatPassword){
    $error = "Passwords do not match!";
  } elseif(strlen($password) < 6){
    $error = "Password must be at least 6 characters long!";
  } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    $error = "Invalid email format!";
  } else {
    // Require OTP verification for the provided mobile number
    $isVerified = isset($_SESSION['otp_verified'][$mobileNumber]) && $_SESSION['otp_verified'][$mobileNumber] === true;
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
        unset($_SESSION['otp_verified'][$mobileNumber]);
      } else {
        $error = "Registration failed! Please try again.";
      }
    }
    }
  }
}

include(__DIR__ . "/includes/login-header.php");
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

    <form method="POST" class="auth-form">
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Full Name</label>
          <input type="text" class="form-control" name="fullName" placeholder="Full name" required>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" name="email" placeholder="Your Email" required>
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
          <label class="form-label">Password</label>
          <input type="password" class="form-control" name="password" placeholder="Password" required>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Repeat Password</label>
          <input type="password" class="form-control" name="repeatPassword" placeholder="Repeat Password" required>
        </div>
      </div>
      <button type="submit" class="btn btn-brown w-100">Sign Up</button>
    </form>

    <div class="auth-footer text-center mt-3">
      <div>Already have an account? <a class="auth-link" href="login.php">Login</a></div>
      <a class="auth-link d-inline-block mt-2" href="index.php"><i class="fas fa-arrow-left me-1"></i>Back to Home</a>
    </div>
  </div>
</div>

<?php include(__DIR__ . "/includes/login-footer.php"); ?>

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
      body: JSON.stringify(data)
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

