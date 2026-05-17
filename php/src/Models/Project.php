<?php
namespace NetaTrack\Models;

use NetaTrack\Core\Model;

/**
 * NetaTrack India - Government Project Model
 */
class Project extends Model
{
    protected string $table = 'projects';
    protected array $fillable = [
        'title','slug','description','category',
        'state_id','leader_id','party_id',
        'budget_allocated','budget_used','budget_currency',
        'start_date','expected_end_date','actual_end_date',
        'status','completion_percent','delay_days',
        'tender_number','source_url','beneficiaries',
        'ai_verified','admin_verified','is_featured',
        'latitude','longitude','created_at','updated_at'
    ];

    // status: planned | in_progress | completed | delayed | cancelled | stalled

    public function getWithRelations(int $id): ?array
    {
        return $this->db->fetch(
            'SELECT pj.*,
                    s.name as state_name,
                    l.name as leader_name, l.photo as leader_photo,
                    p.name as party_name, p.color_code as party_color
             FROM projects pj
             LEFT JOIN states s  ON s.id=pj.state_id
             LEFT JOIN leaders l ON l.id=pj.leader_id
             LEFT JOIN parties p ON p.id=pj.party_id
             WHERE pj.id=? LIMIT 1',
            [$id]
        );
    }

    public function getPlatformStats(): array
    {
        return [
            'total'      => $this->count(),
            'in_progress'=> $this->count("status='in_progress'"),
            'completed'  => $this->count("status='completed'"),
            'delayed'    => $this->count("status='delayed'"),
            'cancelled'  => $this->count("status='cancelled'"),
        ];
    }

    public function getDelayed(): array
    {
        return $this->db->fetchAll(
            "SELECT pj.*, s.name as state_name, l.name as leader_name
             FROM projects pj
             LEFT JOIN states s ON s.id=pj.state_id
             LEFT JOIN leaders l ON l.id=pj.leader_id
             WHERE pj.status='delayed'
             ORDER BY pj.delay_days DESC LIMIT 50"
        );
    }
}
