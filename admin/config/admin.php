<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin Application Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration specific to the Altobul Admin panel.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Backend API Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL of the Altobul backend API (without /api/admin).
    | Example: https://api.altobul.com
    |
    */

    'api_base_url' => env('ADMIN_API_BASE_URL', 'http://localhost:8000'),

    /*
    |--------------------------------------------------------------------------
    | Admin API Key
    |--------------------------------------------------------------------------
    |
    | The ADMIN type API Key used to identify this admin application
    | when making requests to the backend API.
    | Generated via: php artisan api-keys:create --type=ADMIN --name="Admin Panel"
    |
    */

    'api_key' => env('ADMIN_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | Timeout in seconds for API requests to the backend.
    |
    */

    'timeout' => env('ADMIN_API_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Dashboard Configuration
    |--------------------------------------------------------------------------
    */

    'dashboard' => [
        'charts' => [
            'days' => 30,
            'colors' => [
                'primary' => '#3b82f6',
                'success' => '#22c55e',
                'warning' => '#f59e0b',
                'danger' => '#ef4444',
                'info' => '#06b6d4',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Geo-Zones Map Configuration
    |--------------------------------------------------------------------------
    */

    'geo_zones' => [
        'default_center' => [-34.6037, -58.3816], // Buenos Aires
        'default_zoom' => 10,
        'tile_layer' => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        'attribution' => '&copy; OpenStreetMap contributors',
    ],

    /*
    |--------------------------------------------------------------------------
    | Profile Fields Configuration
    |--------------------------------------------------------------------------
    */

    'profile_fields' => [
        'types' => [
            'text' => 'Texto corto',
            'textarea' => 'Texto largo',
            'number' => 'Número',
            'select' => 'Selección única',
            'multiselect' => 'Selección múltiple',
            'radio' => 'Radio buttons',
            'checkbox' => 'Checkbox',
            'date' => 'Fecha',
            'boolean' => 'Sí/No',
        ],
        'visibilities' => [
            'PUBLIC' => 'Público',
            'MATCH' => 'Solo Match',
            'FRIENDS' => 'Solo Amigos',
            'PRIVATE' => 'Privado (solo con permiso)',
        ],
    ],

];
