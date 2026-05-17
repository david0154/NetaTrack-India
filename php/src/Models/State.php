<?php
namespace NetaTrack\Models;

use NetaTrack\Core\Model;

/**
 * NetaTrack India - State Model
 */
class State extends Model
{
    protected string $table = 'states';
    protected array $fillable = [
        'name','slug','code','region','capital',
        'cm_name','cm_party','total_mlas','total_mps',
        'latitude','longitude','map_color',
        'is_ut','status','created_at','updated_at'
    ];

    public function allActive(): array
    {
        return $this->db->fetchAll("SELECT * FROM states WHERE status='active' ORDER BY name ASC");
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->findBy('slug', $slug);
    }

    public function getWithLeaderCount(): array
    {
        return $this->db->fetchAll(
            'SELECT s.*, COUNT(l.id) as leader_count
             FROM states s
             LEFT JOIN leaders l ON l.state_id=s.id AND l.status="active"
             GROUP BY s.id ORDER BY s.name'
        );
    }
}
