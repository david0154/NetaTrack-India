<?php
return [
    'name'        => 'NetaTrack India',
    'version'     => '1.0.0',
    'timezone'    => 'Asia/Kolkata',
    'locale'      => 'en',
    'debug'       => filter_var(getenv('APP_DEBUG') ?: false, FILTER_VALIDATE_BOOLEAN),
    'url'         => getenv('APP_URL') ?: 'http://localhost',
    'secret'      => getenv('APP_SECRET') ?: 'change_this_secret_key_32chars!!',
    'upload_path' => ROOT_PATH . '/uploads',
    'cache_path'  => ROOT_PATH . '/cache',
    'log_path'    => ROOT_PATH . '/logs',
    'per_page'    => 20,
    'ai' => [
        'openai_key'    => getenv('OPENAI_API_KEY'),
        'gemini_key'    => getenv('GEMINI_API_KEY'),
        'openrouter_key'=> getenv('OPENROUTER_API_KEY'),
        'sarvam_key'    => getenv('SARVAM_API_KEY'),
    ],
    'smtp' => [
        'host'     => getenv('SMTP_HOST') ?: 'smtp.gmail.com',
        'port'     => getenv('SMTP_PORT') ?: 587,
        'username' => getenv('SMTP_USERNAME'),
        'password' => getenv('SMTP_PASSWORD'),
        'from'     => getenv('SMTP_FROM') ?: 'noreply@netatrack.in',
        'from_name'=> 'NetaTrack India',
    ],
    'redis' => [
        'host' => getenv('REDIS_HOST') ?: '127.0.0.1',
        'port' => getenv('REDIS_PORT') ?: 6379,
        'pass' => getenv('REDIS_PASSWORD') ?: null,
    ],
];
