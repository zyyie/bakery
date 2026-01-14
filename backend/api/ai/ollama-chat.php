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

$system = "You are KARNEEK Bakery's helpful assistant. You are here to help customers with questions about our bakery products, services, and ordering process.

Your expertise includes:
- Our fresh breads, cakes, pastries, and specialty items
- Ingredients, allergens, and dietary information
- Pricing, promotions, and special offers
- Ordering process and payment methods
- Store hours, location, and pickup information
- Custom orders and special occasion cakes
- Website navigation and account help

Important Guidelines:
- Focus ONLY on KARNEEK Bakery and baking-related topics
- For pickup orders: Customers can place orders online and pickup at our store
- Do NOT mention delivery options - we only offer pickup service
- Keep answers friendly, helpful, and concise
- If you don't know something, suggest contacting the store directly
- Always be professional and bakery-focused

If someone asks about delivery, politely explain that we offer pickup service only and guide them to our ordering process.";
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

// Use generate API for better reliability with smaller model
$prompt = $system . "\n\nUser: " . $message . "\nAssistant: ";
$payload = [
    'model' => 'llama3.2:3b',
    'prompt' => $prompt,
    'stream' => false,
    'options' => [
        'temperature' => 0.7,
        'num_predict' => 100,  // Reduced response length
        'top_p' => 0.9,
        'repeat_penalty' => 1.1,
        'num_ctx' => 2048,  // Reduced context
        'num_batch' => 512
    ]
];

$ch = curl_init('http://127.0.0.1:11434/api/generate');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);  // Increased timeout

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
