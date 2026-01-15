<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load shared app bootstrap (DB, sessions, helpers)
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

$__appLogDir = __DIR__ . '/../../logs';
if (!is_dir($__appLogDir)) {
    @mkdir($__appLogDir, 0775, true);
}
ini_set('error_log', $__appLogDir . '/php_errors.log');

set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function (Throwable $e) {
    $msg = "Unhandled exception: " . get_class($e) . ": " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString();
    error_log($msg);

    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=UTF-8');
    }
    echo 'An unexpected error occurred. Please try again later.';
    exit;
});

register_shutdown_function(function () {
    $err = error_get_last();
    if (!$err) {
        return;
    }
    $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
    if (!in_array($err['type'] ?? 0, $fatalTypes, true)) {
        return;
    }

    error_log('Fatal error: ' . ($err['message'] ?? '') . ' in ' . ($err['file'] ?? '') . ':' . ($err['line'] ?? ''));
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=UTF-8');
    }
    echo 'An unexpected error occurred. Please try again later.';
});

function adminIsLoggedIn() {
    return isset($_SESSION['adminID']);
}

function requireAdminLogin() {
    if (!adminIsLoggedIn()) {
        $scriptName = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? ''));
        $adminBaseUrl = '';
        $adminPos = strpos($scriptName, '/admin/');
        if ($adminPos !== false) {
            $adminBaseUrl = substr($scriptName, 0, $adminPos) . '/admin';
        }
        header('Location: ' . ($adminBaseUrl !== '' ? ($adminBaseUrl . '/login.php') : 'login.php'));
        exit();
    }
}

function adminRegenerateSession() {
    if (!isset($_SESSION['admin_last_regeneration']) || (time() - $_SESSION['admin_last_regeneration']) > 300) {
        $_SESSION['admin_last_regeneration'] = time();
        session_regenerate_id(true);
    }

    // Auto-deliver orders: Check and update orders with delivery date today or past
    $today = date('Y-m-d');
    $autoDeliverQuery = "UPDATE orders 
                         SET orderStatus = 'Delivered' 
                         WHERE deliveryDate IS NOT NULL 
                         AND DATE(deliveryDate) <= ? 
                         AND orderStatus = 'On The Way'";
    executePreparedUpdate($autoDeliverQuery, "s", [$today]);
}

function adminGetNotifications() {
    $notifications = [];

    $unread = 0;
    $pending = 0;

    $resUnread = executePreparedQuery("SELECT COUNT(*) AS c FROM enquiries WHERE status = ?", "s", ['Unread']);
    if ($resUnread && ($row = $resUnread->fetch_assoc())) {
        $unread = (int)$row['c'];
    }

    $resPending = executePreparedQuery("SELECT COUNT(*) AS c FROM orders WHERE orderStatus = ?", "s", ['Still Pending']);
    if ($resPending && ($row2 = $resPending->fetch_assoc())) {
        $pending = (int)$row2['c'];
    }

    if ($unread > 0) {
        $notifications[] = [
            'label' => 'Unread Customer Messages',
            'count' => $unread,
            'url' => 'read-enquiry.php'
        ];
    }

    if ($pending > 0) {
        $notifications[] = [
            'label' => 'New Orders',
            'count' => $pending,
            'url' => 'new-orders.php'
        ];
    }

    $total = 0;
    foreach ($notifications as $n) {
        $total += (int)$n['count'];
    }

    return ['total' => $total, 'items' => $notifications];
}
