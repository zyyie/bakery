<?php
$scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$redirectUri = $scheme . '://' . $host . '/backend/api/social-login.php';
echo 'Your redirect URI is: ' . $redirectUri . PHP_EOL;
echo 'For local development, this should be: http://localhost/backend/api/social-login.php' . PHP_EOL;
?>
