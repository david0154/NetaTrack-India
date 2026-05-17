<?php
/**
 * NetaTrack India — AI Cron Jobs (run via crontab or web cron)
 * Handles: auto-classify pending announcements,
 *          auto-summarise leader bios,
 *          auto-analyse pending reports,
 *          translate to Hindi
 *
 * Crontab example:
 *   * * * * * php /var/www/netatrack/php/artisan cron:ai >> /var/log/netatrack_ai.log 2>&1
 */

namespace App\AI;

class AICron
{
    private AIRouter $ai;
    private \PDO $pdo;

    public function __construct()
    {
        $this->ai  = AIRouter::make();
        $this->pdo = db()->getPdo();
    }

    public function run(): void
    {
        if (!$this->ai->isAvailable()) {
            echo "[!] No AI keys configured. Skipping.\n";
            return;
        }
        echo "[*] AI Cron — providers: " . implode(', ', $this->ai->activeProviders()) . "\n";
        $this->classifyAnnouncements();
        $this->summariseLeaders();
        $this->analyseReports();
        echo "[✓] AI Cron done.\n";
    }

    // Auto-classify pending announcements
    private function classifyAnnouncements(): void
    {
        $rows = $this->pdo->query(
            "SELECT id, title, description FROM announcements WHERE category='general' AND status='pending' LIMIT 30"
        )->fetchAll(\PDO::FETCH_ASSOC);
        echo "  Classifying " . count($rows) . " announcements...\n";
        foreach ($rows as $row) {
            $result = $this->ai->classifyNews($row['title'], $row['description'] ?? '');
            if (!empty($result['category'])) {
                $stmt = $this->pdo->prepare(
                    "UPDATE announcements SET category=?, status='approved' WHERE id=?"
                );
                $stmt->execute([$result['category'], $row['id']]);
                // Link to leader if found
                if (!empty($result['leader_name'])) {
                    $lname = '%' . explode(' ', $result['leader_name'])[0] . '%';
                    $l = $this->pdo->prepare("SELECT id FROM leaders WHERE name LIKE ? LIMIT 1");
                    $l->execute([$lname]);
                    if ($lid = $l->fetchColumn()) {
                        $this->pdo->prepare("UPDATE announcements SET leader_id=? WHERE id=?")
                            ->execute([$lid, $row['id']]);
                    }
                }
                echo "    #{$row['id']} → {$result['category']}\n";
            }
            usleep(600000); // 0.6s
        }
    }

    // Auto-summarise leaders missing bio
    private function summariseLeaders(): void
    {
        $rows = $this->pdo->query(
            "SELECT l.id, l.name, l.total_score, l.constituency,
             COALESCE(p.name,'') AS party
             FROM leaders l LEFT JOIN parties p ON l.party_id=p.id
             WHERE (l.bio IS NULL OR l.bio='') AND l.status='active' LIMIT 20"
        )->fetchAll(\PDO::FETCH_ASSOC);
        echo "  Summarising " . count($rows) . " leader bios...\n";
        foreach ($rows as $leader) {
            $bio = $this->ai->summariseLeader($leader);
            if ($bio) {
                $this->pdo->prepare("UPDATE leaders SET bio=? WHERE id=?")
                    ->execute([$bio, $leader['id']]);
                echo "    {$leader['name']} ✓\n";
            }
            usleep(800000);
        }
    }

    // Auto-analyse pending reports
    private function analyseReports(): void
    {
        $rows = $this->pdo->query(
            "SELECT r.id, r.description, r.title, l.name AS leader_name
             FROM reports r JOIN leaders l ON r.leader_id=l.id
             WHERE r.status='pending' AND r.ai_analysed=0 LIMIT 20"
        )->fetchAll(\PDO::FETCH_ASSOC);
        echo "  Analysing " . count($rows) . " reports...\n";
        foreach ($rows as $row) {
            $text = ($row['title'] ?? '') . ' ' . ($row['description'] ?? '');
            $result = $this->ai->analyseReport($text, $row['leader_name']);
            if (!empty($result)) {
                $suggested = $result['suggested_action'] ?? 'review';
                $spam = !empty($result['is_spam']) ? 1 : 0;
                $summary = $result['summary'] ?? '';
                $this->pdo->prepare(
                    "UPDATE reports SET ai_analysed=1, ai_summary=?, ai_suggestion=?, is_spam=? WHERE id=?"
                )->execute([$summary, $suggested, $spam, $row['id']]);
                echo "    Report #{$row['id']} → {$suggested}" . ($spam ? ' [SPAM]' : '') . "\n";
            }
            usleep(700000);
        }
    }
}
