<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/AiClient.php';

/**
 * VerificationEngine - handles spam detection, fact-checking, duplicate detection for submissions
 */
class VerificationEngine
{
    private AiClient $ai;

    public function __construct()
    {
        $this->ai = new AiClient();
    }

    /**
     * Full pipeline: spam → duplicate → ai extraction → fact check
     */
    public function processSubmission(int $submissionId): array
    {
        $sub = Database::queryOne('SELECT * FROM public_submissions WHERE id = ?', [$submissionId]);
        if (!$sub) return ['ok' => false, 'error' => 'Submission not found'];

        // 1. Spam detection
        $spam = $this->ai->detectSpam($sub['title'] . ' ' . $sub['description']);
        $spamScore = (int)($spam['spam_score'] ?? 0);
        if ($spamScore >= 80 || ($spam['is_spam'] ?? false)) {
            Database::execute(
                'UPDATE public_submissions SET status="rejected", rejection_reason=?, ai_spam_score=? WHERE id=?',
                ['Detected as spam or AI-generated content', $spamScore, $submissionId]
            );
            return ['ok' => false, 'rejected' => true, 'reason' => 'spam', 'score' => $spamScore];
        }

        // 2. Duplicate detection
        $hash = hash('sha256', strtolower(trim($sub['title'] . $sub['leader_name'])));
        $dup  = Database::queryOne(
            'SELECT id FROM public_submissions WHERE id != ? AND status != "rejected" AND MD5(CONCAT(LOWER(TRIM(title)), LOWER(TRIM(leader_name)))) = MD5(?)',
            [$submissionId, strtolower(trim($sub['title'] . $sub['leader_name']))]
        );
        if ($dup) {
            Database::execute(
                'UPDATE public_submissions SET status="duplicate", duplicate_of=?, ai_spam_score=? WHERE id=?',
                [$dup['id'], $spamScore, $submissionId]
            );
            return ['ok' => false, 'rejected' => true, 'reason' => 'duplicate', 'duplicate_of' => $dup['id']];
        }

        // 3. AI fact verification
        $verify = $this->ai->verifyClaim($sub['title'] . '. ' . $sub['description']);
        $verificationScore = (int)(($verify['confidence'] ?? 50));

        // 4. Update status
        $autoApproveThreshold = (int)(getenv('AI_AUTO_APPROVE_THRESHOLD') ?: 90);
        $newStatus = 'admin_review';
        if ($verificationScore >= $autoApproveThreshold && !($verify['is_fake'] ?? false)) {
            $newStatus = 'approved';
        } elseif ($verify['is_fake'] ?? false) {
            $newStatus = 'rejected';
        }

        Database::execute(
            'UPDATE public_submissions SET status=?, ai_spam_score=?, ai_verification_score=? WHERE id=?',
            [$newStatus, $spamScore, $verificationScore, $submissionId]
        );

        // Log verification
        Database::execute(
            'INSERT INTO verification_logs (ref_type, ref_id, action, performed_by_type, notes, score_after) VALUES (?,?,?,?,?,?)',
            ['submission', $submissionId, 'ai_verify', 'ai', json_encode($verify), $verificationScore]
        );

        return [
            'ok'                 => true,
            'spam_score'         => $spamScore,
            'verification_score' => $verificationScore,
            'status'             => $newStatus,
            'verdict'            => $verify['verdict'] ?? 'unverifiable',
        ];
    }

    /**
     * Process all pending submissions
     */
    public function processPending(): array
    {
        $pending = Database::query('SELECT id FROM public_submissions WHERE status = "pending" LIMIT 50');
        $results = [];
        foreach ($pending as $row) {
            $results[] = $this->processSubmission($row['id']);
        }
        return $results;
    }

    /**
     * Verify an AI collected item
     */
    public function verifyAiCollected(int $itemId): array
    {
        $item = Database::queryOne('SELECT * FROM ai_collected_data WHERE id = ?', [$itemId]);
        if (!$item) return ['ok' => false, 'error' => 'Item not found'];

        // Duplicate hash check
        $hash = hash('sha256', strtolower(trim($item['title'])));
        $dup  = Database::queryOne(
            'SELECT id FROM ai_collected_data WHERE id != ? AND duplicate_hash = ?',
            [$itemId, $hash]
        );
        if ($dup) {
            Database::execute('UPDATE ai_collected_data SET status="duplicate" WHERE id=?', [$itemId]);
            return ['ok' => false, 'reason' => 'duplicate'];
        }

        Database::execute('UPDATE ai_collected_data SET duplicate_hash=? WHERE id=?', [$hash, $itemId]);
        return ['ok' => true, 'hash' => $hash];
    }
}
