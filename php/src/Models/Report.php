<?php
namespace NetaTrack\Models;

use NetaTrack\Core\Database;

class Report
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function all(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['status'])) {
            $where[]  = 'r.status = :status';
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['type'])) {
            $where[]  = 'r.type = :type';
            $params[':type'] = $filters['type'];
        }
        if (!empty($filters['leader_id'])) {
            $where[]  = 'r.leader_id = :leader_id';
            $params[':leader_id'] = $filters['leader_id'];
        }
        if (!empty($filters['q'])) {
            $where[]  = '(r.title LIKE :q OR r.description LIKE :q)';
            $params[':q'] = '%'.$filters['q'].'%';
        }

        $whereStr = implode(' AND ', $where);
        $offset   = ($page - 1) * $perPage;

        $cStmt = $this->db->prepare("SELECT COUNT(*) FROM reports r WHERE $whereStr");
        $cStmt->execute($params);
        $total = (int)$cStmt->fetchColumn();

        $sql = "SELECT r.*, l.name AS leader_name, l.slug AS leader_slug,
                       s.name AS state_name
                FROM reports r
                LEFT JOIN leaders l ON r.leader_id = l.id
                LEFT JOIN states s ON r.state_id = s.id
                WHERE $whereStr
                ORDER BY r.created_at DESC
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

    public function byLeader(int $leaderId, int $limit = 10): array
    {
        $stmt = $this->db->prepare(
            "SELECT r.*, s.name AS state_name FROM reports r
             LEFT JOIN states s ON r.state_id=s.id
             WHERE r.leader_id=:lid AND r.status='approved'
             ORDER BY r.created_at DESC LIMIT :lim"
        );
        $stmt->bindValue(':lid', $leaderId, \PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit,    \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function pending(int $limit = 10): array
    {
        $stmt = $this->db->prepare(
            "SELECT r.*, l.name AS leader_name FROM reports r
             LEFT JOIN leaders l ON r.leader_id=l.id
             WHERE r.status='pending'
             ORDER BY r.created_at DESC LIMIT :lim"
        );
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM reports WHERE id=:id LIMIT 1");
        $stmt->execute([':id'=>$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function create(array $data): int
    {
        $data['created_at'] = $data['updated_at'] = date('Y-m-d H:i:s');
        $data['status']     = $data['status'] ?? 'pending';
        $data['ai_confidence'] = $data['ai_confidence'] ?? 0;
        $cols   = implode(', ', array_keys($data));
        $places = implode(', ', array_map(fn($k)=>":$k", array_keys($data)));
        $this->db->prepare("INSERT INTO reports ($cols) VALUES ($places)")->execute($data);
        return (int)$this->db->lastInsertId();
    }

    public function updateStatus(int $id, string $status, ?string $adminNote = null): bool
    {
        $data = ['status'=>$status,'updated_at'=>date('Y-m-d H:i:s'),'id'=>$id];
        if ($adminNote !== null) $data['admin_note'] = $adminNote;
        $sets = implode(', ', array_map(fn($k)=>"$k=:$k", array_diff(array_keys($data),['id'])));
        return $this->db->prepare("UPDATE reports SET $sets WHERE id=:id")->execute($data);
    }

    public function stats(): array
    {
        $rows = $this->db->query("SELECT status, COUNT(*) AS cnt FROM reports GROUP BY status")->fetchAll(\PDO::FETCH_ASSOC);
        $data = ['total'=>0,'pending'=>0,'approved'=>0,'rejected'=>0];
        foreach ($rows as $row) { $data[$row['status']] = (int)$row['cnt']; $data['total'] += (int)$row['cnt']; }
        $data['fake_detected']      = (int)$this->db->query("SELECT COUNT(*) FROM reports WHERE type='fake_claim' AND status='approved'")->fetchColumn();
        $data['leaders_with_cases'] = (int)$this->db->query("SELECT COUNT(*) FROM leaders WHERE criminal_cases>0")->fetchColumn();
        $data['resolved']           = (int)$this->db->query("SELECT COUNT(*) FROM reports WHERE status='rejected'")->fetchColumn();
        $data['total_reports']      = $data['total'];
        return $data;
    }
}
