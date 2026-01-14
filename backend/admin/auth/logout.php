<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';

$scriptName = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? ''));
$adminBaseUrl = '';
$adminPos = strpos($scriptName, '/admin/');
if ($adminPos !== false) {
    $adminBaseUrl = substr($scriptName, 0, $adminPos) . '/admin';
}

unset($_SESSION['adminID'], $_SESSION['adminUsername'], $_SESSION['admin_last_regeneration']);

if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}

header('Location: ' . ($adminBaseUrl !== '' ? ($adminBaseUrl . '/login.php') : 'login.php'));
exit();
?>

