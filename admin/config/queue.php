<?php

return [

    'default' => env('QUEUE_CONNECTION', 'database'),

    'connections' => [

        'sync' => [
            'driver' => 'sync',
        ],

        'database' => [
            'driver' => 'database',
            'table' => env('QUEUE_DATABASE_TABLE', 'jobs'),
            'queue' => env('QUEUE_DATABASE_QUEUE', 'default'),
            'retry_after' => 90,
            'after_commit' => false,
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => env('REDIS_QUEUE', 'default'),
            'retry_after' => 90,
            'block_for' => null,
            'after_commit' => false,
        ],

        'sqs' => [
            'driver' => 'sqs',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'prefix' => env('SQS_PREFIX', 'https://sqs.us-east-1.amazonaws.com/your-account-id'),
            'queue' => env('SQS_QUEUE', 'default'),
            'suffix' => env('SQS_SUFFIX'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
        ],

    ],

    'batching' => [
        'database' => env('QUEUE_BATCHING_DATABASE_CONNECTION', 'database'),
        'table' => env('QUEUE_BATCHING_TABLE', 'job_batches'),
    ],

    'failed' => [
        'driver' => env('QUEUE_FAILED_DRIVER', 'database'),
        'database' => env('QUEUE_FAILED_DATABASE_CONNECTION', 'database'),
        'table' => env('QUEUE_FAILED_TABLE', 'failed_jobs'),
    ],

];
