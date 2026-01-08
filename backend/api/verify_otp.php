<?php
session_start();
header('Content-Type: application/json');

// Get input from POST data (form or JSON)
$input = [];
if (($_SERVER['CONTENT_TYPE'] ?? '') === 'application/json') {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true) ?: [];
} else {
    $input = $_POST;
}

$phone = trim($input['phone'] ?? '');
$code = trim($input['code'] ?? '');

if ($phone === '' || $code === '') {
    echo json_encode(['ok' => false, 'error' => 'Missing phone or code']);
    exit;
}

// Clean phone number format
if (!str_starts_with($phone, '+')) {
    $phone = '+' . $phone;
}

$store = $_SESSION['otp'][$phone] ?? null;
if (!$store) {
    echo json_encode(['ok' => false, 'error' => 'OTP not found. Please request a new one.']);
    exit;
}

if (time() > ($store['exp'] ?? 0)) {
    unset($_SESSION['otp'][$phone]);
    echo json_encode(['ok' => false, 'error' => 'OTP expired. Please request a new one.']);
    exit;
}

if ((string)$code !== (string)($store['code'] ?? '')) {
    echo json_encode(['ok' => false, 'error' => 'Invalid OTP.']);
    exit;
}

// Mark verified and clear to prevent reuse
$_SESSION['otp_verified'] = $_SESSION['otp_verified'] ?? [];
$_SESSION['otp_verified'][$phone] = true;
unset($_SESSION['otp'][$phone]);

echo json_encode(['ok' => true]);
?>
