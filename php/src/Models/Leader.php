<?php
namespace NetaTrack\Models;

class Leader extends BaseModel
{
    protected string $table = 'leaders';

    public function withPartyAndState(int $id): array|false
    {
        return $this->db->selectOne(
            'SELECT l.*, p.name as party_name, p.abbreviation as party_abbr, p.logo as party_logo, p.color as party_color, s.name as state_name, s.code as state_code FROM leaders l LEFT JOIN parties p ON l.party_id = p.id LEFT JOIN states s ON l.state_id = s.id WHERE l.id = ?',
            [$id]
        );
    }

    public function getTopLeaders(int $limit = 10, ?string $stateCode = null): array
    {
        $where = 'l.is_active = 1';
        $params = [];
        if ($stateCode) {
            $where .= ' AND s.code = ?';
            $params[] = $stateCode;
        }
        return $this->db->select(
            "SELECT l.*, p.name as party_name, p.color as party_color, s.name as state_name FROM leaders l LEFT JOIN parties p ON l.party_id = p.id LEFT JOIN states s ON l.state_id = s.id WHERE $where ORDER BY l.final_score DESC LIMIT ?",
            array_merge($params, [$limit])
        );
    }

    public function getTrendingCorrupt(int $limit = 5): array
    {
        return $this->db->select(
            'SELECT l.*, p.name as party_name, s.name as state_name FROM leaders l LEFT JOIN parties p ON l.party_id = p.id LEFT JOIN states s ON l.state_id = s.id WHERE l.is_active = 1 ORDER BY l.corruption_score DESC LIMIT ?',
            [$limit]
        );
    }

    public function searchLeaders(string $q, int $limit = 20): array
    {
        return $this->db->select(
            "SELECT l.id, l.uuid, l.name, l.photo, l.designation, l.final_score, l.rank_label, p.name as party_name, s.name as state_name FROM leaders l LEFT JOIN parties p ON l.party_id = p.id LEFT JOIN states s ON l.state_id = s.id WHERE l.name LIKE ? OR l.constituency LIKE ? ORDER BY l.final_score DESC LIMIT ?",
            ["%$q%", "%$q%", $limit]
        );
    }
}
