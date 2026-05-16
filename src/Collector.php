<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/AwsAiClient.php';

class Collector
{
    public function run(array $items): array
    {
        $db = Database::connection();
        $ai = new AwsAiClient();
        $processed = 0;

        foreach ($items as $item) {
            $aiResult = $ai->extract($item);
            $stmt = $db->prepare('INSERT INTO ai_collected_data (source_name, title, content, ai_score, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
            $stmt->execute([
                $item['source_name'] ?? 'unknown',
                $item['title'] ?? 'Untitled',
                $item['content'] ?? '',
                $aiResult['confidence'] ?? 0,
                $aiResult['ok'] ? 'pending_review' : 'failed_ai',
            ]);
            $processed++;
        }

        return ['processed' => $processed];
    }
}
