<?php
namespace NetaTrack\Models;

use NetaTrack\Core\Database;

abstract class BaseModel
{
    protected string $table = '';
    protected string $primaryKey = 'id';
    protected Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function find(int $id): array|false
    {
        return $this->db->selectOne(
            "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = ? LIMIT 1",
            [$id]
        );
    }

    public function findByUuid(string $uuid): array|false
    {
        return $this->db->selectOne(
            "SELECT * FROM `{$this->table}` WHERE uuid = ? LIMIT 1",
            [$uuid]
        );
    }

    public function all(string $orderBy = 'id', string $dir = 'DESC'): array
    {
        return $this->db->select("SELECT * FROM `{$this->table}` ORDER BY `$orderBy` $dir");
    }

    public function paginate(int $page = 1, int $perPage = 20, string $where = '1=1', array $params = [], string $orderBy = 'id', string $dir = 'DESC'): array
    {
        $offset = ($page - 1) * $perPage;
        $total = $this->db->selectOne("SELECT COUNT(*) as count FROM `{$this->table}` WHERE $where", $params)['count'];
        $data = $this->db->select(
            "SELECT * FROM `{$this->table}` WHERE $where ORDER BY `$orderBy` $dir LIMIT $perPage OFFSET $offset",
            $params
        );
        return [
            'data'         => $data,
            'total'        => (int)$total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int)ceil($total / $perPage),
        ];
    }

    public function create(array $data): int
    {
        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        return $this->db->insert($this->table, $data);
    }

    public function update(int $id, array $data): int
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->update($this->table, $data, "`{$this->primaryKey}` = ?", [$id]);
    }

    public function delete(int $id): int
    {
        return $this->db->delete($this->table, "`{$this->primaryKey}` = ?", [$id]);
    }

    public function count(string $where = '1=1', array $params = []): int
    {
        $result = $this->db->selectOne("SELECT COUNT(*) as count FROM `{$this->table}` WHERE $where", $params);
        return (int)($result['count'] ?? 0);
    }

    public function generateUuid(): string
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
