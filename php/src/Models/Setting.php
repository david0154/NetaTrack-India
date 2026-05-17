<?php
namespace NetaTrack\Models;

use NetaTrack\Core\Model;
use NetaTrack\Core\Database;

class Setting extends Model {
    protected static string $table = 'settings';
    private static array $cache = [];

    public static function get(string $key, mixed $default = null): mixed {
        if (isset(self::$cache[$key])) return self::$cache[$key];
        $row = Database::fetch("SELECT value FROM settings WHERE `key` = ? LIMIT 1", [$key]);
        $val = $row ? $row['value'] : $default;
        self::$cache[$key] = $val;
        return $val;
    }

    public static function set(string $key, mixed $value): void {
        $exists = Database::fetch("SELECT id FROM settings WHERE `key` = ?", [$key]);
        if ($exists) {
            Database::update('settings', ['value' => $value, 'updated_at' => date('Y-m-d H:i:s')], '`key` = ?', [$key]);
        } else {
            Database::insert('settings', ['key' => $key, 'value' => $value, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);
        }
        self::$cache[$key] = $value;
    }

    public static function getAll(): array {
        $rows = Database::fetchAll("SELECT * FROM settings");
        $result = [];
        foreach ($rows as $row) {
            $result[$row['key']] = $row['value'];
            self::$cache[$row['key']] = $row['value'];
        }
        return $result;
    }
}
