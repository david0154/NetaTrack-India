<?php
namespace NetaTrack\Models;

class User extends BaseModel
{
    protected string $table = 'users';

    public function findByEmail(string $email): array|false
    {
        return $this->db->selectOne(
            'SELECT u.*, r.name as role_name, r.permissions FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.email = ? LIMIT 1',
            [$email]
        );
    }

    public function withRole(int $id): array|false
    {
        return $this->db->selectOne(
            'SELECT u.*, r.name as role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.id = ?',
            [$id]
        );
    }

    public function getLeaderboard(int $limit = 10): array
    {
        return $this->db->select(
            'SELECT id, name, credibility_score, approved_reports, state FROM users WHERE role_id = 4 ORDER BY credibility_score DESC LIMIT ?',
            [$limit]
        );
    }

    public function incrementCredibility(int $userId, int $points = 1): void
    {
        $this->db->query('UPDATE users SET credibility_score = credibility_score + ?, approved_reports = approved_reports + 1 WHERE id = ?', [$points, $userId]);
    }
}
