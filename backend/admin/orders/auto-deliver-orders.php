<?php
/**
 * Auto-Deliver Orders Script
 * This script should be run daily via cron job to automatically mark orders as "Delivered"
 * when their delivery date arrives.
 * 
 * Setup cron job (runs daily at midnight):
 * 0 0 * * * /usr/bin/php /path/to/backend/admin/auto-deliver-orders.php
 * 
 * Or for Windows Task Scheduler, run this file daily
 */

require_once dirname(__DIR__) . '/includes/bootstrap.php';

// Only allow execution from command line or with admin authentication
if(php_sapi_name() !== 'cli' && !isset($_GET['key']) || (isset($_GET['key']) && $_GET['key'] !== 'auto_deliver_secret_key_2026')){
  die('Unauthorized access');
}

$today = date('Y-m-d');

// Find all orders that:
// 1. Have a delivery date set (not NULL)
// 2. Delivery date is today or in the past
// 3. Status is "On The Way" (not already delivered or cancelled)
$query = "UPDATE orders 
          SET orderStatus = 'Delivered' 
          WHERE deliveryDate IS NOT NULL 
          AND DATE(deliveryDate) <= ? 
          AND orderStatus = 'On The Way'";

$result = executePreparedUpdate($query, "s", [$today]);

if($result !== false){
  $affectedRows = mysqli_affected_rows($GLOBALS['conn']);
  echo "Successfully updated $affectedRows order(s) to Delivered status.\n";
  error_log("Auto-deliver script: Updated $affectedRows order(s) to Delivered on $today");
} else {
  echo "Error updating orders.\n";
  error_log("Auto-deliver script: Error updating orders on $today");
}
?>

