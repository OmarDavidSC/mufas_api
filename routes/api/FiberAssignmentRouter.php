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
    $map->post('release', '/{id}/release', [
        'Controller' => 'App\Controllers\FiberAssignmentController',
        'Action' => 'release'
    ]);
    $map->post('remove', '/{id}/remove', [
        'Controller' => 'App\Controllers\FiberAssignmentController',
        'Action' => 'remove'
    ]);
});
