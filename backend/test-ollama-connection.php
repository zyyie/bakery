<?php
echo "=== Testing Ollama Connection ===\n\n";

// Test 1: Check if Ollama is running
echo "1. Testing Ollama API connection...\n";
$ch = curl_init('http://127.0.0.1:11434/api/tags');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);

$response = curl_exec($ch);
$errno = curl_errno($ch);
$error = curl_error($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($errno) {
    echo "❌ ERROR: Cannot connect to Ollama\n";
    echo "Error: $error\n";
    echo "Make sure Ollama is running with: ollama serve\n";
    exit(1);
}

if ($status !== 200) {
    echo "❌ ERROR: Ollama returned status $status\n";
    echo "Response: $response\n";
    exit(1);
}

echo "✅ Ollama is running!\n";

// Test 2: Check available models
$data = json_decode($response, true);
if (isset($data['models'])) {
    echo "\n2. Available models:\n";
    foreach ($data['models'] as $model) {
        echo "   - " . $model['name'] . "\n";
    }
} else {
    echo "\n⚠️  No models found. You may need to pull a model:\n";
    echo "   ollama pull llama3.2:3b\n";
}

// Test 3: Test generate API
echo "\n3. Testing generate API...\n";
$testPayload = [
    'model' => 'llama3.2:3b',
    'prompt' => 'You are a helpful assistant. User: Say "Hello World". Assistant:',
    'stream' => false,
    'options' => [
        'temperature' => 0.7,
        'num_predict' => 10,
        'num_ctx' => 2048,
        'num_batch' => 512
    ]
];

$ch = curl_init('http://127.0.0.1:11434/api/generate');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testPayload));
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);

$response = curl_exec($ch);
$errno = curl_errno($ch);
$error = curl_error($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($errno) {
    echo "❌ ERROR: Generate API failed\n";
    echo "Error: $error\n";
    exit(1);
}

if ($status !== 200) {
    echo "❌ ERROR: Generate API returned status $status\n";
    echo "Response: $response\n";
    exit(1);
}

$out = json_decode($response, true);
if (isset($out['response'])) {
    echo "✅ Generate API working!\n";
    echo "Response: " . trim($out['response']) . "\n";
} else {
    echo "❌ ERROR: Unexpected response format\n";
    echo "Response: $response\n";
}

echo "\n=== Test Complete ===\n";
echo "If all tests passed, the chatbot should work!\n";
?>
