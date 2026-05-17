<?php
namespace NetaTrack\Core;

/**
 * NetaTrack India - Base Model
 */
abstract class Model
{
    protected Database $db;
    protected string $table = '';
    protected string $primaryKey = 'id';
    protected array $fillable = [];
    protected array $hidden = ['password'];
    protected bool $timestamps = true;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function find(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey}=? LIMIT 1",
            [$id]
        );
    }

    public function findBy(string $column, mixed $value): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM {$this->table} WHERE {$column}=? LIMIT 1",
            [$value]
        );
    }

    public function all(string $orderBy = 'id', string $dir = 'ASC'): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} ORDER BY {$orderBy} {$dir}"
        );
    }

    public function paginate(int $page = 1, int $perPage = 20, string $where = '1', array $params = []): array
    {
        $offset = ($page - 1) * $perPage;
        $total  = $this->db->fetch("SELECT COUNT(*) as cnt FROM {$this->table} WHERE {$where}", $params)['cnt'];
        $rows   = $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE {$where} LIMIT {$perPage} OFFSET {$offset}",
            $params
        );
        return [
            'data'        => $rows,
            'total'       => (int)$total,
            'per_page'    => $perPage,
            'current_page'=> $page,
            'last_page'   => (int)ceil($total / $perPage),
        ];
    }

    public function create(array $data): int
    {
        if ($this->timestamps) {
            $data['created_at'] = $data['updated_at'] = date('Y-m-d H:i:s');
        }
        $data = $this->filterFillable($data);
        return $this->db->insert($this->table, $data);
    }

    public function update(int $id, array $data): int
    {
        if ($this->timestamps) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        $data = $this->filterFillable($data);
        return $this->db->update($this->table, $data, "{$this->primaryKey}=?", [$id]);
    }

    public function delete(int $id): int
    {
        return $this->db->delete($this->table, "{$this->primaryKey}=?", [$id]);
    }

    public function count(string $where = '1', array $params = []): int
    {
        return (int)($this->db->fetch(
            "SELECT COUNT(*) as cnt FROM {$this->table} WHERE {$where}", $params
        )['cnt'] ?? 0);
    }

    protected function filterFillable(array $data): array
    {
        if (empty($this->fillable)) return $data;
        return array_intersect_key($data, array_flip($this->fillable));
    }
}
