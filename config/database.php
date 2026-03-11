<?php

return [
    // Automatically select the driver from the .env file, defaulting to mysql
    'default' => $_ENV['DB_CONNECTION'] ?? 'mysql',

    'connections' => [
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => $_ENV['DB_DATABASE'] ?? __DIR__ . '/../database/database.sqlite',
        ],
        'mysql' => [
            'driver' => 'mysql',
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'port' => $_ENV['DB_PORT'] ?? '',
            'database' => $_ENV['DB_DATABASE'] ?? 'mouto2',
            'username' => $_ENV['DB_USERNAME'] ?? '',
            'password' => $_ENV['DB_PASSWORD'] ?? '',
            'charset' => 'utf8mb4',
        ],
        'pgsql' => [
            'driver' => 'pgsql',
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'port' => $_ENV['DB_PORT'] ?? '',
            'database' => $_ENV['DB_DATABASE'] ?? '',
            'username' => $_ENV['DB_USERNAME'] ?? '',
            'password' => $_ENV['DB_PASSWORD'] ?? '',
            'sslmode' => 'prefer',
        ],
    ],

    // Keep your existing tables array here...
    'tables' => [
        'posts' => 'posts',
        'users' => 'users',
        // ...
    ]
];
