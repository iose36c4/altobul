<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Hash Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default hash driver that will be used to hash
    | passwords for your application. By default, the bcrypt algorithm is
    | used; however, you remain free to modify this option if you wish.
    |
    | Supported: "bcrypt", "argon", "argon2id"
    |
    */

    'driver' => env('HASHING_DRIVER', 'argon2id'),

    /*
    |--------------------------------------------------------------------------
    | Bcrypt Options
    |--------------------------------------------------------------------------
    |
    | Here you may specify the configuration options that should be used when
    | passwords are hashed using the Bcrypt algorithm. This will allow you
    | to control the amount of time it takes to hash the given password.
    |
    | iterations: 12 is the Laravel default (OWASP recommended minimum)
    |
    */

    'bcrypt' => [
        'rounds' => (int) env('BCRYPT_ROUNDS', 12),
        'verify' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Argon Options
    |--------------------------------------------------------------------------
    |
    | Here you may specify the configuration options that should be used when
    | passwords are hashed using the Argon algorithms. These will allow you
    | to control the amount of time it takes to hash the given password.
    |
    | memory_cost: 65536 (64MB) - OWASP recommended minimum
    | time_cost: 4 iterations
    | threads: 1 thread (Argon2id doesn't benefit from multiple threads)
    |
    */

    'argon' => [
        'memory_cost' => (int) env('ARGON_MEMORY_COST', 65536),
        'time_cost' => (int) env('ARGON_TIME_COST', 4),
        'threads' => (int) env('ARGON_THREADS', 1),
        'verify' => true,
    ],

];
