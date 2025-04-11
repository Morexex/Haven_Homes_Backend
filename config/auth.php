<?php

return [

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'admin_user'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'admin_users'),
    ],

    'guards' => [

        'admin_user' => [
            'driver' => 'passport',
            'provider' => 'admin_users',
        ],

        'property_user' => [
            'driver' => 'passport',
            'provider' => 'property_users',
        ],
    ],

    'providers' => [

        'admin_users' => [
            'driver' => 'eloquent',
            'model' => App\Models\AdminUser::class,
        ],

        'property_users' => [
            'driver' => 'eloquent',
            'model' => App\Models\PropertyUser::class,
        ],
    ],

    'passwords' => [
        'admin_users' => [
            'provider' => 'admin_users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
        'property_users' => [
            'provider' => 'property_users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
