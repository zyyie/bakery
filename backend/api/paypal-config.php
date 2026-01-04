<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

try {
    // Direct config loading without bootstrap to avoid session issues
    $cfg = require __DIR__ . '/../config/paypal.php';
    
    echo json_encode([
        'mode' => $cfg['mode'],
        'client_id' => $cfg['client_id'],
        'currency' => $cfg['currency'],
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Configuration error: ' . $e->getMessage()
    ]);
}
