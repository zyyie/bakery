<?php
include("connect.php");

$error = "";

if(isset($_POST['email']) && isset($_POST['password'])){
  $email = $_POST['email'];
  $password = md5($_POST['password']);
  
  $query = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
  $result = executeQuery($query);
  
  if(mysqli_num_rows($result) > 0){
    $user = mysqli_fetch_assoc($result);
    $_SESSION['userID'] = $user['userID'];
    $_SESSION['userName'] = $user['fullName'];
    header("Location: index.php");
    exit();
  } else {
    $error = "Invalid email or password!";
  }
}

include("includes/login-header.php");
?>

<div class="login-container">
  <h2 class="text-center mb-4">Login to your account</h2>
  
  <?php if($error): ?>
  <div class="alert alert-danger"><?php echo $error; ?></div>
  <?php endif; ?>
  
  <form method="POST">
    <div class="mb-3">
      <label class="form-label">Email*</label>
      <input type="email" class="form-control" name="email" placeholder="Enter Email" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Password*</label>
      <input type="password" class="form-control" name="password" placeholder="Password" required>
    </div>
    <div class="mb-3">
      <a href="#" class="text-primary">Forgot password?</a>
    </div>
    <button type="submit" class="btn btn-warning w-100">Login</button>
  </form>
  
  <div class="mt-3 text-center">
    <p>Don't have an account? <a href="signup.php">Sign Up</a></p>
  </div>
</div>

<?php include("includes/login-footer.php"); ?>

