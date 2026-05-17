<?php
/**
 * NetaTrack India — Public AI API endpoints
 * /api/ai/summarise   — get AI summary for a leader
 * /api/ai/factcheck   — fact check a claim
 * /api/ai/translate   — translate text to Hindi
 * /api/ai/chat        — chat with AI about a leader
 * Rate limited by session to avoid abuse.
 */

use App\AI\AIRouter;

function api_ai_rate_limit(string $action, int $limit = 10): bool {
    $key = "ai_rate_{$action}_" . (session_id() ?: 'anon');
    $count = (int)($_SESSION[$key] ?? 0);
    if ($count >= $limit) return false;
    $_SESSION[$key] = $count + 1;
    return true;
}

function api_ai_json($data, int $code = 200): never {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

$ai     = AIRouter::make();
$action = $router->segment(3); // /api/ai/{action}
$body   = json_decode(file_get_contents('php://input'), true) ?? [];

if (!$ai->isAvailable()) {
    api_ai_json(['error' => 'AI not configured. Add an API key in Admin → Settings.'], 503);
}

match ($action) {

    'summarise' => (function() use ($ai, $body) {
        if (!api_ai_rate_limit('summarise', 20)) api_ai_json(['error' => 'Rate limit'], 429);
        $lid = (int)($body['leader_id'] ?? 0);
        if (!$lid) api_ai_json(['error' => 'leader_id required'], 400);
        $leader = db()->fetchOne(
            "SELECT l.*, COALESCE(p.name,'') AS party FROM leaders l
             LEFT JOIN parties p ON l.party_id=p.id WHERE l.id=?", [$lid]
        );
        if (!$leader) api_ai_json(['error' => 'Leader not found'], 404);
        $summary = $ai->summariseLeader($leader);
        // Cache in DB
        if ($summary) db()->execute("UPDATE leaders SET bio=? WHERE id=? AND (bio IS NULL OR bio='')", [$summary, $lid]);
        api_ai_json(['summary' => $summary, 'provider' => $ai->activeProviders()[0] ?? 'unknown']);
    })(),

    'factcheck' => (function() use ($ai, $body) {
        if (!api_ai_rate_limit('factcheck', 5)) api_ai_json(['error' => 'Rate limit'], 429);
        $claim  = trim($body['claim'] ?? '');
        $leader = trim($body['leader_name'] ?? '');
        if (!$claim) api_ai_json(['error' => 'claim required'], 400);
        api_ai_json($ai->factCheck($claim, $leader));
    })(),

    'translate' => (function() use ($ai, $body) {
        if (!api_ai_rate_limit('translate', 15)) api_ai_json(['error' => 'Rate limit'], 429);
        $text = trim($body['text'] ?? '');
        if (!$text) api_ai_json(['error' => 'text required'], 400);
        api_ai_json(['hindi' => $ai->translateToHindi($text)]);
    })(),

    'chat' => (function() use ($ai, $body) {
        if (!api_ai_rate_limit('chat', 8)) api_ai_json(['error' => 'Rate limit. Try again later.'], 429);
        $question = trim($body['question'] ?? '');
        $leader   = trim($body['leader_name'] ?? '');
        if (!$question) api_ai_json(['error' => 'question required'], 400);
        $context = $leader ? "You are answering questions about Indian politician '{$leader}'. " : "You are NetaTrack AI, an assistant for Indian political accountability. ";
        $context .= "Answer in 2-3 sentences. Be factual and neutral.";
        $answer = $ai->ask($context . "\n\nQuestion: {$question}", 400);
        api_ai_json(['answer' => $answer]);
    })(),

    'classify_news' => (function() use ($ai, $body) {
        // Internal — called from admin only
        if (!isset($_SESSION['admin_id'])) api_ai_json(['error' => 'Forbidden'], 403);
        $result = $ai->classifyNews($body['title'] ?? '', $body['description'] ?? '');
        api_ai_json($result);
    })(),

    default => api_ai_json(['error' => 'Unknown action'], 404),
};
