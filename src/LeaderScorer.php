<?php
require_once __DIR__ . '/Database.php';

/**
 * LeaderScorer - calculates AI-powered leader scores based on 8 weighted categories
 *
 * Weights:
 *   promise_completion   30%
 *   project_delivery     20%
 *   budget_efficiency    15%
 *   public_satisfaction  10%
 *   transparency         10%
 *   corruption_penalty  -20% (deduction)
 *   fake_claim_penalty  -10% (deduction)
 *   verification_trust   15%
 */
class LeaderScorer
{
    private const WEIGHTS = [
        'promise_completion'  => 30,
        'project_delivery'    => 20,
        'budget_efficiency'   => 15,
        'public_satisfaction' => 10,
        'transparency'        => 10,
        'verification_trust'  => 15,
    ];

    public function calculate(int $leaderId): array
    {
        $leader = Database::queryOne('SELECT * FROM leaders WHERE id = ?', [$leaderId]);
        if (!$leader) return ['ok' => false, 'error' => 'Leader not found'];

        $scores = [
            'promise_completion'  => $this->promiseCompletionScore($leaderId),
            'project_delivery'    => $this->projectDeliveryScore($leaderId),
            'budget_efficiency'   => $this->budgetEfficiencyScore($leaderId),
            'public_satisfaction' => $this->publicSatisfactionScore($leaderId),
            'transparency'        => $this->transparencyScore($leaderId),
            'verification_trust'  => $this->verificationTrustScore($leaderId),
            'corruption_penalty'  => $this->corruptionPenalty($leaderId),
            'fake_claim_penalty'  => $this->fakeClaimPenalty($leaderId),
        ];

        $final = 0;
        foreach (self::WEIGHTS as $key => $weight) {
            $final += ($scores[$key] / 100) * $weight;
        }
        $final -= $scores['corruption_penalty'];
        $final -= $scores['fake_claim_penalty'];
        $final  = max(0, min(100, round($final, 2)));

        $rank = match (true) {
            $final >= 90 => 'Excellent',
            $final >= 75 => 'Good',
            $final >= 50 => 'Average',
            default      => 'Poor',
        };

        // Calculate corruption level from cases
        $corruptionScore   = $this->corruptionRiskScore($leaderId);
        $corruptionLevel   = match (true) {
            $corruptionScore <= 10 => 'Very Clean',
            $corruptionScore <= 30 => 'Minor Allegations',
            $corruptionScore <= 60 => 'Moderate',
            default                 => 'High Risk',
        };

        // Persist
        Database::execute(
            'UPDATE leaders SET
               promise_completion_score=?, project_delivery_score=?, budget_efficiency_score=?,
               public_satisfaction_score=?, transparency_score=?, corruption_penalty=?,
               fake_claim_penalty=?, verification_trust_score=?, final_score=?,
               rank_label=?, corruption_level=?, updated_at=NOW()
             WHERE id=?',
            [
                $scores['promise_completion'], $scores['project_delivery'], $scores['budget_efficiency'],
                $scores['public_satisfaction'], $scores['transparency'], $scores['corruption_penalty'],
                $scores['fake_claim_penalty'], $scores['verification_trust'], $final,
                $rank, $corruptionLevel, $leaderId,
            ]
        );

        return [
            'ok'               => true,
            'leader_id'        => $leaderId,
            'scores'           => $scores,
            'final_score'      => $final,
            'rank'             => $rank,
            'corruption_level' => $corruptionLevel,
            'corruption_score' => $corruptionScore,
        ];
    }

    public function recalculateAll(): array
    {
        $leaders = Database::query('SELECT id FROM leaders WHERE is_active = 1');
        $results = [];
        foreach ($leaders as $l) {
            $results[] = $this->calculate($l['id']);
        }
        return $results;
    }

    private function promiseCompletionScore(int $leaderId): float
    {
        $row = Database::queryOne(
            'SELECT COUNT(*) as total,
                    SUM(CASE WHEN status="completed" THEN 1 ELSE 0 END) as completed
             FROM promises WHERE leader_id = ?',
            [$leaderId]
        );
        if (!$row || $row['total'] == 0) return 50;
        return round(($row['completed'] / $row['total']) * 100, 2);
    }

    private function projectDeliveryScore(int $leaderId): float
    {
        $row = Database::queryOne(
            'SELECT COUNT(*) as total,
                    SUM(CASE WHEN status="completed" THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status="delayed" OR status="stalled" THEN 1 ELSE 0 END) as delayed
             FROM projects WHERE leader_id = ?',
            [$leaderId]
        );
        if (!$row || $row['total'] == 0) return 50;
        $score = (($row['completed'] - $row['delayed'] * 0.5) / $row['total']) * 100;
        return max(0, min(100, round($score, 2)));
    }

    private function budgetEfficiencyScore(int $leaderId): float
    {
        $row = Database::queryOne(
            'SELECT SUM(total_budget) as total, SUM(spent_budget) as spent FROM projects WHERE leader_id = ? AND status = "completed"',
            [$leaderId]
        );
        if (!$row || !$row['total']) return 50;
        $utilization = $row['spent'] / $row['total'];
        // Ideal = 0.85-1.0 utilization; penalize over/under spend
        $score = 100 - abs($utilization - 0.92) * 100;
        return max(0, min(100, round($score, 2)));
    }

    private function publicSatisfactionScore(int $leaderId): float
    {
        $row = Database::queryOne(
            'SELECT COUNT(*) as total,
                    SUM(CASE WHEN status="approved" THEN 1 ELSE 0 END) as positive
             FROM public_submissions WHERE leader_id = ? AND type NOT IN ("corruption","fake_claim")',
            [$leaderId]
        );
        if (!$row || $row['total'] == 0) return 50;
        return round((1 - ($row['positive'] / $row['total'])) * 100, 2);
    }

    private function transparencyScore(int $leaderId): float
    {
        $row = Database::queryOne(
            'SELECT COUNT(*) as total, AVG(verification_score) as avg_score FROM promises WHERE leader_id = ?',
            [$leaderId]
        );
        return (float) round($row['avg_score'] ?? 50, 2);
    }

    private function verificationTrustScore(int $leaderId): float
    {
        $row = Database::queryOne(
            'SELECT AVG(ai_confidence) as avg FROM promises WHERE leader_id = ?',
            [$leaderId]
        );
        return (float) round($row['avg'] ?? 50, 2);
    }

    private function corruptionPenalty(int $leaderId): float
    {
        $row = Database::queryOne(
            'SELECT COUNT(*) as total FROM corruption_cases WHERE leader_id = ? AND verified = 1',
            [$leaderId]
        );
        return min(20, ($row['total'] ?? 0) * 4);
    }

    private function fakeClaimPenalty(int $leaderId): float
    {
        $row = Database::queryOne(
            'SELECT COUNT(*) as total FROM promises WHERE leader_id = ? AND status = "fake"',
            [$leaderId]
        );
        return min(10, ($row['total'] ?? 0) * 2);
    }

    private function corruptionRiskScore(int $leaderId): float
    {
        $weights = ['critical' => 40, 'serious' => 25, 'moderate' => 15, 'minor' => 5];
        $cases   = Database::query('SELECT severity FROM corruption_cases WHERE leader_id = ? AND verified = 1', [$leaderId]);
        $total   = 0;
        foreach ($cases as $c) {
            $total += $weights[$c['severity']] ?? 5;
        }
        return min(100, $total);
    }
}
