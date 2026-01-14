<?php
// Enable error reporting for debugging
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

try {
    // Direct config loading without bootstrap to avoid session issues
    $cfg = require __DIR__ . '/../../config/paypal.php';
    
    echo json_encode([
        'ok' => true,
        'mode' => $cfg['mode'],
        'client_id' => $cfg['client_id'],
        'currency' => $cfg['currency'],
    ], JSON_UNESCAPED_SLASHES);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'Configuration error: ' . $e->getMessage()
    ], JSON_UNESCAPED_SLASHES);
}
