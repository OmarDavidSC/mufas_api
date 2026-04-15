<?php

    $map->get('index', '/',[
        'Controller' => 'App\Controllers\HomeController',
        'Action' => 'index'
    ]);

    if (\App\Middlewares\Authenticate::isDeveloper()) {
        $map->attach('dev.', '/dev', function ($map) {
            include __DIR__ . "/api.php";
        });
    }

    include __DIR__ . "/api.php";  

?>