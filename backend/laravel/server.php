<?php

// Minimal Alpha-01 Backend Handler (Server.php)
// This file handles internal 8000 port requests before Laravel is fully bootstrapped for Alpha-01.

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

function orchard_db(): ?PDO
{
    $host = getenv('DB_HOST') ?: 'db';
    $database = getenv('DB_DATABASE') ?: 'orchard_ai';
    $user = getenv('DB_USERNAME') ?: 'orchard_user';
    $pass = getenv('DB_PASSWORD') ?: 'orchard_pass';

    try {
        return new PDO(
            "mysql:host={$host};dbname={$database};charset=utf8mb4",
            $user,
            $pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    } catch (Throwable $e) {
        return null;
    }
}

// API Route: /api/winnowing/stream
if ($uri === '/api/winnowing/stream') {
    require_once __DIR__ . '/app/Services/BedrockScoringAgent.php';
    $agent = new \App\Services\BedrockScoringAgent();

    $posts = [];
    $db = orchard_db();
    if ($db) {
        $stmt = $db->query("SELECT content, score, verdict FROM trees ORDER BY created_at DESC LIMIT 12");
        $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        foreach ($rows as $row) {
            $text = (string)($row['content'] ?? '');
            $scoreData = $agent->score($text);
            $posts[] = [
                'text' => $text,
                'utility' => round($scoreData['utility'], 2),
                'privacy' => round($scoreData['privacy'], 2),
                'co2' => round($scoreData['sustainability'], 2),
                'score' => round((float)($row['score'] ?? $scoreData['total_score']), 2),
                'verdict' => $row['verdict'] ?? ($scoreData['total_score'] > 0.7 ? 'wheat' : 'chaff')
            ];
        }
    }

    if (count($posts) === 0) {
        $sampleTexts = [
            "Q4 sales up 22% due to new pricing strategy. Actionable insight.",
            "Just had coffee. Non-actionable status update.",
            "Patient ID 4432 has hypertension",
            "Breaking: Orchard.ai winnowing hits 97% this hour",
            "Just finished sprint planning. Wheat: 3 new features."
        ];
        foreach ($sampleTexts as $text) {
            $scoreData = $agent->score($text);
            $score = round($scoreData['total_score'], 2);
            $verdict = $score > 0.7 ? 'wheat' : 'chaff';
            $posts[] = [
                'text' => $text,
                'utility' => round($scoreData['utility'], 2),
                'privacy' => round($scoreData['privacy'], 2),
                'co2' => round($scoreData['sustainability'], 2),
                'score' => $score,
                'verdict' => $verdict
            ];
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode(['posts' => $posts]);
    exit;
}

if ($uri === '/api/ai/status') {
    require_once __DIR__ . '/app/Services/BedrockScoringAgent.php';
    $agent = new \App\Services\BedrockScoringAgent();
    header('Content-Type: application/json');
    echo json_encode($agent->providerStatus());
    exit;
}

// Route: /api/winnowing/dispatch
// Dispatches a winnowing task to RabbitMQ for asynchronous processing by the Python workers.
if ($uri === '/api/winnowing/dispatch') {
    $content = trim((string)($_GET['content'] ?? ''));
    if ($content === '') {
        http_response_code(422);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'content query parameter is required']);
        exit;
    }
    
    // RabbitMQ Management API credentials (guest:guest is default)
    $ch = curl_init('http://rabbitmq:15672/api/exchanges/%2f/amq.default/publish');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, "guest:guest");
    curl_setopt($ch, CURLOPT_POST, true);
    
    $payload = json_encode([
        "routing_key" => "orchard_fanout",
        "properties" => (object)[],
        "payload" => json_encode(["text" => $content]),
        "payload_encoding" => "string"
    ]);
    
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    header('Content-Type: application/json');
    $decoded = json_decode($response, true);
    if ($httpCode === 200 && (($decoded['routed'] ?? false) === true)) {
        echo json_encode(['status' => 'success', 'message' => 'Winnowing task dispatched for: ' . $content, 'routed' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to dispatch task', 'rabbitmq_response' => $decoded]);
    }
    exit;
}

// Route: /api/ai/chat
// Provides a chat response from the Orchard.ai Server Assistant.
if ($uri === '/api/ai/chat') {
    require_once __DIR__ . '/app/Services/BedrockScoringAgent.php';
    $agent = new \App\Services\BedrockScoringAgent();
    $rawBody = file_get_contents('php://input');
    $input = json_decode($rawBody, true);
    $message = $input['message'] ?? ($_POST['message'] ?? ($_GET['message'] ?? ''));
    
    $msg = strtolower(trim((string)$message));
    if ($msg === '') {
        http_response_code(422);
        header('Content-Type: application/json');
        echo json_encode(['reply' => 'Send a message and I will help with Orchard operations.']);
        exit;
    }
    $reply = $agent->chat((string) $message);
    
    header('Content-Type: application/json');
    echo json_encode(['reply' => $reply]);
    exit;
}

// Route: /api/compliance/mitigate
// Logs risk mitigation actions for ISO 9001 auditability.
if ($uri === '/api/compliance/mitigate') {
    $input = json_decode(file_get_contents('php://input'), true);
    $riskId = $input['riskId'] ?? 'unknown';
    $strategy = $input['strategy'] ?? 'none';
    
    // Log the mitigation action (Server-side logging)
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'event' => 'RISK_MITIGATION_APPLIED',
        'risk_id' => $riskId,
        'strategy' => $strategy,
        'status' => 'ACCEPTED'
    ];
    
    // For Alpha-01, we output this to a persistent compliance log file
    file_put_contents(__DIR__ . '/compliance_audit.log', json_encode($logEntry) . PHP_EOL, FILE_APPEND);
    
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'message' => "Mitigation for Risk #$riskId officially logged.", 'audit_id' => uniqid()]);
    exit;
}

// Route: /api/storage/files
// Lists all files in the storage directory.
if ($uri === '/api/storage/files') {
    $storageDir = __DIR__ . '/storage';
    $files = [];
    if (is_dir($storageDir)) {
        $items = array_diff(scandir($storageDir), ['.', '..']);
        foreach ($items as $item) {
            $files[] = [
                'name' => $item,
                'size' => filesize($storageDir . '/' . $item),
                'type' => pathinfo($item, PATHINFO_EXTENSION)
            ];
        }
    }
    header('Content-Type: application/json');
    echo json_encode(['files' => $files]);
    exit;
}

// Route: /api/storage/upload
// Handles multi-part file uploads to the storage directory.
if ($uri === '/api/storage/upload' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $storageDir = __DIR__ . '/storage';
    if (!isset($_FILES['file'])) {
        http_response_code(400);
        echo json_encode(['error' => 'No file uploaded']);
        exit;
    }

    $file = $_FILES['file'];
    $name = basename($file['name']);
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $allowed = ['md', 'pdf', 'zip', 'docx', 'sql', 'txt'];

    if (!in_array($ext, $allowed)) {
        http_response_code(400);
        echo json_encode(['error' => 'Unsupported file type: ' . $ext]);
        exit;
    }

    if (move_uploaded_file($file['tmp_name'], $storageDir . '/' . $name)) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'name' => $name]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to move uploaded file']);
    }
    exit;
}

// Route: /api/storage/delete
// Deletes a specific file from the storage directory.
if ($uri === '/api/storage/delete') {
    $name = $_GET['name'] ?? '';
    $storageDir = __DIR__ . '/storage';
    $filePath = $storageDir . '/' . basename($name);

    if ($name && file_exists($filePath)) {
        unlink($filePath);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => "File $name deleted."]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'File not found']);
    }
    exit;
}

// Route: /api/lessons/list
// Returns the list of lessons learned from the persistent JSON store.
if ($uri === '/api/lessons/list') {
    $file = __DIR__ . '/lessons_learned.json';
    $lessons = [];
    if (file_exists($file)) {
        $lessons = json_decode(file_get_contents($file), true) ?: [];
    }
    header('Content-Type: application/json');
    echo json_encode(['lessons' => $lessons]);
    exit;
}

// Route: /api/lessons/log
// Persists a new lesson learned to the JSON store.
if ($uri === '/api/lessons/log' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $text = $input['lesson'] ?? '';
    
    if (!$text) {
        http_response_code(400);
        echo json_encode(['error' => 'Lesson text is required']);
        exit;
    }

    $file = __DIR__ . '/lessons_learned.json';
    $lessons = [];
    if (file_exists($file)) {
        $lessons = json_decode(file_get_contents($file), true) ?: [];
    }
    
    $newEntry = [
        'text' => $text,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    array_unshift($lessons, $newEntry); // Newest first
    
    file_put_contents($file, json_encode($lessons, JSON_PRETTY_PRINT));
    
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'lesson' => $newEntry]);
    exit;
}

// Fallback: 404
http_response_code(404);
echo json_encode(['error' => 'Orchard.ai Alpha-01: Resource not found at ' . $uri]);
