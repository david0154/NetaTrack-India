<?php
namespace NetaTrack\Services;

use NetaTrack\Core\Database;
use NetaTrack\Models\{Leader, Promise, Report};

/**
 * ScraperService — RSS/Web news scraper that:
 * 1. Fetches articles from configured RSS sources
 * 2. Matches articles to leaders by name
 * 3. Creates auto-draft reports/promise updates
 * 4. Logs scraper jobs to the DB
 */
class ScraperService
{
    private \PDO      $db;
    private AIService $ai;
    private array     $leaderIndex = [];

    /** Default Indian political news RSS feeds */
    private array $defaultSources = [
        ['name'=>'NDTV Politics',     'url'=>'https://feeds.feedburner.com/ndtvnews-india-news',      'type'=>'rss'],
        ['name'=>'India Today Pol',   'url'=>'https://www.indiatoday.in/rss/1206577',                'type'=>'rss'],
        ['name'=>'The Hindu Politics','url'=>'https://www.thehindu.com/news/national/?service=rss',  'type'=>'rss'],
        ['name'=>'Times of India',    'url'=>'https://timesofindia.indiatimes.com/rssfeeds/-2128936835.cms','type'=>'rss'],
        ['name'=>'Hindustan Times',   'url'=>'https://www.hindustantimes.com/rss/india/rssfeed.xml', 'type'=>'rss'],
        ['name'=>'News18 Politics',   'url'=>'https://www.news18.com/rss/politics.xml',              'type'=>'rss'],
    ];

    public function __construct(string $geminiKey = '')
    {
        $this->db = Database::getInstance()->getConnection();
        $this->ai = new AIService($geminiKey);
        $this->buildLeaderIndex();
    }

    /**
     * Run all configured RSS sources.
     * Returns total items processed.
     */
    public function runAll(): int
    {
        $sources = $this->getSources();
        $total   = 0;
        foreach ($sources as $source) {
            $total += $this->runSource($source);
        }
        return $total;
    }

    /**
     * Run a single RSS source.
     */
    public function runSource(array $source): int
    {
        $jobId = $this->createJob($source);
        $count = 0;

        try {
            $this->updateJob($jobId, 'running');
            $items = $this->fetchRss($source['url']);

            foreach ($items as $item) {
                if ($this->processItem($item)) $count++;
            }

            $this->updateJob($jobId, 'completed', $count);
        } catch (\Throwable $e) {
            $this->updateJob($jobId, 'failed', 0, $e->getMessage());
        }

        return $count;
    }

    /**
     * Fetch and parse RSS feed.
     */
    private function fetchRss(string $url): array
    {
        $ctx = stream_context_create(['http' => [
            'timeout'    => 10,
            'user_agent' => 'NetaTrack-Scraper/1.0',
            'follow_location' => 1,
        ]]);

        $raw = @file_get_contents($url, false, $ctx);
        if (!$raw) return [];

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($raw, 'SimpleXMLElement', LIBXML_NOCDATA);
        if (!$xml) return [];

        $items  = [];
        $channel = $xml->channel ?? $xml;
        foreach ($channel->item as $item) {
            $items[] = [
                'title'       => (string)($item->title        ?? ''),
                'description' => strip_tags((string)($item->description ?? '')),
                'link'        => (string)($item->link         ?? ''),
                'pubDate'     => (string)($item->pubDate      ?? ''),
                'content'     => strip_tags((string)($item->children('content', true)->encoded ?? $item->description ?? '')),
            ];
        }
        return $items;
    }

    /**
     * Process a single RSS item: match leaders, create draft reports.
     */
    private function processItem(array $item): bool
    {
        $text    = $item['title'] . ' ' . $item['description'];
        $matched = $this->matchLeaders($text);

        if (empty($matched)) return false;

        // Avoid duplicate by URL
        $existing = $this->db->prepare("SELECT id FROM reports WHERE evidence_urls LIKE :url LIMIT 1");
        $existing->execute([':url' => '%' . $item['link'] . '%']);
        if ($existing->fetchColumn()) return false;

        $leaderId = $matched[0]['id'];
        $type     = $this->guessType($text);
        $conf     = 0;

        // Optional AI analysis
        try {
            $conf = $this->ai->analyzeReport($item['title'], $item['description']);
        } catch (\Throwable) {}

        $reportModel = new Report();
        $reportModel->create([
            'leader_id'     => $leaderId,
            'state_id'      => $matched[0]['state_id'] ?? null,
            'title'         => mb_substr($item['title'], 0, 490),
            'description'   => mb_substr($item['description'], 0, 2000),
            'type'          => $type,
            'status'        => 'pending',
            'evidence_urls' => $item['link'],
            'reporter_name' => 'AI Scraper',
            'ai_confidence' => $conf,
        ]);

        return true;
    }

    /**
     * Match leader names found in text against our leader index.
     */
    private function matchLeaders(string $text): array
    {
        $matched = [];
        foreach ($this->leaderIndex as $leader) {
            if (stripos($text, $leader['name']) !== false) {
                $matched[] = $leader;
            }
            // Also match common short names (first + last name only)
            $parts = explode(' ', $leader['name']);
            if (count($parts) >= 2) {
                $short = $parts[0] . ' ' . end($parts);
                if ($short !== $leader['name'] && stripos($text, $short) !== false) {
                    $matched[] = $leader;
                }
            }
        }
        return array_unique($matched, SORT_REGULAR);
    }

    /**
     * Guess report type from text keywords.
     */
    private function guessType(string $text): string
    {
        $text = strtolower($text);
        $map  = [
            'corruption'    => ['corruption','corrupt','bribery','bribe','scam','fraud','embezzl','hawala','kickback'],
            'fake_claim'    => ['fake','mislead','misinform','false claim','fact check','fabricat','doctored'],
            'project_delay' => ['delay','delayed','stuck','stalled','incomplete','pending project','behind schedule'],
            'promise_broken'=> ['promise','promis','pledg','election promise','manifesto'],
            'positive'      => ['inaugurate','launch','completed','achieve','award','success','develop','scheme'],
        ];
        foreach ($map as $type => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($text, $kw)) return $type;
            }
        }
        return 'other';
    }

    /**
     * Build an in-memory index of all leader names + ids.
     */
    private function buildLeaderIndex(): void
    {
        $this->leaderIndex = $this->db->query(
            "SELECT id, name, state_id FROM leaders WHERE status='active' ORDER BY name"
        )->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getSources(): array
    {
        try {
            $rows = $this->db->query(
                "SELECT name, source_url AS url, type FROM scraper_sources ORDER BY id"
            )->fetchAll(\PDO::FETCH_ASSOC);
            return $rows ?: $this->defaultSources;
        } catch (\Throwable) {
            return $this->defaultSources;
        }
    }

    private function createJob(array $source): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO scraper_jobs (source_name, source_url, type, status, started_at)
             VALUES (:name, :url, :type, 'pending', NOW())"
        );
        $stmt->execute([':name'=>$source['name'],':url'=>$source['url'],':type'=>$source['type']??'rss']);
        return (int)$this->db->lastInsertId();
    }

    private function updateJob(int $id, string $status, int $items = 0, string $error = ''): void
    {
        $this->db->prepare(
            "UPDATE scraper_jobs SET status=:s, items_found=:i, error_msg=:e, finished_at=NOW() WHERE id=:id"
        )->execute([':s'=>$status,':i'=>$items,':e'=>$error,':id'=>$id]);
    }
}
