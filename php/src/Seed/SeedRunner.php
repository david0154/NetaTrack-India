<?php
/**
 * NetaTrack India — PHP Seed Runner
 * Runs seed_states_leaders.sql once on fresh install.
 *
 * Auto-called by installer.
 * Manual: php artisan seed:states
 * Or visit: /admin/settings/run-seed  (admin only)
 */

namespace App\Seed;

class SeedRunner
{
    private \PDO $pdo;
    private string $sqlFile;

    public function __construct(\PDO $pdo = null)
    {
        $this->pdo     = $pdo ?? db()->getPdo();
        $this->sqlFile = BASE_PATH . '/database/seed_states_leaders.sql';
    }

    public function run(): array
    {
        if (!file_exists($this->sqlFile)) {
            return ['ok' => false, 'error' => 'Seed file not found: ' . $this->sqlFile];
        }

        $sql   = file_get_contents($this->sqlFile);
        $stmts = array_filter(
            array_map('trim', explode(';', $sql)),
            fn($s) => $s !== '' && !str_starts_with($s, '--')
        );

        $ok = 0; $skipped = 0; $errors = [];
        foreach ($stmts as $stmt) {
            try {
                $this->pdo->exec($stmt);
                $ok++;
            } catch (\PDOException $e) {
                if (str_contains($e->getMessage(), '1062')) {
                    $skipped++; // Duplicate — already seeded
                } else {
                    $errors[] = substr($e->getMessage(), 0, 120);
                }
            }
        }

        return [
            'ok'      => true,
            'ran'     => $ok,
            'skipped' => $skipped,
            'errors'  => $errors,
            'states'  => $this->count('states'),
            'leaders' => $this->count('leaders'),
            'parties' => $this->count('parties'),
        ];
    }

    private function count(string $table): int
    {
        return (int) $this->pdo->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
    }

    /**
     * Get all states for dropdown (used in leader add/edit forms).
     */
    public static function getStates(): array
    {
        return db()->query(
            "SELECT id, name, code, type FROM states ORDER BY type ASC, name ASC"
        )->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get all parties for dropdown.
     */
    public static function getParties(): array
    {
        return db()->query(
            "SELECT id, name, abbreviation, color FROM parties ORDER BY name ASC"
        )->fetchAll(\PDO::FETCH_ASSOC);
    }
}
