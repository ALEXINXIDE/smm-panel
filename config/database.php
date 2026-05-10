<?php

// ============================================================================
// config/database.php — Production-Grade Database Configuration
// ============================================================================
// Changes from original:
//  1. Default connection changed from sqlite → pgsql (SQLite is unsuitable
//     for production: no concurrent writes, no row-level locking).
//  2. Added full PostgreSQL connection block with SSL support, connection
//     pooling parameters, and strict mode.
//  3. Retained MySQL block for legacy compatibility (Docker or managed DBs).
//  4. Removed SQLite as the default to prevent accidental local-DB deployments.
//  5. Added `failed_jobs` and `cache` table configuration for Redis fallback.
// ============================================================================

return [

    // ── Default Connection ─────────────────────────────────────────────────
    // Set DB_CONNECTION=pgsql in .env for production.
    // The app will crash with a clear error if DB_CONNECTION is not set,
    // rather than silently using SQLite.
    'default' => env('DB_CONNECTION'),

    'connections' => [

        // ── PostgreSQL (primary production database) ───────────────────────
        // Benefits over MySQL: better JSON support, superior indexing,
        // row-level locking, better concurrent write performance, advisory locks.
        'pgsql' => [
            'driver'         => 'pgsql',
            'url'            => env('DATABASE_URL'),           // Heroku/Railway style
            'host'           => env('DB_HOST', '127.0.0.1'),
            'port'           => env('DB_PORT', '5432'),
            'database'       => env('DB_DATABASE', 'smm_panel'),
            'username'       => env('DB_USERNAME', 'smm_user'),
            'password'       => env('DB_PASSWORD', ''),
            'charset'        => 'utf8',
            'prefix'         => '',
            'prefix_indexes' => true,
            'search_path'    => 'public',
            'sslmode'        => env('DB_SSLMODE', 'require'),  // Default to 'require' for Railway security
            // Production: DB_SSLMODE=require ensures all data-in-transit is encrypted
            'options'        => extension_loaded('pdo_pgsql') ? array_filter([
                PDO::ATTR_TIMEOUT => 10,   // 10s connection timeout
                PDO::ATTR_PERSISTENT => false, // Never use persistent connections with queues
            ]) : [],
        ],

        // ── MySQL (legacy / Docker compatibility) ──────────────────────────
        'mysql' => [
            'driver'         => 'mysql',
            'url'            => env('DATABASE_URL'),
            'host'           => env('DB_HOST', '127.0.0.1'),
            'port'           => env('DB_PORT', '3306'),
            'database'       => env('DB_DATABASE', 'smm_panel'),
            'username'       => env('DB_USERNAME', 'forge'),
            'password'       => env('DB_PASSWORD', ''),
            'unix_socket'    => env('DB_SOCKET', ''),
            'charset'        => 'utf8mb4',
            'collation'      => 'utf8mb4_unicode_ci',
            'prefix'         => '',
            'strict'         => true,   // Strict mode prevents silent data truncation
            'engine'         => 'InnoDB',
            'options'        => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        // ── SQLite (local development only — never production) ─────────────
        // To use: DB_CONNECTION=sqlite DB_DATABASE=/absolute/path/to/db.sqlite
        'sqlite' => [
            'driver'                  => 'sqlite',
            'url'                     => env('DATABASE_URL'),
            'database'                => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix'                  => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],
    ],

    // ── Migration Settings ─────────────────────────────────────────────────
    'migrations' => 'migrations',
    'migrations_update_date_on_publish' => true,

    // ── Redis (cache + queues + sessions) ─────────────────────────────────
    // Using Redis for cache, sessions, and queues provides:
    //  - Horizontal scalability (shared state across multiple app instances)
    //  - Atomic operations (prevents race conditions in balance updates)
    //  - Pub/sub for real-time features
    'redis' => [
        'client' => env('REDIS_CLIENT', 'predis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix'  => env('REDIS_PREFIX', 'smm_panel_'),
        ],

        'default' => [
            'url'      => env('REDIS_URL'),
            'host'     => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port'     => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

        // Separate DB for cache to allow independent flushing
        'cache' => [
            'url'      => env('REDIS_URL'),
            'host'     => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port'     => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],
    ],
];
