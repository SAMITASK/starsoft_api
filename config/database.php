<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for database operations. This is
    | the connection which will be utilized unless another connection
    | is explicitly specified when you execute a query / statement.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Below are all of the database connections defined for your application.
    | An example configuration is provided for each database system which
    | is supported by Laravel. You're free to add / remove connections.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DB_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
            'busy_timeout' => null,
            'journal_mode' => null,
            'synchronous' => null,
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'mariadb' => [
            'driver' => 'mariadb',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            // 'encrypt' => env('DB_ENCRYPT', 'yes'),
            // 'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
        ],
        'sqlsrv_001' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_HOST', '192.168.100.2'),
            'port' => env('DB_PORT', '1433'),
            'database' => '001BDCOMUN',
            'username' => env('DB_USERNAME', 'lector_api'),
            'password' => env('DB_PASSWORD', 'tu_password'),
            'charset' => 'utf8',
            'prefix' => '',
        ],

        'sqlsrv_002' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_HOST', '192.168.100.2'),
            'port' => env('DB_PORT', '1433'),
            'database' => '002BDCOMUN',
            'username' => env('DB_USERNAME', 'lector_api'),
            'password' => env('DB_PASSWORD', 'tu_password'),
            'charset' => 'utf8',
            'prefix' => '',
        ],

        'sqlsrv_003' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_HOST', '192.168.100.2'),
            'port' => env('DB_PORT', '1433'),
            'database' => '003BDCOMUN',
            'username' => env('DB_USERNAME', 'lector_api'),
            'password' => env('DB_PASSWORD', 'tu_password'),
            'charset' => 'utf8',
            'prefix' => '',
        ],
        'sqlsrv_004' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_HOST', '192.168.100.2'),
            'port' => env('DB_PORT', '1433'),
            'database' => '004BDCOMUN',
            'username' => env('DB_USERNAME', 'lector_api'),
            'password' => env('DB_PASSWORD', 'tu_password'),
            'charset' => 'utf8',
            'prefix' => '',
        ],

        'sqlsrv_005' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_HOST', '192.168.100.2'),
            'port' => env('DB_PORT', '1433'),
            'database' => '005BDCOMUN',
            'username' => env('DB_USERNAME', 'lector_api'),
            'password' => env('DB_PASSWORD', 'tu_password'),
            'charset' => 'utf8',
            'prefix' => '',
        ],

        'sqlsrv_006' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_HOST', '192.168.100.2'),
            'port' => env('DB_PORT', '1433'),
            'database' => '006BDCOMUN',
            'username' => env('DB_USERNAME', 'lector_api'),
            'password' => env('DB_PASSWORD', 'tu_password'),
            'charset' => 'utf8',
            'prefix' => '',
        ],
        'sqlsrv_007' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_HOST', '192.168.100.2'),
            'port' => env('DB_PORT', '1433'),
            'database' => '007BDCOMUN',
            'username' => env('DB_USERNAME', 'lector_api'),
            'password' => env('DB_PASSWORD', 'tu_password'),
            'charset' => 'utf8',
            'prefix' => '',
        ],

        'sqlsrv_008' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_HOST', '192.168.100.2'),
            'port' => env('DB_PORT', '1433'),
            'database' => '008BDCOMUN',
            'username' => env('DB_USERNAME', 'lector_api'),
            'password' => env('DB_PASSWORD', 'tu_password'),
            'charset' => 'utf8',
            'prefix' => '',
        ],

        'sqlsrv_009' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_HOST', '192.168.100.2'),
            'port' => env('DB_PORT', '1433'),
            'database' => '009BDCOMUN',
            'username' => env('DB_USERNAME', 'lector_api'),
            'password' => env('DB_PASSWORD', 'tu_password'),
            'charset' => 'utf8',
            'prefix' => '',
        ],
        'sqlsrv_010' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_HOST', '192.168.100.2'),
            'port' => env('DB_PORT', '1433'),
            'database' => '010BDCOMUN',
            'username' => env('DB_USERNAME', 'lector_api'),
            'password' => env('DB_PASSWORD', 'tu_password'),
            'charset' => 'utf8',
            'prefix' => '',
        ],
        'sqlsrv_011' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_HOST', '192.168.100.2'),
            'port' => env('DB_PORT', '1433'),
            'database' => '011BDCOMUN',
            'username' => env('DB_USERNAME', 'lector_api'),
            'password' => env('DB_PASSWORD', 'tu_password'),
            'charset' => 'utf8',
            'prefix' => '',
        ],
        'bdwenco' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_HOST', '192.168.100.2'),
            'port' => env('DB_PORT', '1433'),
            'database' => 'BDWENCO',
            'username' => env('DB_USERNAME', 'lector_api'),
            'password' => env('DB_PASSWORD', 'ClavePrueba2024*'),
            'charset' => 'utf8',
            'prefix' => '',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run on the database.
    |
    */

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as Memcached. You may define your connection settings here.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_') . '_database_'),
            'persistent' => env('REDIS_PERSISTENT', false),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],

    ],

];
