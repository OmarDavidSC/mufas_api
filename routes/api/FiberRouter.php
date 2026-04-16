<?php

$map->attach('fibers.', '/fiber', function ($map) {

    $map->post('index', '', [
        'Controller' => 'App\Controllers\FiberController',
        'Action' => 'index'
    ]);
    $map->get('adm', '/adm', [
        'Controller' => 'App\Controllers\FiberController',
        'Action' => 'adm'
    ]);
    $map->post('store', '/store', [
        'Controller' => 'App\Controllers\FiberController',
        'Action' => 'store'
    ]);
    $map->post('update', '/{id}/update', [
        'Controller' => 'App\Controllers\FiberController',
        'Action' => 'update'
    ]);
    $map->post('remove', '/{id}/remove', [
        'Controller' => 'App\Controllers\FiberController',
        'Action' => 'remove'
    ]);
});
