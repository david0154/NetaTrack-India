<?php
namespace NetaTrack\Models;

use NetaTrack\Core\Model;
use NetaTrack\Core\Database;

class PublicReport extends Model {
    protected static string $table = 'public_reports';

    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_FAKE     = 'fake';

    public static function getPending(): array {
        return Database::fetchAll(
            "SELECT pr.*, u.name as user_name, u.credibility_score
             FROM public_reports pr
             LEFT JOIN users u ON pr.user_id = u.id
             WHERE pr.status = 'pending'
             ORDER BY pr.created_at DESC"
        );
    }

    public static function approve(int $id, int $adminId): void {
        static::update($id, [
            'status'      => self::STATUS_APPROVED,
            'reviewed_by' => $adminId,
            'reviewed_at' => date('Y-m-d H:i:s'),
        ]);
        $report = static::find($id);
        if ($report) User::updateCredibility($report['user_id'], 10);
    }

    public static function reject(int $id, int $adminId, string $reason = ''): void {
        static::update($id, [
            'status'       => self::STATUS_REJECTED,
            'reviewed_by'  => $adminId,
            'reviewed_at'  => date('Y-m-d H:i:s'),
            'admin_notes'  => $reason,
        ]);
    }
}
