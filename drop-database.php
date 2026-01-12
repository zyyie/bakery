<?php
/**
 * Script to drop database (use with caution!)
 * Run this file directly: http://localhost/bakery/drop-database.php
 */

require_once __DIR__ . '/backend/connect.php';

// Database name
$dbname = 'db_bakery';

// Connect without selecting database
$conn = new mysqli('localhost', 'root', '');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Drop database
$sql = "DROP DATABASE IF EXISTS `$dbname`";

if ($conn->query($sql) === TRUE) {
    echo "<h2 style='color: green;'>Success!</h2>";
    echo "<p>Database '$dbname' has been dropped successfully.</p>";
} else {
    echo "<h2 style='color: red;'>Error!</h2>";
    echo "<p>Error dropping database: " . $conn->error . "</p>";
}

$conn->close();
?>

