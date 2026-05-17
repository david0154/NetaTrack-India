<?php
namespace NetaTrack\Models;

use NetaTrack\Core\Model;

/**
 * NetaTrack India - User Model
 */
class User extends Model
{
    protected string $table = 'users';
    protected array $fillable = [
        'name','email','password','role','status',
        'phone','avatar','bio','credibility_score',
        'email_verified_at','last_login_at','ip_address',
        'created_at','updated_at'
    ];
    protected array $hidden = ['password'];

    public function createUser(array $data): int
    {
        $data['password']           = password_hash($data['password'], PASSWORD_BCRYPT, ['cost'=>12]);
        $data['role']               = $data['role'] ?? 'public';
        $data['status']             = 'active';
        $data['credibility_score']  = 0;
        return $this->create($data);
    }

    public function findByEmail(string $email): ?array
    {
        return $this->findBy('email', $email);
    }

    public function updateCredibility(int $userId, int $delta): void
    {
        $this->db->query(
            'UPDATE users SET credibility_score = GREATEST(0, credibility_score + ?) WHERE id=?',
            [$delta, $userId]
        );
    }

    public function getAdmins(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM users WHERE role IN ('super_admin','admin','moderator','editor') AND status='active' ORDER BY role"
        );
    }

    public function ban(int $userId): void
    {
        $this->db->query('UPDATE users SET status=? WHERE id=?', ['banned', $userId]);
    }

    public function getStats(): array
    {
        return [
            'total'   => $this->count(),
            'active'  => $this->count("status='active'"),
            'banned'  => $this->count("status='banned'"),
            'admins'  => $this->count("role IN ('super_admin','admin','moderator','editor')"),
        ];
    }
}
