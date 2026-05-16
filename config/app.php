<?php
// NetaTrack India — Application Configuration

return [
    'name'        => getenv('APP_NAME')  ?: 'NetaTrack India',
    'url'         => getenv('APP_URL')   ?: 'http://localhost',
    'env'         => getenv('APP_ENV')   ?: 'production',
    'debug'       => filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN),
    'timezone'    => 'Asia/Kolkata',
    'locale'      => 'en_IN',
    'secret_key'  => getenv('APP_SECRET') ?: 'change-me-in-production-32chars-min',
    'jwt_secret'  => getenv('JWT_SECRET') ?: 'change-me-jwt-secret-32chars-min',
    'jwt_expiry'  => (int)(getenv('JWT_EXPIRY_HOURS') ?: 24),

    // File uploads
    'upload_path'    => getenv('UPLOAD_PATH')    ?: __DIR__ . '/../storage/uploads',
    'upload_max_mb'  => (int)(getenv('UPLOAD_MAX_MB') ?: 20),
    'allowed_mimes'  => ['image/jpeg','image/png','image/webp','image/gif','application/pdf','video/mp4'],

    // Rate limiting
    'rate_limit_submissions' => (int)(getenv('RATE_LIMIT_SUB') ?: 5),   // per hour per IP
    'rate_limit_api'         => (int)(getenv('RATE_LIMIT_API') ?: 100),  // per hour per key

    // Cloudflare
    'cloudflare_zone_id'  => getenv('CF_ZONE_ID')  ?: '',
    'cloudflare_api_key'  => getenv('CF_API_KEY')  ?: '',

    // Redis
    'redis_host' => getenv('REDIS_HOST') ?: '127.0.0.1',
    'redis_port' => (int)(getenv('REDIS_PORT') ?: 6379),
    'redis_pass' => getenv('REDIS_PASS') ?: '',
];
