<?php
/**
 * NetaTrack India - App Configuration
 */
return [
    'name'    => getenv('APP_NAME') ?: 'NetaTrack India',
    'url'     => getenv('APP_URL')  ?: 'http://localhost',
    'debug'   => (bool)(getenv('APP_DEBUG') ?: false),
    'timezone'=> 'Asia/Kolkata',
    'locale'  => 'en',
    'key'     => getenv('APP_KEY')  ?: 'netatrack_secret_key_change_me',
    'version' => '1.0.0',
    'phase'   => 1,
    'admin_prefix' => 'admin',
    'per_page' => 20,
    'upload_max_size' => 10 * 1024 * 1024, // 10MB
    'allowed_image_types' => ['image/jpeg','image/png','image/webp','image/gif'],
    'allowed_doc_types'   => ['application/pdf'],
    'pagination_links'    => 5,
];
