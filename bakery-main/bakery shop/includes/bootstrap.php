<?php

require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/session.php';

function is_https() {
    return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect($path) {
    header('Location: ' . $path);
    exit();
}

function current_base_url() {
    $scheme = is_https() ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $dir = rtrim(dirname($_SERVER['PHP_SELF'] ?? '/'), '/\\');
    return $scheme . '://' . $host . ($dir ? '/' . $dir : '');
}

function set_app_cookie($name, $value, $expires) {
    $options = [
        'expires' => $expires,
        'path' => '/',
        'secure' => is_https(),
        'httponly' => true,
        'samesite' => 'Strict'
    ];

    setcookie($name, $value, $options);
}

function getProductImage($itemImage, $packageName, $defaultSize = '300x200') {
    if ($itemImage && file_exists(__DIR__ . '/../uploads/' . $itemImage)) {
        return 'uploads/' . $itemImage;
    }
    
    // Load image mappings from JSON file
    $jsonPath = __DIR__ . '/../bread_images/product_images.json';
    $imageMap = [];
    if (file_exists($jsonPath)) {
        $jsonContent = file_get_contents($jsonPath);
        $imageMap = json_decode($jsonContent, true) ?? [];
    }
    
    if (isset($imageMap[$packageName])) {
        $imagePath = $imageMap[$packageName];
        if (file_exists(__DIR__ . '/../' . $imagePath)) {
            return $imagePath;
        }
    }
    
    return 'https://via.placeholder.com/' . $defaultSize;
}

if (!isset($_SESSION['userID']) && isset($_COOKIE['remember_token'])) {
    $rawToken = trim((string)$_COOKIE['remember_token']);

    if ($rawToken !== '') {
        $tokenHash = hash('sha256', $rawToken);

        $query = "SELECT userID, fullName, email, remember_token FROM users WHERE token_expires > NOW() AND (remember_token = ? OR remember_token = ?) LIMIT 1";
        $result = executePreparedQuery($query, "ss", [$rawToken, $tokenHash]);

        if ($result && ($user = $result->fetch_assoc())) {
            $_SESSION['userID'] = $user['userID'];
            $_SESSION['fullName'] = $user['fullName'];
            $_SESSION['email'] = $user['email'];

            $newRawToken = bin2hex(random_bytes(32));
            $newTokenHash = hash('sha256', $newRawToken);
            $expiresTs = time() + (86400 * 30);
            $expiryDate = date('Y-m-d H:i:s', $expiresTs);

            set_app_cookie('remember_token', $newRawToken, $expiresTs);

            $update = "UPDATE users SET remember_token = ?, token_expires = ? WHERE userID = ?";
            executePreparedUpdate($update, "ssi", [$newTokenHash, $expiryDate, $user['userID']]);
        } else {
            set_app_cookie('remember_token', '', time() - 3600);
        }
    }
}
