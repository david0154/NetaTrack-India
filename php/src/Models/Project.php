<?php
namespace NetaTrack\Models;

use NetaTrack\Core\Database;

class Project
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
            $where[]  = '(pr.title LIKE :q OR pr.description LIKE :q)';
            $params[':q'] = '%'.$filters['q'].'%';
        }
        if (!empty($filters['status'])) {
            $where[]  = 'pr.status = :status';
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['state_id'])) {
            $where[]  = 'pr.state_id = :state_id';
            $params[':state_id'] = $filters['state_id'];
        }
        if (!empty($filters['leader_id'])) {
            $where[]  = 'pr.leader_id = :leader_id';
            $params[':leader_id'] = $filters['leader_id'];
        }

        $whereStr = implode(' AND ', $where);
        $offset   = ($page - 1) * $perPage;

        $total = (int)$this->db->prepare(
            "SELECT COUNT(*) FROM projects pr WHERE $whereStr"
        )->execute($params) ? $this->db->prepare(
            "SELECT COUNT(*) FROM projects pr WHERE $whereStr"
        )->execute($params) : 0;

        // Re-execute for count
        $cStmt = $this->db->prepare("SELECT COUNT(*) FROM projects pr WHERE $whereStr");
        $cStmt->execute($params);
        $total = (int)$cStmt->fetchColumn();

        $sql = "SELECT pr.*, l.name AS leader_name, l.slug AS leader_slug,
                       s.name AS state_name
                FROM projects pr
                LEFT JOIN leaders l ON pr.leader_id = l.id
                LEFT JOIN states s ON pr.state_id = s.id
                WHERE $whereStr
                ORDER BY pr.created_at DESC
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

    public function byLeader(int $leaderId): array
    {
        $stmt = $this->db->prepare(
            "SELECT pr.*, s.name AS state_name FROM projects pr
             LEFT JOIN states s ON pr.state_id=s.id
             WHERE pr.leader_id=:lid ORDER BY pr.created_at DESC"
        );
        $stmt->execute([':lid'=>$leaderId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function stats(): array
    {
        $rows = $this->db->query("SELECT status, COUNT(*) AS cnt FROM projects GROUP BY status")->fetchAll(\PDO::FETCH_ASSOC);
        $data = ['total'=>0,'completed'=>0,'in_progress'=>0,'planned'=>0,'delayed'=>0,'cancelled'=>0,'total_budget'=>0];
        foreach ($rows as $row) { $data[$row['status']] = (int)$row['cnt']; $data['total'] += (int)$row['cnt']; }
        $data['total_budget'] = (int)$this->db->query("SELECT COALESCE(SUM(budget_crore),0) FROM projects")->fetchColumn();
        return $data;
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM projects WHERE id=:id LIMIT 1");
        $stmt->execute([':id'=>$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function create(array $data): int
    {
        $data['created_at'] = $data['updated_at'] = date('Y-m-d H:i:s');
        $cols   = implode(', ', array_keys($data));
        $places = implode(', ', array_map(fn($k)=>":$k", array_keys($data)));
        $this->db->prepare("INSERT INTO projects ($cols) VALUES ($places)")->execute($data);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $sets = implode(', ', array_map(fn($k)=>"$k=:$k", array_keys($data)));
        $data['id'] = $id;
        return $this->db->prepare("UPDATE projects SET $sets WHERE id=:id")->execute($data);
    }

    public function delete(int $id): bool
    {
        return $this->db->prepare("DELETE FROM projects WHERE id=:id")->execute([':id'=>$id]);
    }
}
