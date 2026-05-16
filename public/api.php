<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/Repository.php';
require_once __DIR__ . '/../src/Database.php';

header('Content-Type: application/json');
$path = $_GET['path'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET' && $path === 'stats') {
        echo json_encode(Repository::stats());
        exit;
    }

    if ($method === 'GET' && $path === 'promises') {
        echo json_encode(Repository::latestPromises(50));
        exit;
    }

    if ($method === 'GET' && $path === 'submissions') {
        echo json_encode(Repository::latestSubmissions(50));
        exit;
    }

    if ($method === 'POST' && $path === 'submissions') {
        $payload = json_decode(file_get_contents('php://input'), true);
        if (!is_array($payload)) {
            throw new RuntimeException('Invalid JSON payload');
        }
        $pdo = Database::connection();
        $stmt = $pdo->prepare('INSERT INTO public_submissions (title, leader_name, state, description, source_link, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([
            trim((string)($payload['title'] ?? '')),
            trim((string)($payload['leader_name'] ?? '')),
            trim((string)($payload['state'] ?? '')),
            trim((string)($payload['description'] ?? '')),
            trim((string)($payload['source_link'] ?? '')),
            'pending',
        ]);
        echo json_encode(['ok' => true, 'message' => 'Submission saved', 'status' => 'pending']);
        exit;
    }

    http_response_code(404);
    echo json_encode(['error' => 'Not found']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
