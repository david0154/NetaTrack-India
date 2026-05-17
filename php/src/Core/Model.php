<?php
namespace NetaTrack\Core;

abstract class Model {
    protected static string $table = '';
    protected static string $primaryKey = 'id';

    public static function all(string $orderBy = 'id DESC'): array {
        return Database::fetchAll("SELECT * FROM " . static::$table . " ORDER BY $orderBy");
    }

    public static function find(int $id): ?array {
        return Database::fetch("SELECT * FROM " . static::$table . " WHERE " . static::$primaryKey . " = ?", [$id]);
    }

    public static function findBy(string $col, mixed $val): ?array {
        return Database::fetch("SELECT * FROM " . static::$table . " WHERE $col = ? LIMIT 1", [$val]);
    }

    public static function where(string $condition, array $params = [], string $order = ''): array {
        $sql = "SELECT * FROM " . static::$table . " WHERE $condition";
        if ($order) $sql .= " ORDER BY $order";
        return Database::fetchAll($sql, $params);
    }

    public static function create(array $data): int {
        if (!isset($data['created_at'])) $data['created_at'] = date('Y-m-d H:i:s');
        if (!isset($data['updated_at'])) $data['updated_at'] = date('Y-m-d H:i:s');
        return Database::insert(static::$table, $data);
    }

    public static function update(int $id, array $data): int {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return Database::update(static::$table, $data, static::$primaryKey . " = ?", [$id]);
    }

    public static function delete(int $id): int {
        return Database::delete(static::$table, static::$primaryKey . " = ?", [$id]);
    }

    public static function count(string $condition = '1', array $params = []): int {
        return (int)(Database::fetch("SELECT COUNT(*) as c FROM " . static::$table . " WHERE $condition", $params)['c'] ?? 0);
    }

    public static function paginate(int $page = 1, int $perPage = 20, string $condition = '1', array $params = [], string $order = 'id DESC'): array {
        $total  = static::count($condition, $params);
        $offset = ($page - 1) * $perPage;
        $items  = Database::fetchAll("SELECT * FROM " . static::$table . " WHERE $condition ORDER BY $order LIMIT $perPage OFFSET $offset", $params);
        return [
            'items'        => $items,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int)ceil($total / $perPage),
        ];
    }
}
