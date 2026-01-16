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
  if ($phone === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Phone is required']);
    exit;
  }

  // Basic phone validation: allow +, digits, spaces, dashes, parentheses. 7-20 characters after stripping non-digits
  $digits = preg_replace('/\D+/', '', $phone);
  if (strlen($digits) < 7 || strlen($digits) > 15) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid phone number']);
    exit;
  }

  // Rate limit sending per phone: 1 per 30 seconds
  if (!isset($_SESSION['otp'])) { $_SESSION['otp'] = []; }
  $now = time();
  // Use normalized digits-only key to avoid format mismatches
  $key = $digits;
  $entry = isset($_SESSION['otp'][$key]) ? $_SESSION['otp'][$key] : null;
  if ($entry && isset($entry['sent_at']) && ($now - (int)$entry['sent_at']) < 30) {
    http_response_code(429);
    echo json_encode(['ok' => false, 'error' => 'Please wait before requesting another OTP']);
    exit;
  }

  // Generate 6-digit code
  $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
  $ttl = 5 * 60; // 5 minutes

  $_SESSION['otp'][$key] = [
    'code' => $code,
    'sent_at' => $now,
    'expires_at' => $now + $ttl,
    'attempts' => 0,
    'max_attempts' => 5,
  ];

  // At this point, integrate with SMS provider to send $code to $phone.
  // For development, we do not actually send SMS. You may log the code if needed.

  echo json_encode(['ok' => true, 'message' => 'OTP sent']);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'Server error']);
}
