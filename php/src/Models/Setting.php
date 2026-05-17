<?php
namespace NetaTrack\Models;

use NetaTrack\Core\Database;

class Setting
{
    private \PDO $db;
    private static array $cache = [];

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (isset(self::$cache[$key])) return self::$cache[$key];
        $stmt = $this->db->prepare("SELECT value FROM settings WHERE `key`=:k LIMIT 1");
        $stmt->execute([':k'=>$key]);
        $val = $stmt->fetchColumn();
        self::$cache[$key] = ($val !== false) ? $val : $default;
        return self::$cache[$key];
    }

    public function set(string $key, mixed $value): void
    {
        $this->db->prepare(
            "INSERT INTO settings (`key`,`value`) VALUES (:k,:v)
             ON DUPLICATE KEY UPDATE `value`=:v2"
        )->execute([':k'=>$key,':v'=>$value,':v2'=>$value]);
        self::$cache[$key] = $value;
    }

    public function all(): array
    {
        return $this->db->query("SELECT `key`,`value` FROM settings ORDER BY `key`")
                        ->fetchAll(\PDO::FETCH_KEY_PAIR);
    }

    public function bulkSet(array $data): void
    {
        foreach ($data as $k => $v) $this->set($k, $v);
    }
}
