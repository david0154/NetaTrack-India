<?php
namespace NetaTrack\Models;

use NetaTrack\Core\Model;
use NetaTrack\Core\Database;

class Promise extends Model {
    protected static string $table = 'promises';

    public const STATUS_PENDING    = 'pending';
    public const STATUS_IN_PROGRESS= 'in_progress';
    public const STATUS_COMPLETED  = 'completed';
    public const STATUS_BROKEN     = 'broken';
    public const STATUS_FAKE       = 'fake';

    public static function getByLeader(int $leaderId): array {
        return Database::fetchAll(
            "SELECT * FROM promises WHERE leader_id = ? ORDER BY created_at DESC",
            [$leaderId]
        );
    }

    public static function getCompletionRate(int $leaderId): float {
        $total     = static::count('leader_id = ?', [$leaderId]);
        $completed = static::count('leader_id = ? AND status = ?', [$leaderId, self::STATUS_COMPLETED]);
        return $total > 0 ? round(($completed / $total) * 100, 1) : 0;
    }

    public static function getTrending(): array {
        return Database::fetchAll(
            "SELECT p.*, l.name as leader_name, l.photo as leader_photo
             FROM promises p
             LEFT JOIN leaders l ON p.leader_id = l.id
             ORDER BY p.created_at DESC LIMIT 20"
        );
    }
}
