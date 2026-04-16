<?php

$map->attach('nodes.', '/node', function ($map) {

    $map->post('index', '', [
        'Controller' => 'App\Controllers\NodeController',
        'Action' => 'index'
    ]);
    $map->get('adm', '/adm', [
        'Controller' => 'App\Controllers\NodeController',
        'Action' => 'adm'
    ]);
    $map->post('store', '/store', [
        'Controller' => 'App\Controllers\NodeController',
        'Action' => 'store'
    ]);
    $map->post('update', '/{id}/update', [
        'Controller' => 'App\Controllers\NodeController',
        'Action' => 'update'
    ]);
    $map->post('remove', '/{id}/remove', [
        'Controller' => 'App\Controllers\NodeController',
        'Action' => 'remove'
    ]);
});
