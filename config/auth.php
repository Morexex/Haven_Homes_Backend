<?php

return [

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'admin_user'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'admin_users'),
    ],

    'guards' => [

        'property_user' => [
            'driver' => 'session',
            'provider' => 'property_users',
        ],

        'admin_user' => [
            'driver' => 'session',
            'provider' => 'admin_users',
        ],

        'api' => [ // ✅ Add API guard for Sanctum
            'driver' => 'sanctum',
            'provider' => null, // Allow multiple user models to authenticate
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
