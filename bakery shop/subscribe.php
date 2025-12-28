<?php
require_once __DIR__ . '/includes/bootstrap.php';

if(isset($_POST['email'])){
  $email = trim($_POST['email']);
  
  // Input validation
  if(!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)){
    // Check if already subscribed using prepared statement
    $checkQuery = "SELECT * FROM subscribers WHERE email = ?";
    $checkResult = executePreparedQuery($checkQuery, "s", [$email]);
    
    if($checkResult && mysqli_num_rows($checkResult) == 0){
      $query = "INSERT INTO subscribers (email) VALUES (?)";
      executePreparedUpdate($query, "s", [$email]);
    }
  }
}

header("Location: index.php");
exit();
