#!/usr/bin/env php
<?php
/**
 * NetaTrack India — CLI Migration Runner
 * Usage:
 *   php database/migrate.php            # Run all pending migrations
 *   php database/migrate.php --fresh    # Drop all tables and re-run
 *   php database/migrate.php --seed     # Run seeders after migration
 */

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/bootstrap.php';

$args  = array_slice($argv ?? [], 1);
$fresh = in_array('--fresh', $args);
$seed  = in_array('--seed', $args);

$db  = \NetaTrack\Core\Database::getInstance();
$pdo = $db->getConnection();

// ---- Track executed migrations ----
$pdo->exec(
    "CREATE TABLE IF NOT EXISTS _migrations (
        id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        filename   VARCHAR(255) NOT NULL UNIQUE,
        ran_at     DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

if ($fresh) {
    echo "\e[33m[!] --fresh flag detected. Dropping all tables...\e[0m\n";
    $tables = $pdo->query(
        "SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name != '_migrations'"
    )->fetchAll(PDO::FETCH_COLUMN);
    $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
    foreach ($tables as $t) { $pdo->exec("DROP TABLE IF EXISTS `$t`"); echo "  Dropped: $t\n"; }
    $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
    $pdo->exec("DELETE FROM _migrations");
    echo "\e[32m[✓] All tables dropped.\e[0m\n\n";
}

// ---- Run pending migration files ----
$migDir  = __DIR__ . '/migrations';
$files   = glob($migDir . '/*.sql');
sort($files);

$ran = $pdo->query("SELECT filename FROM _migrations")->fetchAll(PDO::FETCH_COLUMN);
$pending = array_filter($files, fn($f) => !in_array(basename($f), $ran));

if (empty($pending)) {
    echo "\e[32m[✓] No pending migrations. Database is up to date.\e[0m\n";
} else {
    foreach ($pending as $file) {
        $name = basename($file);
        echo "  Running: $name ... ";
        try {
            $sql = file_get_contents($file);
            // Split on semicolon (outside strings) for multi-statement files
            $statements = array_filter(
                array_map('trim', preg_split('/;\s*$/m', $sql)),
                fn($s) => $s !== '' && !preg_match('/^--/', ltrim($s))
            );
            foreach ($statements as $stmt) {
                if (trim($stmt)) $pdo->exec($stmt);
            }
            $pdo->prepare("INSERT INTO _migrations (filename) VALUES (?)")->execute([$name]);
            echo "\e[32mDone\e[0m\n";
        } catch (\PDOException $e) {
            echo "\e[31mFAILED: " . $e->getMessage() . "\e[0m\n";
            exit(1);
        }
    }
    echo "\n\e[32m[✓] All migrations complete.\e[0m\n";
}

// ---- Seeder ----
if ($seed) {
    $seederFile = __DIR__ . '/seeders/DemoSeeder.php';
    if (file_exists($seederFile)) {
        echo "\n\e[34m[*] Running seeders...\e[0m\n";
        require $seederFile;
        echo "\e[32m[✓] Seeding complete.\e[0m\n";
    } else {
        echo "\e[33m[!] No seeder found at database/seeders/DemoSeeder.php\e[0m\n";
    }
}
