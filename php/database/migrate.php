<?php
/**
 * NetaTrack India - Database Migration Runner
 * Usage: php migrate.php
 */

require_once __DIR__ . '/../bootstrap.php';

$db = NetaTrack\Core\Database::getInstance();
$migrationDir = __DIR__ . '/migrations';
$files = glob($migrationDir . '/*.sql');
sort($files);

echo "\n=== NetaTrack India - Database Migrations ===\n";

foreach ($files as $file) {
    $name = basename($file);
    echo "Running: {$name} ... ";
    try {
        $sql = file_get_contents($file);
        // Split by semicolons and execute each statement
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            fn($s) => !empty($s) && !str_starts_with(ltrim($s), '--')
        );
        foreach ($statements as $stmt) {
            if (trim($stmt)) $db->pdo()->exec($stmt);
        }
        echo "OK\n";
    } catch (\Exception $e) {
        echo "FAILED: " . $e->getMessage() . "\n";
    }
}

echo "\n=== Migrations Complete ===\n\n";
