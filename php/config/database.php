<?php
/**
 * NetaTrack India - Database Configuration
 */

return [
    'default' => getenv('DB_CONNECTION') ?: 'mysql',
    'connections' => [
        'mysql' => [
            'driver'    => 'mysql',
            'host'      => getenv('DB_HOST') ?: '127.0.0.1',
            'port'      => getenv('DB_PORT') ?: '3306',
            'database'  => getenv('DB_DATABASE') ?: 'netatrack',
            'username'  => getenv('DB_USERNAME') ?: 'root',
            'password'  => getenv('DB_PASSWORD') ?: '',
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'options'   => [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'",
            ],
        ],
    ],
];
