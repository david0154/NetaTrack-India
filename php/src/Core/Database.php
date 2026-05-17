<?php
namespace NetaTrack\Core;

class Database
{
    private static ?self $instance = null;
    private \PDO $pdo;

    private function __construct()
    {
        $cfg = config('database');
        $dsn = "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['name']};charset=utf8mb4";
        $this->pdo = new \PDO($dsn, $cfg['user'], $cfg['pass'], [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        ]);
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) self::$instance = new self();
        return self::$instance;
    }

    public function getConnection(): \PDO { return $this->pdo; }

    /** Run a raw SQL file (for migrations) */
    public function runMigration(string $filePath): void
    {
        $sql = file_get_contents($filePath);
        $this->pdo->exec($sql);
    }

    /** Convenience: prepare + execute + fetchAll */
    public function select(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Convenience: prepare + execute, returns last insert id */
    public function insert(string $sql, array $params = []): int
    {
        $this->pdo->prepare($sql)->execute($params);
        return (int)$this->pdo->lastInsertId();
    }

    /** Convenience: prepare + execute, returns affected rows */
    public function statement(string $sql, array $params = []): int
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public function beginTransaction(): void  { $this->pdo->beginTransaction(); }
    public function commit(): void            { $this->pdo->commit(); }
    public function rollback(): void          { $this->pdo->rollBack(); }
}
