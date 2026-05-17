<?php
/**
 * NetaTrack India - App Configuration
 */

return [
    'name'        => getenv('APP_NAME') ?: 'NetaTrack India',
    'version'     => '1.0.0',
    'env'         => getenv('APP_ENV') ?: 'production',
    'debug'       => (bool)(getenv('APP_DEBUG') ?: false),
    'url'         => getenv('APP_URL') ?: 'http://localhost',
    'timezone'    => 'Asia/Kolkata',
    'locale'      => 'en',
    'key'         => getenv('APP_KEY') ?: '',
    'cipher'      => 'AES-256-CBC',

    'providers' => [
        'session_driver'  => getenv('SESSION_DRIVER') ?: 'file',
        'session_lifetime'=> (int)(getenv('SESSION_LIFETIME') ?: 120),
        'cache_driver'    => getenv('CACHE_DRIVER') ?: 'file',
    ],

    'ai' => [
        'openai_key'     => getenv('OPENAI_API_KEY') ?: '',
        'gemini_key'     => getenv('GEMINI_API_KEY') ?: '',
        'openrouter_key' => getenv('OPENROUTER_API_KEY') ?: '',
        'sarvam_key'     => getenv('SARVAM_API_KEY') ?: '',
        'confidence_threshold' => 0.75,
    ],

    'upload' => [
        'max_size'       => 10 * 1024 * 1024, // 10MB
        'allowed_images' => ['jpg', 'jpeg', 'png', 'webp', 'gif'],
        'allowed_videos' => ['mp4', 'webm', 'mov'],
        'allowed_docs'   => ['pdf', 'doc', 'docx'],
        'path'           => __DIR__ . '/../storage/uploads/',
    ],

    'rate_limit' => [
        'api_per_minute'    => 60,
        'submit_per_hour'   => 5,
        'login_attempts'    => 5,
        'login_decay_minutes' => 15,
    ],
];
