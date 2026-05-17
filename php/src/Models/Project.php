<?php
namespace NetaTrack\Models;

use NetaTrack\Core\Model;
use NetaTrack\Core\Database;

class Project extends Model {
    protected static string $table = 'projects';

    public const STATUS_PLANNED     = 'planned';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_DELAYED     = 'delayed';
    public const STATUS_COMPLETED   = 'completed';
    public const STATUS_CANCELLED   = 'cancelled';

    public static function getByState(int $stateId): array {
        return Database::fetchAll(
            "SELECT pj.*, l.name as leader_name FROM projects pj
             LEFT JOIN leaders l ON pj.leader_id = l.id
             WHERE pj.state_id = ? ORDER BY pj.start_date DESC",
            [$stateId]
        );
    }

    public static function getDelayed(): array {
        return Database::fetchAll(
            "SELECT pj.*, s.name as state_name, l.name as leader_name
             FROM projects pj
             LEFT JOIN states s ON pj.state_id = s.id
             LEFT JOIN leaders l ON pj.leader_id = l.id
             WHERE pj.status = 'delayed' OR (pj.expected_end_date < NOW() AND pj.status = 'in_progress')
             ORDER BY pj.expected_end_date ASC"
        );
    }

    public static function getBudgetUtilization(int $stateId): float {
        $row = Database::fetch(
            "SELECT SUM(allocated_budget) as total, SUM(spent_budget) as spent
             FROM projects WHERE state_id = ?",
            [$stateId]
        );
        if (!$row || !$row['total']) return 0;
        return round(($row['spent'] / $row['total']) * 100, 1);
    }
}
