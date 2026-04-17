<?php

$map->attach('clients.', '/client', function ($map) {

    $map->post('index', '', [
        'Controller' => 'App\Controllers\ClientController',
        'Action' => 'index'
    ]);
    $map->get('adm', '/adm', [
        'Controller' => 'App\Controllers\ClientController',
        'Action' => 'adm'
    ]);
    $map->post('store', '/store', [
        'Controller' => 'App\Controllers\ClientController',
        'Action' => 'store'
    ]);
    $map->post('update', '/{id}/update', [
        'Controller' => 'App\Controllers\ClientController',
        'Action' => 'update'
    ]);
    $map->post('remove', '/{id}/remove', [
        'Controller' => 'App\Controllers\ClientController',
        'Action' => 'remove'
    ]);
});
