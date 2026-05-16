<?php
/**
 * NetaTrack India - Application Configuration
 */
return [
    'name'        => getenv('APP_NAME')  ?: 'NetaTrack India',
    'env'         => getenv('APP_ENV')   ?: 'production',
    'url'         => getenv('APP_URL')   ?: 'http://localhost',
    'jwt_secret'  => getenv('JWT_SECRET') ?: '',
    'jwt_expiry'  => (int)(getenv('JWT_EXPIRY_HOURS') ?: 24),
    'timezone'    => 'Asia/Kolkata',
    'locale'      => 'en_IN',
    'upload_path' => getenv('UPLOAD_PATH') ?: BASE_PATH . '/public/uploads',
    'upload_url'  => (getenv('APP_URL') ?: '') . '/uploads',
    'max_upload_mb'=> (int)(getenv('MAX_UPLOAD_MB') ?: 50),
    'cloudflare'  => [
        'zone_id'   => getenv('CF_ZONE_ID') ?: '',
        'api_token' => getenv('CF_API_TOKEN') ?: '',
    ],
    'rate_limit'  => [
        'api_per_minute'   => (int)(getenv('RATE_LIMIT_API') ?: 60),
        'submit_per_hour'  => (int)(getenv('RATE_LIMIT_SUBMIT') ?: 5),
        'login_per_minute' => (int)(getenv('RATE_LIMIT_LOGIN') ?: 10),
    ],
];
