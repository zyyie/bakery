<?php
require_once __DIR__ . '/includes/bootstrap.php';

if(isset($_SESSION['adminID'])){
  header("Location: dashboard.php");
  exit();
}

$error = "";

if(isset($_POST['username']) && isset($_POST['password'])){
  $username = trim($_POST['username']);
  $password = $_POST['password'];
  
  // Input validation
  if(empty($username) || empty($password)){
    $error = "Username and password are required!";
  } else {
    // Use prepared statement to prevent SQL injection
    $query = "SELECT * FROM admin WHERE username = ?";
    $result = executePreparedQuery($query, "s", [$username]);
    
    if($result && mysqli_num_rows($result) > 0){
      $admin = mysqli_fetch_assoc($result);
      
      // Verify password using password_verify (works with both MD5 legacy and bcrypt)
      // Check if password is MD5 (legacy) or bcrypt (new)
      if(strlen($admin['password']) == 32 && ctype_xdigit($admin['password'])){
        // Legacy MD5 password - verify and optionally upgrade
        if(md5($password) === $admin['password']){
          // Upgrade to bcrypt on successful login
          $newHash = password_hash($password, PASSWORD_BCRYPT);
          $updateQuery = "UPDATE admin SET password = ? WHERE adminID = ?";
          executePreparedUpdate($updateQuery, "si", [$newHash, $admin['adminID']]);
          
          session_regenerate_id(true);
          $_SESSION['adminID'] = $admin['adminID'];
          $_SESSION['adminUsername'] = $admin['username'];
          header("Location: dashboard.php");
          exit();
        } else {
          $error = "Invalid username or password!";
        }
      } else {
        // New bcrypt password
        if(password_verify($password, $admin['password'])){
          session_regenerate_id(true);
          $_SESSION['adminID'] = $admin['adminID'];
          $_SESSION['adminUsername'] = $admin['username'];
          header("Location: dashboard.php");
          exit();
        } elseif(hash_equals((string)$admin['password'], (string)$password)) {
          // Legacy plain text password - upgrade to bcrypt on successful login
          $newHash = password_hash($password, PASSWORD_BCRYPT);
          $updateQuery = "UPDATE admin SET password = ? WHERE adminID = ?";
          executePreparedUpdate($updateQuery, "si", [$newHash, $admin['adminID']]);

          session_regenerate_id(true);
          $_SESSION['adminID'] = $admin['adminID'];
          $_SESSION['adminUsername'] = $admin['username'];
          header("Location: dashboard.php");
          exit();
        } else {
          $error = "Invalid username or password!";
        }
      }
    } else {
      $error = "Invalid username or password!";
    }
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Login - Bakery Management</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://images.unsplash.com/photo-1556910103-1c02745aae4d?w=1200') center/cover;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .login-card {
      background: #ff9800;
      border-radius: 15px;
      padding: 40px;
      color: white;
      max-width: 400px;
      width: 100%;
    }
  </style>
</head>
<body>
  <div class="login-card">
    <h2 class="text-center mb-4">Admin Login</h2>
    
    <?php if($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST">
      <div class="mb-3">
        <input type="text" class="form-control" name="username" placeholder="Username" required>
      </div>
      <div class="mb-3">
        <input type="password" class="form-control" name="password" placeholder="Password" required>
      </div>
      <div class="mb-3 d-flex justify-content-between">
        <a href="#" class="text-white">Forgot Password?</a>
        <label class="text-white">
          <input type="checkbox" checked> Keep me signed in
        </label>
      </div>
      <button type="submit" class="btn btn-light w-100 mb-3">LOGIN</button>
      <div class="text-center">
        <a href="../index.php" class="text-white"><i class="fas fa-home"></i> BACK HOME</a>
      </div>
    </form>
  </div>
</body>
</html>

