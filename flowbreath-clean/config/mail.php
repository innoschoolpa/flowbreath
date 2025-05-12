<?php

return [
    'smtp' => [
        'host' => getenv('MAIL_HOST'),
        'port' => getenv('MAIL_PORT'),
        'username' => getenv('MAIL_USERNAME'),
        'password' => getenv('MAIL_PASSWORD'),
    ],
    'from' => [
        'address' => getenv('MAIL_FROM_ADDRESS'),
        'name' => getenv('MAIL_FROM_NAME'),
    ],
    'app_url' => getenv('APP_URL'),
]; 