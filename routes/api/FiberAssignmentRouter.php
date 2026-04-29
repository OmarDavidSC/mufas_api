<?php

$map->attach('fassignments.', '/assignment', function ($map) {

    $map->post('index', '', [
        'Controller' => 'App\Controllers\FiberAssignmentController',
        'Action' => 'index'
    ]);
    $map->post('store', '/store', [
        'Controller' => 'App\Controllers\FiberAssignmentController',
        'Action' => 'store'
    ]);
    $map->get('show', '/{id}/show', [
        'Controller' => 'App\Controllers\FiberAssignmentController',
        'Action' => 'show'
    ]);
    $map->post('release', '/{id}/release', [
        'Controller' => 'App\Controllers\FiberAssignmentController',
        'Action' => 'release'
    ]);
    $map->get('client', '/{client_id}/client', [
        'Controller' => 'App\Controllers\FiberAssignmentController',
        'Action' => 'client'
    ]);
    $map->post('remove', '/{id}/remove', [
        'Controller' => 'App\Controllers\FiberAssignmentController',
        'Action' => 'remove'
    ]);
});
