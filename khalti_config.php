<?php
// khalti_config.php - Khalti Payment Gateway Configuration

// Khalti API Configuration
define('KHALTI_MODE', 'test'); // 'test' or 'live'
define('KHALTI_DEMO_MODE', false); // Set to false when you have real keys

// IMPORTANT: Get these keys from https://test-admin.khalti.com → Settings → Keys
// Even in test mode, the key is called "live_secret_key"
// Example format: live_secret_key_68791341fdd94846a146f0457ff7b455

// Test/Sandbox credentials - REPLACE WITH YOUR ACTUAL KEYS
define('KHALTI_TEST_SECRET_KEY', '9c9e3e0ac36046ebb2164f77aae3c876');
define('KHALTI_TEST_PUBLIC_KEY', '820a072c9df44be7a825770d03e77d29');

// Live/Production credentials (get from khalti.com when ready for production)
define('KHALTI_LIVE_SECRET_KEY', '9c9e3e0ac36046ebb2164f77aae3c876');
define('KHALTI_LIVE_PUBLIC_KEY', '820a072c9df44be7a825770d03e77d29');

// Get active keys based on mode
function get_khalti_secret_key() {
    return (KHALTI_MODE === 'live') ? KHALTI_LIVE_SECRET_KEY : KHALTI_TEST_SECRET_KEY;
}

function get_khalti_public_key() {
    return (KHALTI_MODE === 'live') ? KHALTI_LIVE_PUBLIC_KEY : KHALTI_TEST_PUBLIC_KEY;
}

// API Endpoints
function get_khalti_api_url() {
    if (KHALTI_MODE === 'live') {
        return 'https://khalti.com/api/v2/';
    }
    return 'https://dev.khalti.com/api/v2/';
}

// Helper function to make Khalti API calls
function khalti_api_request($endpoint, $data = [], $method = 'POST') {
    $url = get_khalti_api_url() . $endpoint;
    $secret_key = get_khalti_secret_key();
    
    $curl = curl_init();
    
    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => [
            'Authorization: Key ' . $secret_key,
            'Content-Type: application/json',
        ],
    ];
    
    if (!empty($data) && $method === 'POST') {
        $options[CURLOPT_POSTFIELDS] = json_encode($data);
    }
    
    curl_setopt_array($curl, $options);
    
    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);
    
    curl_close($curl);
    
    if ($error) {
        return [
            'success' => false,
            'error' => $error,
            'http_code' => $http_code
        ];
    }
    
    $decoded = json_decode($response, true);
    
    return [
        'success' => ($http_code >= 200 && $http_code < 300),
        'data' => $decoded,
        'http_code' => $http_code,
        'raw_response' => $response
    ];
}