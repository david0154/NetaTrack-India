<?php
namespace NetaTrack\Models;

use NetaTrack\Core\Database;

class Promise
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function all(array $filters = [], int $page = 1, int $perPage = 18): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['q'])) {
            $where[]  = '(p.title LIKE :q OR p.description LIKE :q)';
            $params[':q'] = '%'.$filters['q'].'%';
        }
        if (!empty($filters['status'])) {
            $where[]  = 'p.status = :status';
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['leader_id'])) {
            $where[]  = 'p.leader_id = :leader_id';
            $params[':leader_id'] = $filters['leader_id'];
        }
        if (!empty($filters['state_id'])) {
            $where[]  = 'l.state_id = :state_id';
            $params[':state_id'] = $filters['state_id'];
        }

        $whereStr = implode(' AND ', $where);
        $offset   = ($page - 1) * $perPage;

        $countSql  = "SELECT COUNT(*) FROM promises p LEFT JOIN leaders l ON p.leader_id=l.id WHERE $whereStr";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $sql = "SELECT p.*, l.name AS leader_name, l.slug AS leader_slug,
                       s.name AS state_name
                FROM promises p
                LEFT JOIN leaders l ON p.leader_id = l.id
                LEFT JOIN states s ON l.state_id = s.id
                WHERE $whereStr
                ORDER BY p.created_at DESC
                LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit',  $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,  \PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data'         => $stmt->fetchAll(\PDO::FETCH_ASSOC),
            'total'        => $total,
            'current_page' => $page,
            'last_page'    => max(1, (int)ceil($total / $perPage)),
        ];
    }

    public function byLeader(int $leaderId, int $page = 1, int $perPage = 12): array
    {
        return $this->all(['leader_id' => $leaderId], $page, $perPage);
    }

    public function stats(?int $leaderId = null): array
    {
        $where  = $leaderId ? 'WHERE leader_id=:lid' : '';
        $params = $leaderId ? [':lid' => $leaderId] : [];

        $rows = $this->db->prepare(
            "SELECT status, COUNT(*) AS cnt FROM promises $where GROUP BY status"
        );
        $rows->execute($params);
        $data  = ['total'=>0,'kept'=>0,'broken'=>0,'in_progress'=>0,'partial'=>0,'expired'=>0];
        foreach ($rows->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $data[$row['status']] = (int)$row['cnt'];
            $data['total'] += (int)$row['cnt'];
        }
        return $data;
    }

    public function recent(int $limit = 6): array
    {
        $stmt = $this->db->prepare(
            "SELECT p.*, l.name AS leader_name, l.slug AS leader_slug, s.name AS state_name
             FROM promises p
             LEFT JOIN leaders l ON p.leader_id=l.id
             LEFT JOIN states s ON l.state_id=s.id
             ORDER BY p.created_at DESC LIMIT :lim"
        );
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM promises WHERE id=:id LIMIT 1");
        $stmt->execute([':id'=>$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function create(array $data): int
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $cols   = implode(', ', array_keys($data));
        $places = implode(', ', array_map(fn($k)=>":$k", array_keys($data)));
        $this->db->prepare("INSERT INTO promises ($cols) VALUES ($places)")->execute($data);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $sets = implode(', ', array_map(fn($k)=>"$k=:$k", array_keys($data)));
        $data['id'] = $id;
        return $this->db->prepare("UPDATE promises SET $sets WHERE id=:id")->execute($data);
    }

    public function delete(int $id): bool
    {
        return $this->db->prepare("DELETE FROM promises WHERE id=:id")->execute([':id'=>$id]);
    }
}
