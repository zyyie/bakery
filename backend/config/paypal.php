<?php

$localSecrets = [];
$localSecretsPath = __DIR__ . '/secrets.local.php';
if (file_exists($localSecretsPath)) {
    $maybe = require $localSecretsPath;
    if (is_array($maybe)) {
        $localSecrets = $maybe;
    }
}

$clientId = getenv('PAYPAL_CLIENT_ID');
$clientSecret = getenv('PAYPAL_CLIENT_SECRET');

return [
    'mode' => getenv('PAYPAL_MODE') ?: 'sandbox', // Change to 'live' for production
    'client_id' => ($localSecrets['paypal_client_id'] ?? null)
        ?: (($clientId && trim($clientId) !== '') ? $clientId : 'YOUR_PAYPAL_CLIENT_ID_HERE'),
    'client_secret' => ($localSecrets['paypal_client_secret'] ?? null)
        ?: (($clientSecret && trim($clientSecret) !== '') ? $clientSecret : 'YOUR_PAYPAL_CLIENT_SECRET_HERE'),
    'currency' => getenv('PAYPAL_CURRENCY') ?: 'PHP',
];

/*
To get PayPal Sandbox credentials:
1. Go to https://developer.paypal.com/
2. Log in or create an account
3. Go to Dashboard > Applications & Credentials
4. Create a new app (select "Merchant" type)
5. Copy the Client ID from the Sandbox section
6. Generate and copy the Client Secret
7. Replace the values above with your actual credentials
*/
