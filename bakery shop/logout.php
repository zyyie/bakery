<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (isset($_COOKIE['remember_token'])) {
    $rawToken = trim((string)$_COOKIE['remember_token']);
    if ($rawToken !== '') {
        $tokenHash = hash('sha256', $rawToken);
        executePreparedUpdate(
            "UPDATE users SET remember_token = NULL, token_expires = NULL WHERE remember_token = ? OR remember_token = ?",
            "ss",
            [$rawToken, $tokenHash]
        );
    }

    set_app_cookie('remember_token', '', time() - 3600);
}

if (isset($_SESSION['userID'])) {
    executePreparedUpdate(
        "UPDATE users SET remember_token = NULL, token_expires = NULL WHERE userID = ?",
        "i",
        [intval($_SESSION['userID'])]
    );
}

$_SESSION = [];
if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}

header('Location: index.php');
exit();
?>

