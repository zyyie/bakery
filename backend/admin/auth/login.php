<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';

$scriptName = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? ''));
$adminBaseUrl = '';
$adminPos = strpos($scriptName, '/admin/');
if ($adminPos !== false) {
  $adminBaseUrl = substr($scriptName, 0, $adminPos) . '/admin';
}

if(isset($_SESSION['adminID'])){
  header('Location: ' . ($adminBaseUrl !== '' ? ($adminBaseUrl . '/dashboard.php') : 'dashboard.php'));
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
          header('Location: ' . ($adminBaseUrl !== '' ? ($adminBaseUrl . '/dashboard.php') : 'dashboard.php'));
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
          header('Location: ' . ($adminBaseUrl !== '' ? ($adminBaseUrl . '/dashboard.php') : 'dashboard.php'));
          exit();
        } elseif(hash_equals((string)$admin['password'], (string)$password)) {
          // Legacy plain text password - upgrade to bcrypt on successful login
          $newHash = password_hash($password, PASSWORD_BCRYPT);
          $updateQuery = "UPDATE admin SET password = ? WHERE adminID = ?";
          executePreparedUpdate($updateQuery, "si", [$newHash, $admin['adminID']]);

          session_regenerate_id(true);
          $_SESSION['adminID'] = $admin['adminID'];
          $_SESSION['adminUsername'] = $admin['username'];
          header('Location: ' . ($adminBaseUrl !== '' ? ($adminBaseUrl . '/dashboard.php') : 'dashboard.php'));
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
// Compute app base path (URL path up to but not including '/backend/') for linking shared assets like CSS
$scriptName = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? ''));
$backendPos = strpos($scriptName, '/backend/');
$appBasePath = '';
if ($backendPos !== false) {
  $appBasePath = substr($scriptName, 0, $backendPos);
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
  <link rel="stylesheet" href="<?php echo htmlspecialchars($appBasePath); ?>/frontend/css/admin-style.css?v=<?php echo time(); ?>">
</head>
<body class="auth-body admin-auth">
  <div class="auth-container">
    <div class="auth-card">
      <div class="text-center mb-4">
        <img src="../../frontend/images/logo.png" alt="Bakery Logo" class="auth-logo mb-3">
        <h2 class="auth-title mb-1">Admin Login</h2>
        <div class="auth-subtitle">Sign in to manage the bakery</div>
      </div>
    
    <?php if($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST" class="auth-form">
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input type="text" class="form-control" name="username" placeholder="Username" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" class="form-control" name="password" placeholder="Password" required>
      </div>
      <div class="mb-3 d-flex justify-content-between align-items-center">
        <a href="#" class="auth-link">Forgot Password?</a>
        <label class="text-muted">
          <input type="checkbox" checked> Keep me signed in
        </label>
      </div>
      <button type="submit" class="btn btn-brown w-100 mb-3">LOGIN</button>
      <div class="text-center">
        <a href="../index.php" class="auth-link"><i class="fas fa-home"></i> BACK HOME</a>
      </div>
    </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

