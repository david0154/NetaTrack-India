<?php
require_once __DIR__ . '/Database.php';

class Repository
{
    public static function stats(): array
    {
        $pdo = Database::connection();
        return [
            'total_promises' => (int)$pdo->query('SELECT COUNT(*) FROM promises')->fetchColumn(),
            'pending_submissions' => (int)$pdo->query("SELECT COUNT(*) FROM public_submissions WHERE status='pending'")->fetchColumn(),
            'pending_ai' => (int)$pdo->query("SELECT COUNT(*) FROM ai_collected_data WHERE status='pending_review'")->fetchColumn(),
            'published_promises' => (int)$pdo->query("SELECT COUNT(*) FROM promises WHERE status='published'")->fetchColumn(),
        ];
    }

    public static function latestPromises(int $limit = 20): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT id,title,state,category,status,verification_score,source_name,source_url,created_at FROM promises ORDER BY id DESC LIMIT ?');
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function createPromise(array $input): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('INSERT INTO promises (title, description, state, category, budget, deadline, status, verification_score, source_name, source_url, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([
            $input['title'], $input['description'], $input['state'], $input['category'],
            $input['budget'] !== '' ? $input['budget'] : null,
            $input['deadline'] !== '' ? $input['deadline'] : null,
            $input['status'], (int)$input['verification_score'], $input['source_name'], $input['source_url'],
            $input['created_by'] ?? null,
        ]);
    }

    public static function latestSubmissions(int $limit = 20): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT id,title,leader_name,state,description,source_link,status,created_at FROM public_submissions ORDER BY id DESC LIMIT ?');
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function setSubmissionStatus(int $id, string $status): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('UPDATE public_submissions SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
    }

    public static function latestAiQueue(int $limit = 20): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT id,source_name,title,ai_score,status,created_at FROM ai_collected_data ORDER BY id DESC LIMIT ?');
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
