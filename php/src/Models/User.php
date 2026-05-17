<?php
namespace NetaTrack\Models;

use NetaTrack\Core\Model;
use NetaTrack\Core\Database;

class User extends Model {
    protected static string $table = 'users';

    public static function findByEmail(string $email): ?array {
        return Database::fetch("SELECT * FROM users WHERE email = ? LIMIT 1", [$email]);
    }

    public static function create(array $data): int {
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        $data['credibility_score'] = 0;
        $data['status'] = 'active';
        return parent::create($data);
    }

    public static function verify(string $email, string $password): ?array {
        $user = static::findByEmail($email);
        if ($user && password_verify($password, $user['password'])) return $user;
        return null;
    }

    public static function updateCredibility(int $userId, int $delta): void {
        Database::query("UPDATE users SET credibility_score = credibility_score + ? WHERE id = ?", [$delta, $userId]);
    }
}
