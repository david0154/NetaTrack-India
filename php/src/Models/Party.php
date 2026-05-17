<?php
namespace NetaTrack\Models;

use NetaTrack\Core\Model;

/**
 * NetaTrack India - Political Party Model
 */
class Party extends Model
{
    protected string $table = 'parties';
    protected array $fillable = [
        'name','slug','abbreviation','founded_year',
        'ideology','logo','color_code','website',
        'national_or_state','status','created_at','updated_at'
    ];

    public function allActive(): array
    {
        return $this->db->fetchAll("SELECT * FROM parties WHERE status='active' ORDER BY name");
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->findBy('slug', $slug);
    }

    public function getWithLeaderCount(): array
    {
        return $this->db->fetchAll(
            'SELECT p.*, COUNT(l.id) as leader_count
             FROM parties p
             LEFT JOIN leaders l ON l.party_id=p.id AND l.status="active"
             GROUP BY p.id ORDER BY p.name'
        );
    }
}
