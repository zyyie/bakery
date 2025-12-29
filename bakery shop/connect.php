<?php

  $dbhost = "localhost";
	$dbuser = "root";
	$dbpass = "";
	$db = "DB_bakery";

	$conn = new mysqli($dbhost, $dbuser, $dbpass, $db);
	
	if($conn->connect_error)
	{
		die("Connection Failed: " . $conn->connect_error);
	}

  // Legacy function for backward compatibility (deprecated - use prepared statements instead)
  function executeQuery($query){
    $conn = $GLOBALS['conn'];
    return mysqli_query($conn, $query);
  }

  // Secure prepared statement helper function
  function executePreparedQuery($query, $types = "", $params = []){
    $conn = $GLOBALS['conn'];
    try {
      $stmt = $conn->prepare($query);
    } catch (mysqli_sql_exception $e) {
      error_log("Prepare failed: " . $e->getMessage());
      return false;
    }
    
    if(!$stmt){
      error_log("Prepare failed: " . $conn->error);
      return false;
    }
    
    if(!empty($params)){
      $stmt->bind_param($types, ...$params);
    }
    
    if(!$stmt->execute()){
      error_log("Execute failed: " . $stmt->error);
      $stmt->close();
      return false;
    }
    
    $result = $stmt->get_result();
    $stmt->close();
    return $result;
  }

  // Helper function for INSERT/UPDATE/DELETE with prepared statements
  function executePreparedUpdate($query, $types = "", $params = []){
    $conn = $GLOBALS['conn'];
    try {
      $stmt = $conn->prepare($query);
    } catch (mysqli_sql_exception $e) {
      error_log("Prepare failed: " . $e->getMessage());
      return false;
    }
    
    if(!$stmt){
      error_log("Prepare failed: " . $conn->error);
      return false;
    }
    
    if(!empty($params)){
      $stmt->bind_param($types, ...$params);
    }
    
    if(!$stmt->execute()){
      error_log("Execute failed: " . $stmt->error);
      $stmt->close();
      return false;
    }
    
    $affected = $stmt->affected_rows;
    $stmt->close();
    return $affected;
  }

?>

