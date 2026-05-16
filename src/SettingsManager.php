<?php
require_once __DIR__ . '/Database.php';

class SettingsManager
{
    private static array $cache = [];

    public static function get(string $key, $default = null)
    {
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }
        $row = Database::queryOne('SELECT value, type FROM settings WHERE key_name = ?', [$key]);
        if (!$row) return $default;
        $value = self::cast($row['value'], $row['type']);
        self::$cache[$key] = $value;
        return $value;
    }

    public static function set(string $key, $value, string $type = 'text', int $updatedBy = 0): void
    {
        self::$cache[$key] = $value;
        $stored = is_array($value) ? json_encode($value) : (string) $value;
        Database::execute(
            'INSERT INTO settings (key_name, value, type, updated_by) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE value = ?, updated_by = ?',
            [$key, $stored, $type, $updatedBy, $stored, $updatedBy]
        );
    }

    public static function all(string $group = null): array
    {
        $sql    = 'SELECT key_name, value, type, group_name, label FROM settings';
        $params = [];
        if ($group) {
            $sql   .= ' WHERE group_name = ?';
            $params[] = $group;
        }
        $rows = Database::query($sql, $params);
        $result = [];
        foreach ($rows as $row) {
            $result[$row['key_name']] = self::cast($row['value'], $row['type']);
        }
        return $result;
    }

    private static function cast($value, string $type)
    {
        return match ($type) {
            'bool'   => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'number' => is_numeric($value) ? (float) $value : 0,
            'json'   => json_decode($value, true),
            default  => $value,
        };
    }
}
