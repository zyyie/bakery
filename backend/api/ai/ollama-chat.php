<?php
require_once __DIR__ . '/../../includes/bootstrap.php';

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

require_once __DIR__ . '/../../config/connect.php';

// Helper: get available columns for a table
function kb_get_columns($conn, $table) {
    $cols = [];
    $res = @mysqli_query($conn, "SHOW COLUMNS FROM `" . mysqli_real_escape_string($conn, $table) . "`");
    if ($res) {
        while ($r = mysqli_fetch_assoc($res)) {
            if (isset($r['Field'])) { $cols[] = $r['Field']; }
        }
        mysqli_free_result($res);
    }
    return $cols;
}

// Helper: pick first matching candidate that exists
function kb_pick($available, $candidates) {
    foreach ($candidates as $c) { if (in_array($c, $available, true)) return $c; }
    return '';
}

$kb = '';
if (isset($conn)) {
    $lines = [];

    // Items/products knowledge
    $itemCols = kb_get_columns($conn, 'items');
    if (!empty($itemCols)) {
        $cName = kb_pick($itemCols, ['name','item_name','product_name','title']);
        $cPrice = kb_pick($itemCols, ['price','item_price','amount','cost']);
        $cDesc = kb_pick($itemCols, ['description','desc','details','content','short_description']);
        $select = [];
        if ($cName !== '') $select[] = "`$cName` AS __name";
        if ($cPrice !== '') $select[] = "`$cPrice` AS __price";
        if ($cDesc !== '') $select[] = "`$cDesc` AS __desc";
        if (!empty($select)) {
            $sql = "SELECT " . implode(', ', $select) . " FROM items ORDER BY 1 DESC LIMIT 50";
            $rs1 = @mysqli_query($conn, $sql);
            if ($rs1) {
                while ($row = mysqli_fetch_assoc($rs1)) {
                    $name = trim((string)($row['__name'] ?? ''));
                    $price = isset($row['__price']) ? (string)$row['__price'] : '';
                    $desc = trim((string)($row['__desc'] ?? ''));
                    if ($desc !== '' && strlen($desc) > 200) { $desc = substr($desc, 0, 200) . '...'; }
                    if ($name !== '') {
                        $lines[] = "Product: $name" . ($price !== '' ? " | Price: $price" : '') . ($desc !== '' ? " | Desc: $desc" : '');
                    }
                }
                mysqli_free_result($rs1);
            }
        }
    }

    // Pages knowledge
    $pageCols = kb_get_columns($conn, 'pages');
    if (!empty($pageCols)) {
        $cTitle = kb_pick($pageCols, ['title','page_title','name']);
        $cContent = kb_pick($pageCols, ['content','page_content','description','body']);
        $select = [];
        if ($cTitle !== '') $select[] = "`$cTitle` AS __title";
        if ($cContent !== '') $select[] = "`$cContent` AS __content";
        if (!empty($select)) {
            $sql = "SELECT " . implode(', ', $select) . " FROM pages ORDER BY 1 DESC LIMIT 20";
            $rs2 = @mysqli_query($conn, $sql);
            if ($rs2) {
                while ($row = mysqli_fetch_assoc($rs2)) {
                    $title = trim((string)($row['__title'] ?? ''));
                    $content = trim((string)($row['__content'] ?? ''));
                    if ($content !== '' && strlen($content) > 300) { $content = substr($content, 0, 300) . '...'; }
                    if ($title !== '') { $lines[] = "Page: $title | $content"; }
                }
                mysqli_free_result($rs2);
            }
        }
    }

    if (!empty($lines)) {
        $kb = implode("\n", $lines);
        if (strlen($kb) > 4000) { $kb = substr($kb, 0, 4000); }
    }
}

$system = "You are KARNEEK Bakery's assistant. Your job is to help with questions strictly about KARNEEK Bakery and bread/bakery topics: products, ingredients, allergens, pricing, promos, ordering, delivery/pickup, store hours, location, custom orders, and site usage.\n\nRules:\n- If the user's request is NOT related to KARNEEK Bakery or bread/baking, politely decline and say you can only help with KARNEEK Bakery and bread-related questions.\n- Keep answers concise and practical.\n- If information is unknown, ask a clarifying question or say you don't have that info and suggest contacting the store.";
if ($kb !== '') {
    $system .= "\n\nKnowledge:\n" . $kb;
}

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
    'model' => 'llama3.1:latest',
    'stream' => false,
    'messages' => $messages,
    'options' => [
        'num_ctx' => 512,  // Very small context
        'temperature' => 0.7,
        'num_batch' => 128,  // Very small batch
        'num_gpu_layers' => 1
    ]
];

$ch = curl_init('http://127.0.0.1:11434/api/generate');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

// Use generate endpoint instead of chat
$prompt = $system . "\n\n" . $message;
$payload = [
    'model' => 'phi3:mini',
    'prompt' => $prompt,
    'stream' => false,
    'options' => [
        'temperature' => 0.7,
        'num_predict' => 150  // Limit response length
    ]
];

curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

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
if (is_array($out) && isset($out['response'])) {
    $reply = (string)$out['response'];
}

$reply = trim($reply);
if ($reply === '') {
    $reply = 'Sorry, I could not generate a response right now.';
}

echo json_encode(['reply' => $reply]);
