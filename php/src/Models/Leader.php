<?php
namespace NetaTrack\Models;

use NetaTrack\Core\Model;

/**
 * NetaTrack India - Political Leader Model
 */
class Leader extends Model
{
    protected string $table = 'leaders';
    protected array $fillable = [
        'name','slug','photo','dob','gender','state_id','party_id',
        'constituency','position','designation','term_start','term_end',
        'education','assets_declared','criminal_cases',
        // Score fields
        'score_promise_completion','score_project_delivery',
        'score_budget_efficiency','score_public_satisfaction',
        'score_transparency','score_corruption','score_fake_claims',
        'score_verification_trust','total_score','score_rank',
        // Contact
        'email','phone','website','twitter','facebook','instagram',
        // Meta
        'bio','status','is_verified','created_at','updated_at'
    ];

    public function findBySlug(string $slug): ?array
    {
        return $this->db->fetch(
            'SELECT l.*,
                    s.name as state_name, s.slug as state_slug,
                    p.name as party_name, p.abbreviation as party_abbr, p.color_code as party_color
             FROM leaders l
             LEFT JOIN states s ON s.id=l.state_id
             LEFT JOIN parties p ON p.id=l.party_id
             WHERE l.slug=? LIMIT 1',
            [$slug]
        );
    }

    public function getTopLeaders(int $limit = 10): array
    {
        return $this->db->fetchAll(
            'SELECT l.*, s.name as state_name, p.name as party_name, p.color_code as party_color
             FROM leaders l
             LEFT JOIN states s ON s.id=l.state_id
             LEFT JOIN parties p ON p.id=l.party_id
             WHERE l.status="active"
             ORDER BY l.total_score DESC LIMIT ?',
            [$limit]
        );
    }

    public function getByState(int $stateId): array
    {
        return $this->db->fetchAll(
            'SELECT l.*, p.name as party_name, p.color_code as party_color
             FROM leaders l
             LEFT JOIN parties p ON p.id=l.party_id
             WHERE l.state_id=? AND l.status="active"
             ORDER BY l.total_score DESC',
            [$stateId]
        );
    }

    public function search(string $q, int $limit = 20): array
    {
        return $this->db->fetchAll(
            'SELECT l.*, s.name as state_name, p.name as party_name
             FROM leaders l
             LEFT JOIN states s ON s.id=l.state_id
             LEFT JOIN parties p ON p.id=l.party_id
             WHERE l.status="active" AND (
                 l.name LIKE ? OR l.constituency LIKE ? OR l.designation LIKE ?
             ) LIMIT ?',
            ["%{$q}%", "%{$q}%", "%{$q}%", $limit]
        );
    }

    public function recalculateScore(int $leaderId): void
    {
        $l = $this->find($leaderId);
        if (!$l) return;

        $score =
            ($l['score_promise_completion']  * 0.30) +
            ($l['score_project_delivery']    * 0.20) +
            ($l['score_budget_efficiency']   * 0.15) +
            ($l['score_public_satisfaction'] * 0.10) +
            ($l['score_transparency']        * 0.10) +
            ($l['score_verification_trust']  * 0.15) -
            ($l['score_corruption']          * 0.20) -
            ($l['score_fake_claims']         * 0.10);

        $score = max(0, min(100, round($score)));

        $rank = match(true) {
            $score >= 90 => 'Excellent',
            $score >= 75 => 'Good',
            $score >= 50 => 'Average',
            default      => 'Poor',
        };

        $this->db->query(
            'UPDATE leaders SET total_score=?, score_rank=? WHERE id=?',
            [$score, $rank, $leaderId]
        );
    }

    public function getStats(): array
    {
        return [
            'total'     => $this->count(),
            'active'    => $this->count("status='active'"),
            'excellent' => $this->count("score_rank='Excellent'"),
            'good'      => $this->count("score_rank='Good'"),
            'average'   => $this->count("score_rank='Average'"),
            'poor'      => $this->count("score_rank='Poor'"),
        ];
    }
}
