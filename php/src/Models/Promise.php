<?php
namespace NetaTrack\Models;

class Promise extends BaseModel
{
    protected string $table = 'promises';

    public function withLeader(int $id): array|false
    {
        return $this->db->selectOne(
            'SELECT pr.*, l.name as leader_name, l.photo as leader_photo, l.designation, p.name as party_name, p.color as party_color, s.name as state_name FROM promises pr LEFT JOIN leaders l ON pr.leader_id = l.id LEFT JOIN parties p ON l.party_id = p.id LEFT JOIN states s ON pr.state_id = s.id WHERE pr.id = ?',
            [$id]
        );
    }

    public function getByLeader(int $leaderId, string $status = ''): array
    {
        $where = 'leader_id = ?';
        $params = [$leaderId];
        if ($status) {
            $where .= ' AND status = ?';
            $params[] = $status;
        }
        return $this->db->select("SELECT * FROM promises WHERE $where ORDER BY promised_date DESC", $params);
    }

    public function getStatsByLeader(int $leaderId): array
    {
        return $this->db->selectOne(
            "SELECT COUNT(*) as total, SUM(status='completed') as completed, SUM(status='broken') as broken, SUM(status='in_progress') as in_progress, SUM(fake_claim_flag=1) as fake_claims FROM promises WHERE leader_id = ?",
            [$leaderId]
        );
    }

    public function getFakeClaims(int $limit = 10): array
    {
        return $this->db->select(
            'SELECT pr.*, l.name as leader_name, l.photo as leader_photo FROM promises pr LEFT JOIN leaders l ON pr.leader_id = l.id WHERE pr.fake_claim_flag = 1 ORDER BY pr.updated_at DESC LIMIT ?',
            [$limit]
        );
    }
}
