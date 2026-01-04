<?php
require_once __DIR__ . '/includes/bootstrap.php';

unset($_SESSION['adminID'], $_SESSION['adminUsername'], $_SESSION['admin_last_regeneration']);

if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}

header("Location: login.php");
exit();
?>

