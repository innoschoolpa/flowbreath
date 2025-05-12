<?php
return [
    'google' => [
        'client_id' => getenv('GOOGLE_CLIENT_ID'),
        'client_secret' => getenv('GOOGLE_CLIENT_SECRET'),
        'redirect_uri' => 'https://flowbreath.io/auth/google/callback',
        'scopes' => [
            'email',
            'profile',
            'openid'
        ]
    ]
]; 