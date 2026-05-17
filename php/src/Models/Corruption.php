<?php
namespace NetaTrack\Models;

use NetaTrack\Core\Model;
use NetaTrack\Core\Database;

class Corruption extends Model {
    protected static string $table = 'corruption_allegations';

    public static function getByLeader(int $leaderId): array {
        return Database::fetchAll(
            "SELECT * FROM corruption_allegations WHERE leader_id = ? ORDER BY reported_at DESC",
            [$leaderId]
        );
    }

    public static function calculateCorruptionScore(int $leaderId): int {
        $count    = static::count('leader_id = ?', [$leaderId]);
        $verified = static::count('leader_id = ? AND status = ?', [$leaderId, 'verified']);
        $edCbi    = static::count('leader_id = ? AND type IN (?,?)', [$leaderId, 'ed_case', 'cbi_case']);
        $score = min(100, ($count * 5) + ($verified * 15) + ($edCbi * 20));
        return $score;
    }

    public static function getLevel(int $score): string {
        if ($score <= 10) return 'Very Clean';
        if ($score <= 30) return 'Minor Allegations';
        if ($score <= 60) return 'Moderate';
        return 'High Corruption Risk';
    }

    public static function getRecent(int $limit = 10): array {
        return Database::fetchAll(
            "SELECT ca.*, l.name as leader_name, l.photo as leader_photo
             FROM corruption_allegations ca
             LEFT JOIN leaders l ON ca.leader_id = l.id
             ORDER BY ca.reported_at DESC LIMIT ?",
            [$limit]
        );
    }
}
