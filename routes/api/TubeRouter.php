<?php

$map->attach('tubes.', '/tube', function ($map) {

    $map->post('index', '', [
        'Controller' => 'App\Controllers\TubeController',
        'Action' => 'index'
    ]);
    $map->get('adm', '/adm', [
        'Controller' => 'App\Controllers\TubeController',
        'Action' => 'adm'
    ]);
    $map->post('store', '/store', [
        'Controller' => 'App\Controllers\TubeController',
        'Action' => 'store'
    ]);
    $map->post('update', '/{id}/update', [
        'Controller' => 'App\Controllers\TubeController',
        'Action' => 'update'
    ]);
    $map->post('remove', '/{id}/remove', [
        'Controller' => 'App\Controllers\TubeController',
        'Action' => 'remove'
    ]);
});
