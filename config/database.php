<?php
// NetaTrack India — Database Configuration
// Copy this file to config/database.local.php and fill in real values.
// Never commit real credentials to version control.

return [
    'host'    => getenv('DB_HOST')    ?: '127.0.0.1',
    'port'    => getenv('DB_PORT')    ?: '3306',
    'name'    => getenv('DB_NAME')    ?: 'netatrack',
    'user'    => getenv('DB_USER')    ?: 'root',
    'pass'    => getenv('DB_PASS')    ?: '',
    'charset' => 'utf8mb4',
];
