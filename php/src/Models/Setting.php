<?php
namespace NetaTrack\Models;

use NetaTrack\Core\Model;

/**
 * NetaTrack India - Site Settings Model
 */
class Setting extends Model
{
    protected string $table = 'settings';
    protected array $fillable = ['key','value','group','type','label','updated_at'];
    private array $cache = [];

    public function get(string $key, mixed $default = null): mixed
    {
        if (isset($this->cache[$key])) return $this->cache[$key];
        $row = $this->findBy('key', $key);
        $val = $row ? $row['value'] : $default;
        $this->cache[$key] = $val;
        return $val;
    }

    public function set(string $key, mixed $value): void
    {
        $existing = $this->findBy('key', $key);
        if ($existing) {
            $this->db->query('UPDATE settings SET value=?, updated_at=? WHERE key=?',
                [(string)$value, date('Y-m-d H:i:s'), $key]);
        } else {
            $this->db->insert('settings', [
                'key'   => $key, 'value' => (string)$value,
                'group' => 'general', 'type' => 'text',
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
        $this->cache[$key] = $value;
    }

    public function getGroup(string $group): array
    {
        $rows = $this->db->fetchAll('SELECT * FROM settings WHERE `group`=?', [$group]);
        $result = [];
        foreach ($rows as $r) $result[$r['key']] = $r['value'];
        return $result;
    }

    public function setMany(array $data): void
    {
        foreach ($data as $k => $v) $this->set($k, $v);
    }
}
