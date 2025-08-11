<?php

return [

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'pelanggan'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'pelanggan'),
    ],

    'guards' => [
        'pelanggan' => [
            'driver' => 'session',
            'provider' => 'pelanggan',
        ],
        'admin' => [
            'driver' => 'session',
            'provider' => 'admin',
        ],
    ],

    'providers' => [
        'pelanggan' => [
            'driver' => 'eloquent',
            'model' => App\Models\Pelanggan::class,
        ],
        'admin' => [
            'driver' => 'eloquent',
            'model' => App\Models\Admin::class,
        ],
    ],

    'passwords' => [
        'pelanggan' => [
            'provider' => 'pelanggan',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
        'admin' => [
            'provider' => 'admin',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
