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
              <label class="form-label">Repeat Password*</label>
              <input type="password" class="form-control" name="repeatPassword" placeholder="Repeat Password" required>
            </div>
          </div>
          <button type="submit" class="btn btn-warning w-100">Sign Up</button>
        </form>
        
        <div class="mt-3 text-center">
          <p>Already have an account? <a href="login.php">Login</a></p>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include("includes/footer.php"); ?>

