<?php

return [
    'host' => 'smtp.gmail.com',
    'smtp_auth' => true,
    'username' => 'karneekbakery@gmail.com',
    'password' => 'hoqg spvx xzue xkub',
    'smtp_secure' => 'tls',
    'port' => 587,
    'charset' => 'UTF-8',
    'debug' => 0, // Set to 2 for debugging email issues (0 = off, 1 = client, 2 = client and server, 3 = verbose) - Set to 2 temporarily to debug
    'from_email' => 'karneekbakery@gmail.com',
    'from_name' => 'KARNEEK Bakery',
    // Base URL for reset links (use IP address for mobile device access on same network)
    // IMPORTANT: Set this to your computer's IP address for mobile device access
    // Find your IP: Run 'ipconfig' in CMD and look for IPv4 Address
    // Example: 'http://192.168.18.115/bakery' (use your actual IP)
    // Leave empty to auto-detect (may not work in all cases)
    'base_url' => 'http://10.179.50.237/bakery', // Set your IP address here for mobile access
];
