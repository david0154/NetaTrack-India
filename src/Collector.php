<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/AiClient.php';
require_once __DIR__ . '/VerificationEngine.php';

/**
 * Collector - receives scraped data, runs AI extraction, stores to ai_collected_data
 */
class Collector
{
    private AiClient $ai;
    private VerificationEngine $verifier;

    public function __construct()
    {
        $this->ai       = new AiClient();
        $this->verifier = new VerificationEngine();
    }

    /**
     * Process a batch of scraped items
     * Each item: [source_name, source_url, source_type, title, content]
     */
    public function run(array $items): array
    {
        $processed = 0;
        $failed    = 0;
        $skipped   = 0;

        foreach ($items as $item) {
            $title   = trim($item['title'] ?? '');
            $content = trim($item['content'] ?? '');
            if (empty($title)) { $skipped++; continue; }

            // Translate if regional language content
            if (mb_strlen($content) > 0) {
                $translated = $this->ai->translateToEnglish($content);
                if ($translated['translated']) {
                    $content = $translated['text'];
                }
            }

            // AI extraction
            $extracted = $this->ai->extractEntities($title . ' ' . $content, $item['source_type'] ?? 'news');
            $confidence = (int)($extracted['confidence'] ?? 0);
            $status     = $extracted['ok'] ? 'pending_review' : 'failed_ai';

            // Store
            try {
                $id = Database::execute(
                    'INSERT INTO ai_collected_data (source_name, source_url, source_type, title, content, extracted_entities, ai_confidence, ai_provider, status)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
                    [
                        $item['source_name'] ?? 'unknown',
                        $item['source_url']  ?? '',
                        $item['source_type'] ?? 'other',
                        $title,
                        $content,
                        json_encode($extracted),
                        $confidence,
                        getenv('AI_PROVIDER') ?: 'gemini',
                        $status,
                    ]
                );
                // Run duplicate/hash verification
                $this->verifier->verifyAiCollected($id);
                $processed++;
            } catch (\Exception $e) {
                $failed++;
                error_log('[Collector] Insert failed: ' . $e->getMessage());
            }
        }

        return ['processed' => $processed, 'failed' => $failed, 'skipped' => $skipped];
    }

    /**
     * Fetch and collect from a single scraper source
     */
    public function collectFromSource(int $sourceId): array
    {
        $source = Database::queryOne('SELECT * FROM scraper_sources WHERE id = ? AND is_active = 1', [$sourceId]);
        if (!$source) return ['ok' => false, 'error' => 'Source not found or inactive'];

        // Delegate to Python scraper via CLI (recommended) or simple HTTP fetch
        $items = $this->fetchStatic($source['url'], $source['name']);
        $result = $this->run($items);

        Database::execute(
            'UPDATE scraper_sources SET last_scraped=NOW(), last_status=? WHERE id=?',
            [$result['processed'] > 0 ? 'success' : 'failed', $sourceId]
        );

        return array_merge(['ok' => true], $result);
    }

    /**
     * Simple static page fetch and basic text extraction
     * For production use the Python Thinker App for dynamic/JS pages
     */
    private function fetchStatic(string $url, string $sourceName): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_USERAGENT      => 'NetaTrack-India/1.0 (Political Transparency Tracker)',
        ]);
        $html  = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error || !$html) return [];

        // Basic: extract meta title and og:description
        $title = '';
        $desc  = '';
        if (preg_match('/<title[^>]*>([^<]+)<\/title>/i', $html, $m)) $title = html_entity_decode(trim($m[1]));
        if (preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\']([^\'"]+)["\'][^>]*>/i', $html, $m)) $desc = html_entity_decode(trim($m[1]));
        if (empty($title)) return [];

        return [[
            'source_name' => $sourceName,
            'source_url'  => $url,
            'source_type' => 'other',
            'title'       => $title,
            'content'     => $desc,
        ]];
    }
}
