<?php
include("connect.php");

if(isset($_POST['email'])){
  $email = $_POST['email'];
  
  // Check if already subscribed
  $checkQuery = "SELECT * FROM subscribers WHERE email = '$email'";
  $checkResult = executeQuery($checkQuery);
  
  if(mysqli_num_rows($checkResult) == 0){
    $query = "INSERT INTO subscribers (email) VALUES ('$email')";
    executeQuery($query);
  }
}

header("Location: index.php");
exit();
?>

