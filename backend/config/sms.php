<?php

return [
    // SMS Gateway configuration
    'gateway_url' => 'http://10.179.50.3:8080',
    'gateway_username' => 'sms',
    'gateway_password' => '1234567890',
    
    // Base URL for links sent via SMS (e.g., order tracking, order confirmations)
    // This is the address that will be included in SMS messages
    // IMPORTANT: Set this to your server's IP address and port for mobile device access
    // Example: 'http://10.54.202.176:8080' (use your actual IP and port)
    'base_url' => 'http://10.179.50.3:8080',
    
    // Receiving number - SMS messages sent TO this number will be received by the website
    // Format: +63 for Philippines, then number without leading 0
    'receive_number' => '+639493380766', // This is 09493380766 - messages sent TO this number will appear in admin
    
    // Admin phone number - SMS sent to this number will be shown directly to admin
    'admin_phone' => '+639493380766', // Format: +63 for Philippines, then number without leading 0
];
