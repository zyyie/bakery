<?php
require_once __DIR__ . '/../includes/bootstrap.php';

header('Content-Type: application/json');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

$message = '';
if (is_array($data) && isset($data['message'])) {
    $message = trim((string)$data['message']);
}

if ($message === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Message is required']);
    exit;
}

if (strlen($message) > 2000) {
    http_response_code(400);
    echo json_encode(['error' => 'Message too long']);
    exit;
}

$history = [];
if (is_array($data) && isset($data['history']) && is_array($data['history'])) {
    $history = array_slice($data['history'], -10);
}

$system = "You are KARNEEK Bakery Assistant. Be helpful and concise. You can answer questions about bakery products, ordering, delivery, and store info. If you don't know something, ask a clarifying question.";

$messages = [
    ['role' => 'system', 'content' => $system],
];

foreach ($history as $h) {
    if (!is_array($h)) {
        continue;
    }
    $role = isset($h['role']) ? (string)$h['role'] : '';
    $content = isset($h['content']) ? trim((string)$h['content']) : '';
    if ($content === '') {
        continue;
    }
    if ($role !== 'user' && $role !== 'assistant') {
        continue;
    }
    if (strlen($content) > 2000) {
        $content = substr($content, 0, 2000);
    }
    $messages[] = ['role' => $role, 'content' => $content];
}

$messages[] = ['role' => 'user', 'content' => $message];

$payload = [
    'model' => 'llama3.1',
    'stream' => false,
    'messages' => $messages,
];

$ch = curl_init('http://localhost:11434/api/chat');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
curl_setopt($ch, CURLOPT_TIMEOUT, 25);

$response = curl_exec($ch);
$errno = curl_errno($ch);
$error = curl_error($ch);
$status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($errno) {
    http_response_code(502);
    echo json_encode(['error' => 'Ollama is not reachable. Make sure it is running on this machine.', 'details' => $error]);
    exit;
}

if ($status < 200 || $status >= 300) {
    http_response_code(502);
    echo json_encode(['error' => 'Ollama returned an error', 'status' => $status, 'details' => $response]);
    exit;
}

$out = json_decode((string)$response, true);
$reply = '';
if (is_array($out) && isset($out['message']) && is_array($out['message']) && isset($out['message']['content'])) {
    $reply = (string)$out['message']['content'];
}

$reply = trim($reply);
if ($reply === '') {
    $reply = 'Sorry, I could not generate a response right now.';
}

echo json_encode(['reply' => $reply]);
