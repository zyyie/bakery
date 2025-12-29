<?php

require_once __DIR__ . '/../../connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function adminIsLoggedIn() {
    return isset($_SESSION['adminID']);
}

function requireAdminLogin() {
    if (!adminIsLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function adminRegenerateSession() {
    if (!isset($_SESSION['admin_last_regeneration']) || (time() - $_SESSION['admin_last_regeneration']) > 300) {
        $_SESSION['admin_last_regeneration'] = time();
        session_regenerate_id(true);
    }
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
