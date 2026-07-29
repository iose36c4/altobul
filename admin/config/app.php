<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application.
    |
    */

    'name' => env('APP_NAME', 'Altobul Admin'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when generating URLs.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    'asset_url' => env('ASSET_URL'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => 'America/Argentina/Buenos_Aires',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'es',

    'fallback_locale' => 'en',

    'faker_locale' => 'es_ES',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in maintenance mode, a custom view will be
    | displayed for all requests into your application. This is useful when
    | you are deploying or making significant changes to your application.
    |
    */

    'maintenance' => [
        'driver' => 'file',
        'store' => env('MAINTENANCE_STORE'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Online Threshold Minutes
    |--------------------------------------------------------------------------
    |
    | Minutes to consider a user as online based on last_seen_at.
    |
    */

    'online_threshold_minutes' => env('ONLINE_THRESHOLD_MINUTES', 2),

    /*
    |--------------------------------------------------------------------------
    | Sanitize HTML for Posts
    |--------------------------------------------------------------------------
    |
    | HTML Purifier configuration for sanitizing post content.
    |
    */

    'html_purifier' => [
        'cache_path' => storage_path('app/htmlpurifier'),
        'settings' => [
            'default' => [
                'HTML.Doctype' => 'HTML 4.01 Transitional',
                'HTML.Allowed' => 'p,b,i,strong,em,u,strike,br,ul,ol,li,a[href|title],img[src|alt|width|height],div,span',
                'CSS.AllowedProperties' => 'font,font-size,font-weight,font-style,font-family,text-decoration,color,background-color,text-align,margin,padding',
                'AutoFormat.AutoParagraph' => true,
                'AutoFormat.RemoveEmpty' => true,
            ],
        ],
    ],

];
