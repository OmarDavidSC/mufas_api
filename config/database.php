<?php
    
    use Illuminate\Database\Capsule\Manager as Capsule;
    $capsule = new Capsule;

    $capsule->addConnection([
        'driver'    => 'mysql',
        'host'      => $_ENV['DB_HOST'],
        'port'      => $_ENV['DB_PORT'],
        'database'  => $_ENV['DB_DATABASE'], 
        'username'  => $_ENV['DB_USERNAME'],
        'password'  => $_ENV['DB_PASS'],
        'charset'   => 'utf8mb4',
        'collation' => 'utf8mb4_0900_ai_ci',
        'prefix'    => ''
    ]);

    //MONGO DB
    $capsule->addConnection([
        'name' => 'mongodb',
        'driver'   => 'mongodb',
        'dsn' => $_ENV['DB_DSN_MONGO'],
        'database' => $_ENV['DB_NAME_MONGO']
    ],"mongodb");

    // Extensión necesaria para MongoDB
    $capsule->getDatabaseManager()->extend('mongodb', function($config) {
        return new Jenssegers\Mongodb\Connection($config);
    });

    // Make this Capsule instance available globally via static methods... (optional)
    $capsule->setAsGlobal();

    // Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
    $capsule->bootEloquent();
?>