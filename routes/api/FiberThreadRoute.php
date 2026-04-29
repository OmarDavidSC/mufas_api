<?php

$map->attach('fibersthreads.', '/fthread', function ($map) {

    $map->post('index', '', [
        'Controller' => 'App\Controllers\FiberThreadController',
        'Action' => 'index'
    ]);
    $map->get('bytube', '/{tube_id}/bytube', [
        'Controller' => 'App\Controllers\FiberThreadController',
        'Action' => 'bytube'
    ]);
    $map->get('show', '/{id}/show', [
        'Controller' => 'App\Controllers\FiberThreadController',
        'Action' => 'show'
    ]);
    $map->post('store', '/store', [
        'Controller' => 'App\Controllers\FiberThreadController',
        'Action' => 'store'
    ]);
    $map->post('update', '/{id}/update', [
        'Controller' => 'App\Controllers\FiberThreadController',
        'Action' => 'update'
    ]);
    $map->post('status', '/{id}/status', [
        'Controller' => 'App\Controllers\FiberThreadController',
        'Action' => 'status'
    ]);
    $map->get('trace', '/{id}/trace', [
        'Controller' => 'App\Controllers\FiberThreadController',
        'Action' => 'trace'
    ]);
});
