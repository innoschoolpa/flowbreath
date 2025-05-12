<?php

return [
    'google' => [
        'client_id' => getenv('GOOGLE_CLIENT_ID'),
        'client_secret' => getenv('GOOGLE_CLIENT_SECRET'),
        'redirect_uri' => getenv('GOOGLE_REDIRECT_URI') ?: 'https://flowbreath.io/auth/google/callback',
        'scopes' => [
            'https://www.googleapis.com/auth/userinfo.email',
            'https://www.googleapis.com/auth/userinfo.profile',
            'openid'
        ],
        'access_type' => 'offline',
        'include_granted_scopes' => true,
        'state_expiry' => 300, // 5 minutes
        'max_login_attempts' => 5,
        'lockout_duration' => 1800, // 30 minutes
        'session_lifetime' => 3600, // 1 hour
        'session_secure' => true,
        'session_httponly' => true,
        'session_samesite' => 'None',
        'session_path' => '/',
        'session_domain' => '.flowbreath.io'
    ]
]; 