<?php
// Simple config that reads from environment variables if set, otherwise fallbacks to placeholders.
// No framework helpers required.

$localSecrets = [];
$localSecretsPath = __DIR__ . '/secrets.local.php';
if (file_exists($localSecretsPath)) {
    $maybe = require $localSecretsPath;
    if (is_array($maybe)) {
        $localSecrets = $maybe;
    }
}

$clientId = getenv('GOOGLE_CLIENT_ID');
$clientSecret = getenv('GOOGLE_CLIENT_SECRET');

return [
    'client_id' => ($localSecrets['google_client_id'] ?? null)
        ?: (($clientId && trim($clientId) !== '') ? $clientId : 'YOUR_GOOGLE_CLIENT_ID_HERE'),
    'client_secret' => ($localSecrets['google_client_secret'] ?? null)
        ?: (($clientSecret && trim($clientSecret) !== '') ? $clientSecret : 'YOUR_GOOGLE_CLIENT_SECRET_HERE'),
    'redirect_uri' => '', // Will be set dynamically based on current URL via get_google_redirect_uri()
    'scopes' => [
        'https://www.googleapis.com/auth/userinfo.email',
        'https://www.googleapis.com/auth/userinfo.profile'
    ]
];

