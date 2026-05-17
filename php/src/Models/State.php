<?php
namespace NetaTrack\Models;

use NetaTrack\Core\Model;
use NetaTrack\Core\Database;

class State extends Model {
    protected static string $table = 'states';

    public static function findBySlug(string $slug): ?array {
        return Database::fetch("SELECT * FROM states WHERE slug = ? LIMIT 1", [$slug]);
    }

    public static function getAllWithStats(): array {
        return Database::fetchAll(
            "SELECT s.*,
                COUNT(DISTINCT l.id) as leader_count,
                COUNT(DISTINCT pj.id) as project_count,
                COUNT(DISTINCT pr.id) as promise_count
             FROM states s
             LEFT JOIN leaders l  ON l.state_id  = s.id
             LEFT JOIN projects pj ON pj.state_id = s.id
             LEFT JOIN promises pr ON pr.leader_id IN (SELECT id FROM leaders WHERE state_id = s.id)
             GROUP BY s.id
             ORDER BY s.name ASC"
        );
    }
}
