<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
requireAdminLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lang = isset($_POST['lang']) ? trim((string)$_POST['lang']) : '';
    $allowed = ['en', 'fil'];

    if (in_array($lang, $allowed, true)) {
        $_SESSION['admin_lang'] = $lang;
    }
}

$redirect = isset($_SERVER['HTTP_REFERER']) ? (string)$_SERVER['HTTP_REFERER'] : 'dashboard.php';
header('Location: ' . $redirect);
exit();
