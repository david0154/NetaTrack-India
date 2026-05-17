<?php
/**
 * NetaTrack India - Mail / SMTP Configuration
 */
return [
    'driver'     => getenv('MAIL_DRIVER')     ?: 'smtp',
    'host'       => getenv('MAIL_HOST')       ?: 'smtp.gmail.com',
    'port'       => (int)(getenv('MAIL_PORT') ?: 587),
    'encryption' => getenv('MAIL_ENCRYPTION') ?: 'tls',
    'username'   => getenv('MAIL_USERNAME')   ?: '',
    'password'   => getenv('MAIL_PASSWORD')   ?: '',
    'from_address'=> getenv('MAIL_FROM_ADDRESS') ?: 'noreply@netatrack.in',
    'from_name'   => getenv('MAIL_FROM_NAME')    ?: 'NetaTrack India',
];
