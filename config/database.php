<?php
/**
 * NetaTrack India - Database Configuration
 */
return [
    'host'     => getenv('DB_HOST')     ?: '127.0.0.1',
    'port'     => getenv('DB_PORT')     ?: '3306',
    'name'     => getenv('DB_NAME')     ?: 'netatrack_india',
    'user'     => getenv('DB_USER')     ?: 'root',
    'pass'     => getenv('DB_PASS')     ?: '',
    'charset'  => 'utf8mb4',
    'options'  => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ],
    // Redis for caching
    'redis' => [
        'host'     => getenv('REDIS_HOST') ?: '127.0.0.1',
        'port'     => (int)(getenv('REDIS_PORT') ?: 6379),
        'password' => getenv('REDIS_PASSWORD') ?: null,
        'database' => (int)(getenv('REDIS_DB') ?: 0),
    ],
];
