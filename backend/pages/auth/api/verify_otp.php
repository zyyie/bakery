<?php
require_once __DIR__ . '/../../../includes/bootstrap.php';

header('Content-Type: application/json');

try {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
  }

  $raw = file_get_contents('php://input');
  $data = json_decode($raw, true);
  if (!is_array($data)) { $data = []; }

  $phone = isset($data['phone']) ? trim((string)$data['phone']) : '';
  $code  = isset($data['code']) ? trim((string)$data['code']) : '';

  if ($phone === '' || $code === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Phone and code are required']);
    exit;
  }

  // Validate phone format (same as sender)
  $digits = preg_replace('/\D+/', '', $phone);
  if (strlen($digits) < 7 || strlen($digits) > 15) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid phone number']);
    exit;
  }

  // Validate code format: 6 digits
  if (!preg_match('/^\d{6}$/', $code)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid OTP format']);
    exit;
  }

  if (!isset($_SESSION['otp'][$digits])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'No OTP requested for this phone']);
    exit;
  }

  $entry = $_SESSION['otp'][$digits];
  $now = time();

  if (isset($entry['expires_at']) && $now > (int)$entry['expires_at']) {
    unset($_SESSION['otp'][$digits]);
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'OTP expired. Please request a new one.']);
    exit;
  }

  $attempts = isset($entry['attempts']) ? (int)$entry['attempts'] : 0;
  $maxAttempts = isset($entry['max_attempts']) ? (int)$entry['max_attempts'] : 5;
  if ($attempts >= $maxAttempts) {
    unset($_SESSION['otp'][$digits]);
    http_response_code(429);
    echo json_encode(['ok' => false, 'error' => 'Too many attempts. Please request a new OTP.']);
    exit;
  }

  if (!isset($entry['code'])) {
    unset($_SESSION['otp'][$digits]);
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'OTP not available. Please request again.']);
    exit;
  }

  if (hash_equals((string)$entry['code'], $code)) {
    // Success: mark verified and cleanup OTP entry
    if (!isset($_SESSION['otp_verified'])) { $_SESSION['otp_verified'] = []; }
    $_SESSION['otp_verified'][$digits] = true;
    unset($_SESSION['otp'][$digits]);

    echo json_encode(['ok' => true, 'message' => 'OTP verified']);
    exit;
  } else {
    // Increment attempts and persist
    $entry['attempts'] = $attempts + 1;
    $_SESSION['otp'][$digits] = $entry;

    http_response_code(400);
    $remaining = max(0, $maxAttempts - $entry['attempts']);
    echo json_encode(['ok' => false, 'error' => 'Incorrect code', 'remaining_attempts' => $remaining]);
    exit;
  }
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'Server error']);
}
