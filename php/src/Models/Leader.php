<?php
namespace NetaTrack\Models;

use NetaTrack\Core\Database;

class Leader
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

        if (!empty($filters['q'])) {
            $where[]  = '(l.name LIKE :q OR l.constituency LIKE :q OR l.designation LIKE :q)';
            $params[':q'] = '%'.$filters['q'].'%';
        }
        if (!empty($filters['state'])) {
            $where[]  = 's.slug = :state';
            $params[':state'] = $filters['state'];
        }
        if (!empty($filters['party'])) {
            $where[]  = 'l.party_id = :party';
            $params[':party'] = $filters['party'];
        }
        if (!empty($filters['rank'])) {
            $where[]  = 'l.score_rank = :rank';
            $params[':rank'] = $filters['rank'];
        }
        if (isset($filters['status'])) {
            $where[]  = 'l.status = :status';
            $params[':status'] = $filters['status'];
        }

        $sort = match($filters['sort'] ?? 'score') {
            'name'   => 'l.name ASC',
            'recent' => 'l.created_at DESC',
            default  => 'l.total_score DESC',
        };

        $whereStr = implode(' AND ', $where);
        $offset   = ($page - 1) * $perPage;

        $countSql = "SELECT COUNT(*) FROM leaders l
            LEFT JOIN states s ON l.state_id = s.id
            LEFT JOIN parties p ON l.party_id = p.id
            WHERE $whereStr";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $sql = "SELECT l.*, s.name AS state_name, s.slug AS state_slug,
                       p.name AS party_name, p.abbreviation AS party_abbr,
                       p.color AS party_color
                FROM leaders l
                LEFT JOIN states s ON l.state_id = s.id
                LEFT JOIN parties p ON l.party_id = p.id
                WHERE $whereStr
                ORDER BY $sort
                LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit',  $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,  \PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data'         => $stmt->fetchAll(\PDO::FETCH_ASSOC),
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => max(1, (int)ceil($total / $perPage)),
        ];
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT l.*, s.name AS state_name, s.slug AS state_slug,
                    p.name AS party_name, p.abbreviation AS party_abbr, p.color AS party_color
             FROM leaders l
             LEFT JOIN states s ON l.state_id = s.id
             LEFT JOIN parties p ON l.party_id = p.id
             WHERE l.slug = :slug AND l.status = 'active' LIMIT 1"
        );
        $stmt->execute([':slug' => $slug]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT l.*, s.name AS state_name, p.name AS party_name,
                    p.abbreviation AS party_abbr, p.color AS party_color
             FROM leaders l
             LEFT JOIN states s ON l.state_id = s.id
             LEFT JOIN parties p ON l.party_id = p.id
             WHERE l.id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function topRanked(int $limit = 8): array
    {
        $stmt = $this->db->prepare(
            "SELECT l.*, s.name AS state_name, p.name AS party_name,
                    p.abbreviation AS party_abbr, p.color AS party_color
             FROM leaders l
             LEFT JOIN states s ON l.state_id = s.id
             LEFT JOIN parties p ON l.party_id = p.id
             WHERE l.status = 'active'
             ORDER BY l.total_score DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function corruptionRanked(array $filters = [], int $limit = 50): array
    {
        $where  = ['1=1'];
        $params = [];
        if (!empty($filters['state_id'])) {
            $where[]  = 'l.state_id = :state_id';
            $params[':state_id'] = $filters['state_id'];
        }
        $sort = match($filters['sort'] ?? 'cases') {
            'reports' => 'l.corruption_reports DESC',
            'score'   => 'l.total_score ASC',
            default   => 'l.criminal_cases DESC',
        };
        $whereStr = implode(' AND ', $where);
        $stmt = $this->db->prepare(
            "SELECT l.*, s.name AS state_name, p.name AS party_name,
                    p.abbreviation AS party_abbr, p.color AS party_color
             FROM leaders l
             LEFT JOIN states s ON l.state_id = s.id
             LEFT JOIN parties p ON l.party_id = p.id
             WHERE $whereStr
             ORDER BY $sort
             LIMIT :limit"
        );
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function create(array $data): int
    {
        $data['slug']       = $data['slug'] ?? $this->makeSlug($data['name']);
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->recalcScore($data);
        $cols   = implode(', ', array_keys($data));
        $places = implode(', ', array_map(fn($k)=>":$k", array_keys($data)));
        $stmt   = $this->db->prepare("INSERT INTO leaders ($cols) VALUES ($places)");
        $stmt->execute($data);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->recalcScore($data);
        $sets = implode(', ', array_map(fn($k)=>"$k=:$k", array_keys($data)));
        $stmt = $this->db->prepare("UPDATE leaders SET $sets WHERE id=:id");
        $data['id'] = $id;
        return $stmt->execute($data);
    }

    public function delete(int $id): bool
    {
        return $this->db->prepare("DELETE FROM leaders WHERE id=:id")->execute([':id'=>$id]);
    }

    public function stats(): array
    {
        return [
            'total'     => (int)$this->db->query("SELECT COUNT(*) FROM leaders WHERE status='active'")->fetchColumn(),
            'verified'  => (int)$this->db->query("SELECT COUNT(*) FROM leaders WHERE is_verified=1")->fetchColumn(),
            'excellent' => (int)$this->db->query("SELECT COUNT(*) FROM leaders WHERE score_rank='Excellent'")->fetchColumn(),
        ];
    }

    private function recalcScore(array &$data): void
    {
        $fields = ['score_promise_completion','score_project_delivery','score_transparency',
                   'score_public_satisfaction','score_attendance','score_criminal_record',
                   'score_assets_declared','score_social_media_activity'];
        $total  = 0;
        $count  = 0;
        foreach ($fields as $f) {
            if (isset($data[$f])) { $total += (int)$data[$f]; $count++; }
        }
        if ($count > 0) {
            $avg = round($total / $count);
            $data['total_score'] = $avg;
            $data['score_rank']  = match(true) {
                $avg >= 90 => 'Excellent',
                $avg >= 75 => 'Good',
                $avg >= 50 => 'Average',
                default    => 'Poor',
            };
        }
    }

    private function makeSlug(string $name): string
    {
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
        $slug = trim($slug, '-');
        $exists = $this->db->prepare("SELECT COUNT(*) FROM leaders WHERE slug=:s");
        $exists->execute([':s'=>$slug]);
        if ($exists->fetchColumn() > 0) $slug .= '-'.rand(100,999);
        return $slug;
    }
}
