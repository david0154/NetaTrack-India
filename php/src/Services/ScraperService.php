<?php
namespace NetaTrack\Services;

use NetaTrack\Core\Database;

class ScraperService {
    public function scrapeRss(string $url, string $sourceType = 'news'): array {
        $xml = @simplexml_load_file($url);
        if (!$xml) return [];

        $items = [];
        foreach ($xml->channel->item as $item) {
            $items[] = [
                'title'       => (string)$item->title,
                'description' => (string)$item->description,
                'link'        => (string)$item->link,
                'pub_date'    => date('Y-m-d H:i:s', strtotime((string)$item->pubDate)),
                'source_url'  => $url,
                'source_type' => $sourceType,
                'status'      => 'pending_review',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ];
        }
        return $items;
    }

    public function scrapeUrl(string $url): string {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_USERAGENT      => 'NetaTrack-Bot/1.0 (+https://netatrack.in)',
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $html = curl_exec($ch);
        curl_close($ch);
        return $html ?: '';
    }

    public function extractText(string $html): string {
        $html = preg_replace('/<script[^>]*>.*?<\/script>/si', '', $html);
        $html = preg_replace('/<style[^>]*>.*?<\/style>/si', '', $html);
        return strip_tags($html);
    }

    public function isDuplicate(string $title, string $url): bool {
        $row = Database::fetch(
            "SELECT id FROM ai_collected_data WHERE title = ? OR source_url = ? LIMIT 1",
            [$title, $url]
        );
        return (bool)$row;
    }

    public function saveCollectedData(array $item): int {
        if ($this->isDuplicate($item['title'] ?? '', $item['source_url'] ?? '')) return 0;
        return Database::insert('ai_collected_data', $item);
    }
}
