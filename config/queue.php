<?php

return [
    'default' => env('QUEUE_CONNECTION', 'sync'),

    'connections' => [
        'sync' => [
            'driver' => 'sync',
        ],

        'database' => [
            'driver'      => 'database',
            'connection'  => null,
            'table'       => 'jobs',
            'queue'       => 'default',
            'retry_after' => 90,
            'after_commit'=> false,
        ],

        'redis' => [
            'driver'      => 'redis',
            'connection'  => 'default',
            'queue'       => env('REDIS_QUEUE', 'default'),
            'retry_after' => 90,
            'block_for'   => null,
            'after_commit'=> false,
        ],
    ],

    'failed' => [
        'driver'   => env('QUEUE_FAILED_DRIVER', 'database'),
        'database' => env('DB_CONNECTION'),  // Use actual configured connection (pgsql, mysql, etc.)
        'table'    => 'failed_jobs',
    ],
];
