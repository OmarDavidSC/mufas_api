<?php

$map->attach('fibersplices.', '/fsplice', function ($map) {

    $map->post('index', '', [
        'Controller' => 'App\Controllers\FiberSpliceController',
        'Action' => 'index'
    ]);
    $map->post('store', '/store', [
        'Controller' => 'App\Controllers\FiberSpliceController',
        'Action' => 'store'
    ]);
    $map->get('show', '/{id}/show', [
        'Controller' => 'App\Controllers\FiberSpliceController',
        'Action' => 'show'
    ]);
    $map->post('update', '/{id}/update', [
        'Controller' => 'App\Controllers\FiberSpliceController',
        'Action' => 'update'
    ]);
    $map->post('remove', '/{id}/remove', [
        'Controller' => 'App\Controllers\FiberSpliceController',
        'Action' => 'remove'
    ]);
    $map->get('bythread', '/{thread_id}/bythread', [
        'Controller' => 'App\Controllers\FiberSpliceController',
        'Action' => 'bythread'
    ]);
});
