<?php
/**
 * NetaTrack India - Auth Configuration
 */
return [
    'session_name'     => 'netatrack_session',
    'session_lifetime' => 7200, // 2 hours
    'remember_days'    => 30,
    'admin_guard'      => 'admin',
    'public_guard'     => 'web',
    'password_min'     => 8,
    'max_login_attempts' => 5,
    'lockout_minutes'  => 15,
    'roles' => [
        'super_admin' => 100,
        'admin'       => 80,
        'moderator'   => 60,
        'editor'      => 40,
        'viewer'      => 20,
        'public'      => 10,
    ],
];
