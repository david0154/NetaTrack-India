<?php
namespace NetaTrack\Core;

use PDO;
use PDOException;

/**
 * NetaTrack India - Database Singleton (PDO)
 */
class Database
{
    private static ?Database $instance = null;
    private ?PDO $connection = null;
    private array $config;

    private function __construct()
    {
        $cfg = require ROOT_PATH . '/config/database.php';
        $this->config = $cfg['connections'][$cfg['default']];
        $this->connect();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect(): void
    {
        $c = $this->config;
        $dsn = "{$c['driver']}:host={$c['host']};port={$c['port']};dbname={$c['database']};charset={$c['charset']}";
        try {
            $this->connection = new PDO($dsn, $c['username'], $c['password'], $c['options']);
        } catch (PDOException $e) {
            error_log('[DB] Connection failed: ' . $e->getMessage());
            if (APP_DEBUG) throw $e;
            http_response_code(503);
            die('Database connection error. Please try again later.');
        }
    }

    public function pdo(): PDO
    {
        return $this->connection;
    }

    /**
     * Execute a query and return PDOStatement
     */
    public function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetch(string $sql, array $params = []): ?array
    {
        return $this->query($sql, $params)->fetch() ?: null;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    public function insert(string $table, array $data): int
    {
        $cols = implode(',', array_keys($data));
        $vals = implode(',', array_fill(0, count($data), '?'));
        $this->query("INSERT INTO {$table} ({$cols}) VALUES ({$vals})", array_values($data));
        return (int)$this->connection->lastInsertId();
    }

    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $set = implode(',', array_map(fn($k) => "{$k}=?", array_keys($data)));
        $stmt = $this->query("UPDATE {$table} SET {$set} WHERE {$where}",
            array_merge(array_values($data), $whereParams));
        return $stmt->rowCount();
    }

    public function delete(string $table, string $where, array $params = []): int
    {
        return $this->query("DELETE FROM {$table} WHERE {$where}", $params)->rowCount();
    }

    public function lastInsertId(): int
    {
        return (int)$this->connection->lastInsertId();
    }

    public function beginTransaction(): void { $this->connection->beginTransaction(); }
    public function commit(): void           { $this->connection->commit(); }
    public function rollback(): void         { $this->connection->rollBack(); }

    private function __clone() {}
    public function __wakeup() { throw new \RuntimeException('Cannot unserialize singleton'); }
}
