<?php
namespace NetaTrack\Models;

use NetaTrack\Core\Database;

class User
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id=:id LIMIT 1");
        $stmt->execute([':id'=>$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email=:email LIMIT 1");
        $stmt->execute([':email'=>$email]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function all(array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $where  = ['1=1'];
        $params = [];
        if (!empty($filters['q'])) {
            $where[]  = '(name LIKE :q OR email LIKE :q)';
            $params[':q'] = '%'.$filters['q'].'%';
        }
        if (!empty($filters['role'])) {
            $where[]  = 'role = :role';
            $params[':role'] = $filters['role'];
        }
        $whereStr = implode(' AND ', $where);
        $offset   = ($page - 1) * $perPage;
        $cStmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE $whereStr");
        $cStmt->execute($params);
        $total = (int)$cStmt->fetchColumn();
        $stmt  = $this->db->prepare(
            "SELECT id,name,email,role,status,created_at FROM users WHERE $whereStr ORDER BY created_at DESC LIMIT :lim OFFSET :off"
        );
        foreach ($params as $k=>$v) $stmt->bindValue($k,$v);
        $stmt->bindValue(':lim', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset,  \PDO::PARAM_INT);
        $stmt->execute();
        return [
            'data'         => $stmt->fetchAll(\PDO::FETCH_ASSOC),
            'total'        => $total,
            'current_page' => $page,
            'last_page'    => max(1,(int)ceil($total/$perPage)),
        ];
    }

    public function create(array $data): int
    {
        $data['password']   = password_hash($data['password'], PASSWORD_BCRYPT, ['cost'=>12]);
        $data['role']       = $data['role']       ?? 'user';
        $data['status']     = $data['status']     ?? 'active';
        $data['created_at'] = $data['updated_at'] = date('Y-m-d H:i:s');
        $cols   = implode(', ', array_keys($data));
        $places = implode(', ', array_map(fn($k)=>":$k", array_keys($data)));
        $this->db->prepare("INSERT INTO users ($cols) VALUES ($places)")->execute($data);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost'=>12]);
        }
        $data['updated_at'] = date('Y-m-d H:i:s');
        $sets = implode(', ', array_map(fn($k)=>"$k=:$k", array_keys($data)));
        $data['id'] = $id;
        return $this->db->prepare("UPDATE users SET $sets WHERE id=:id")->execute($data);
    }

    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public function ban(int $id): bool   { return $this->update($id, ['status'=>'banned']); }
    public function unban(int $id): bool { return $this->update($id, ['status'=>'active']); }
}
