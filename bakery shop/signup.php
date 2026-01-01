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
      } else {
        $error = "Registration failed! Please try again.";
      }
    }
  }
}

include("includes/header.php");
?>


<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card shadow rounded-4 p-5">
        <h2 class="mb-4">Sign Up to your account</h2>
        
        <?php if($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
        <div class="alert alert-success"><?php echo $success; ?> <a href="login.php">Login here</a></div>
        <?php endif; ?>
        
        <form method="POST">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Full Name*</label>
              <input type="text" class="form-control" name="fullName" placeholder="Full name" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Your Email*</label>
              <input type="email" class="form-control" name="email" placeholder="Your Email" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Mobile Number*</label>
              <input type="text" class="form-control" name="mobileNumber" placeholder="Mobile Number" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Password*</label>
              <input type="password" class="form-control" name="password" placeholder="Password" required>
            </div>
            <div class="col-md-6 mb-3">
              <!-- Empty space to align with right column -->
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Confirm Password*</label>
              <input type="password" class="form-control" name="repeatPassword" placeholder="Confirm Password" required>
            </div>
          </div>
          <button type="submit" class="btn btn-warning w-100">Sign Up</button>
        </form>
        
        <!-- Divider with OR -->
        <div class="position-relative my-4">
          <hr>
          <div class="position-absolute top-50 start-50 translate-middle bg-white px-3">
            <span class="text-muted">OR</span>
          </div>
        </div>
        
        <!-- Social Login Buttons -->
        <div class="social-login">
          <button type="button" class="btn btn-light w-100 mb-3 social-btn facebook-btn" onclick="loginWithFacebook()">
            <img src="https://www.facebook.com/images/fb_icon_325x325.png" alt="Facebook" class="social-logo me-2" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
            <i class="fab fa-facebook-f me-2" style="color: #1877F2; display: none;"></i>
            <span>Sign Up with Facebook</span>
          </button>
          <button type="button" class="btn btn-light w-100 social-btn google-btn" onclick="loginWithGoogle()">
            <img src="https://www.google.com/favicon.ico" alt="Google" class="social-logo me-2" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
            <i class="fab fa-google me-2" style="color: #4285F4; display: none;"></i>
            <span>Sign Up with Google</span>
          </button>
        </div>
        
        <div class="mt-4 text-center">
          <p class="small text-muted">By signing up, you agree to KARNEEK Bakery's <a href="#" class="text-primary">TERMS OF SERVICE</a> & <a href="#" class="text-primary">PRIVACY POLICY</a></p>
        </div>
        
        <div class="mt-3 text-center">
          <p>Already have an account? <a href="login.php">Login</a></p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Facebook SDK -->
<div id="fb-root"></div>
<script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js"></script>

<!-- Google Sign-In -->
<script src="https://apis.google.com/js/platform.js" async defer></script>
<meta name="google-signin-client_id" content="YOUR_GOOGLE_CLIENT_ID">

<style>
.social-btn {
  border: 1px solid #dee2e6;
  padding: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 500;
  transition: all 0.3s ease;
}

.social-btn:hover {
  background-color: #f8f9fa;
  border-color: #adb5bd;
  transform: translateY(-2px);
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.social-logo {
  width: 20px;
  height: 20px;
  object-fit: contain;
}

.social-btn i {
  font-size: 1.2rem;
  width: 24px;
  text-align: center;
}

.facebook-btn:hover {
  background-color: #E7F3FF;
  border-color: #1877F2;
}

.google-btn:hover {
  background-color: #F0F7FF;
  border-color: #4285F4;
}
</style>

<script>
// Initialize Facebook SDK
window.fbAsyncInit = function() {
  FB.init({
    appId: 'YOUR_FACEBOOK_APP_ID', // Replace with your Facebook App ID
    cookie: true,
    xfbml: true,
    version: 'v18.0'
  });
};

// Facebook Login
function loginWithFacebook() {
  FB.login(function(response) {
    if (response.authResponse) {
      // Get user info
      FB.api('/me', {fields: 'name,email'}, function(userInfo) {
        // Send to server for registration/login
        handleSocialLogin('facebook', {
          id: response.authResponse.userID,
          name: userInfo.name,
          email: userInfo.email
        });
      });
    } else {
      console.log('User cancelled login or did not fully authorize.');
    }
  }, {scope: 'email,public_profile'});
}

// Google Login using OAuth 2.0
function loginWithGoogle() {
  // Google OAuth 2.0 configuration
  const clientId = 'YOUR_GOOGLE_CLIENT_ID'; // Replace with your Google Client ID
  const redirectUri = window.location.origin + '/social-login-callback.php?provider=google';
  const scope = 'openid email profile';
  const responseType = 'code';
  
  // Build OAuth URL
  const authUrl = `https://accounts.google.com/o/oauth2/v2/auth?` +
    `client_id=${encodeURIComponent(clientId)}&` +
    `redirect_uri=${encodeURIComponent(redirectUri)}&` +
    `response_type=${responseType}&` +
    `scope=${encodeURIComponent(scope)}&` +
    `access_type=online`;
  
  // Open popup window
  const popup = window.open(
    authUrl,
    'googleLogin',
    'width=500,height=600,scrollbars=yes,resizable=yes'
  );
  
  // Listen for message from popup
  window.addEventListener('message', function(event) {
    if (event.origin !== window.location.origin) return;
    
    if (event.data.type === 'GOOGLE_LOGIN_SUCCESS') {
      handleSocialLogin('google', event.data.userData);
      popup.close();
    } else if (event.data.type === 'GOOGLE_LOGIN_ERROR') {
      alert('Google login failed: ' + event.data.error);
      popup.close();
    }
  });
}

// Handle social login
function handleSocialLogin(provider, userData) {
  fetch('social-login.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      provider: provider,
      social_id: userData.id,
      name: userData.name,
      email: userData.email
    })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      if (data.redirect) {
        window.location.href = data.redirect;
      } else {
        window.location.reload();
      }
    } else {
      alert(data.message || 'Login failed. Please try again.');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('An error occurred. Please try again.');
  });
}
</script>

<?php include("includes/footer.php"); ?>


