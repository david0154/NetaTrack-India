<?php
namespace NetaTrack\Models;

class Setting extends BaseModel
{
    protected string $table = 'settings';
    private static array $cache = [];

    public function get(string $key, mixed $default = null): mixed
    {
        if (isset(static::$cache[$key])) {
            return static::$cache[$key];
        }
        $row = $this->db->selectOne('SELECT value FROM settings WHERE `key` = ?', [$key]);
        $value = $row ? $row['value'] : $default;
        static::$cache[$key] = $value;
        return $value;
    }

    public function set(string $key, mixed $value): void
    {
        $exists = $this->db->selectOne('SELECT id FROM settings WHERE `key` = ?', [$key]);
        if ($exists) {
            $this->db->update('settings', ['value' => $value, 'updated_at' => date('Y-m-d H:i:s')], '`key` = ?', [$key]);
        } else {
            $this->db->insert('settings', ['key' => $key, 'value' => $value]);
        }
        static::$cache[$key] = $value;
    }

    public function getGroup(string $group): array
    {
        $rows = $this->db->select('SELECT `key`, `value`, `type`, `label` FROM settings WHERE `group` = ?', [$group]);
        $result = [];
        foreach ($rows as $row) {
            $result[$row['key']] = $row;
        }
        return $result;
    }

    public function setMultiple(array $data): void
    {
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }
    }

    public function clearCache(): void
    {
        static::$cache = [];
    }
}
