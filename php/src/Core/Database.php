<?php
namespace NetaTrack\Core;

use PDO;
use PDOException;

class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        $config = require ROOT_PATH . '/config/database.php';
        $db = $config['connections'][$config['default']];

        $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['database']};charset={$db['charset']}";

        try {
            $this->pdo = new PDO($dsn, $db['username'], $db['password'], $db['options']);
        } catch (PDOException $e) {
            if (defined('APP_DEBUG') && APP_DEBUG) {
                throw $e;
            }
            http_response_code(500);
            die(json_encode(['error' => 'Database connection failed']));
        }
    }

    public static function getInstance(): static
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    public function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function select(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    public function selectOne(string $sql, array $params = []): array|false
    {
        return $this->query($sql, $params)->fetch();
    }

    public function insert(string $table, array $data): int
    {
        $columns = implode(', ', array_map(fn($k) => "`$k`", array_keys($data)));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO `$table` ($columns) VALUES ($placeholders)";
        $this->query($sql, array_values($data));
        return (int)$this->pdo->lastInsertId();
    }

    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $set = implode(', ', array_map(fn($k) => "`$k` = ?", array_keys($data)));
        $sql = "UPDATE `$table` SET $set WHERE $where";
        $stmt = $this->query($sql, array_merge(array_values($data), $whereParams));
        return $stmt->rowCount();
    }

    public function delete(string $table, string $where, array $params = []): int
    {
        $stmt = $this->query("DELETE FROM `$table` WHERE $where", $params);
        return $stmt->rowCount();
    }

    public function beginTransaction(): void { $this->pdo->beginTransaction(); }
    public function commit(): void { $this->pdo->commit(); }
    public function rollback(): void { $this->pdo->rollBack(); }

    public function lastInsertId(): int { return (int)$this->pdo->lastInsertId(); }

    private function __clone() {}
}
