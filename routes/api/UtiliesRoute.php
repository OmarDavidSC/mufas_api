<?php

$map->attach('utilies.', '/utilies', function ($map) {
    $map->post('index', '/', [
        'Controller' => 'App\Controllers\UtiliesController',
        'Action' => 'index'
    ]);
});
