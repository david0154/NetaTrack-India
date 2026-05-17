<?php
namespace NetaTrack\Models;

use NetaTrack\Core\Model;
use NetaTrack\Core\Database;

class Leader extends Model {
    protected static string $table = 'leaders';

    public static function getWithParty(int $id): ?array {
        return Database::fetch(
            "SELECT l.*, p.name as party_name, p.color as party_color, p.logo as party_logo,
                    s.name as state_name
             FROM leaders l
             LEFT JOIN parties p ON l.party_id = p.id
             LEFT JOIN states s ON l.state_id = s.id
             WHERE l.id = ?",
            [$id]
        );
    }

    public static function getTrending(int $limit = 10): array {
        return Database::fetchAll(
            "SELECT l.*, p.name as party_name, p.color as party_color
             FROM leaders l
             LEFT JOIN parties p ON l.party_id = p.id
             ORDER BY l.overall_score DESC
             LIMIT ?",
            [$limit]
        );
    }

    public static function search(string $q): array {
        return Database::fetchAll(
            "SELECT l.*, p.name as party_name FROM leaders l
             LEFT JOIN parties p ON l.party_id = p.id
             WHERE l.name LIKE ? OR l.constituency LIKE ? OR p.name LIKE ?
             LIMIT 20",
            ["%$q%", "%$q%", "%$q%"]
        );
    }

    public static function calculateScore(int $leaderId): float {
        $leader = static::find($leaderId);
        if (!$leader) return 0;

        $promiseScore     = (float)($leader['promise_completion_rate'] ?? 0)  * 0.30;
        $projectScore     = (float)($leader['project_delivery_rate']  ?? 0)  * 0.20;
        $budgetScore      = (float)($leader['budget_efficiency']      ?? 0)  * 0.15;
        $satisfactionScore= (float)($leader['public_satisfaction']    ?? 0)  * 0.10;
        $transparencyScore= (float)($leader['transparency_score']     ?? 0)  * 0.10;
        $trustScore       = (float)($leader['verification_trust']     ?? 0)  * 0.15;
        $corruptionPenalty= (float)($leader['corruption_score']       ?? 0)  * 0.20;
        $fakePenalty      = (float)($leader['fake_claims_count']       ?? 0) * 2;

        $total = $promiseScore + $projectScore + $budgetScore + $satisfactionScore
               + $transparencyScore + $trustScore - $corruptionPenalty - $fakePenalty;

        return max(0, min(100, round($total, 2)));
    }

    public static function getRank(float $score): string {
        if ($score >= 90) return 'Excellent';
        if ($score >= 75) return 'Good';
        if ($score >= 50) return 'Average';
        return 'Poor';
    }
}
