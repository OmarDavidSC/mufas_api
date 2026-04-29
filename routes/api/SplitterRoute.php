<?php

$map->attach('splitters.', '/splitter', function ($map) {

    $map->post('index', '', [
        'Controller' => 'App\Controllers\SplitterController',
        'Action' => 'index'
    ]);
    $map->get('show', '/{id}/show', [
        'Controller' => 'App\Controllers\SplitterController',
        'Action' => 'show'
    ]);
    $map->post('store', '/store', [
        'Controller' => 'App\Controllers\SplitterController',
        'Action' => 'store'
    ]);
    $map->post('update', '/{id}/update', [
        'Controller' => 'App\Controllers\SplitterController',
        'Action' => 'update'
    ]);
    $map->post('remove', '/{id}/remove', [
        'Controller' => 'App\Controllers\SplitterController',
        'Action' => 'remove'
    ]);
    $map->get('ports', '/{id}/ports', [
        'Controller' => 'App\Controllers\SplitterController',
        'Action' => 'ports'
    ]);
});
