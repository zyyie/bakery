<?php

  $dbhost = "localhost";
	$dbuser = "root";
	$dbpass = "";
	$db = "DB_bakery";

	$conn = new mysqli($dbhost, $dbuser, $dbpass, $db);
$GLOBALS['db_last_error'] = null;
	
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
      $GLOBALS['db_last_error'] = $e->getMessage();
      error_log("Prepare failed: " . $e->getMessage());
      return false;
    }
    
    if(!$stmt){
      $GLOBALS['db_last_error'] = $conn->error;
      error_log("Prepare failed: " . $conn->error);
      return false;
    }
    
    if(!empty($params)){
      $stmt->bind_param($types, ...$params);
    }
    
    if(!$stmt->execute()){
      $GLOBALS['db_last_error'] = $stmt->error;
      error_log("Execute failed: " . $stmt->error);
      $stmt->close();
      return false;
    }
    
    $result = $stmt->get_result();
    $stmt->close();
    $GLOBALS['db_last_error'] = null;
    return $result;
  }

  // Helper function for INSERT/UPDATE/DELETE with prepared statements
  function executePreparedUpdate($query, $types = "", $params = []){
    $conn = $GLOBALS['conn'];
    try {
      $stmt = $conn->prepare($query);
    } catch (mysqli_sql_exception $e) {
      $GLOBALS['db_last_error'] = $e->getMessage();
      error_log("Prepare failed: " . $e->getMessage());
      return false;
    }
    
    if(!$stmt){
      $GLOBALS['db_last_error'] = $conn->error;
      error_log("Prepare failed: " . $conn->error);
      return false;
    }
    
    if(!empty($params)){
      $stmt->bind_param($types, ...$params);
    }
    
    if(!$stmt->execute()){
      $GLOBALS['db_last_error'] = $stmt->error;
      error_log("Execute failed: " . $stmt->error);
      $stmt->close();
      return false;
    }
    
    $affected = $stmt->affected_rows;
    $stmt->close();
    $GLOBALS['db_last_error'] = null;
    return $affected;
  }

?>

