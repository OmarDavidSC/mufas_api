<?php

$map->attach('nconnections.', '/nconnection', function ($map) {

    $map->post('index', '', [
        'Controller' => 'App\Controllers\NodeConnectionController',
        'Action' => 'index'
    ]);
    $map->post('store', '/store', [
        'Controller' => 'App\Controllers\NodeConnectionController',
        'Action' => 'store'
    ]);
    $map->post('update', '/{id}/update', [
        'Controller' => 'App\Controllers\NodeConnectionController',
        'Action' => 'update'
    ]);
    $map->post('shortespath', '/{id}/shortespath', [
        'Controller' => 'App\Controllers\NodeConnectionController',
        'Action' => 'shortespath'
    ]);
    $map->post('remove', '/{id}/remove', [
        'Controller' => 'App\Controllers\NodeConnectionController',
        'Action' => 'remove'
    ]);
    $map->get('nstatus', 'nstatus', [
        'Controller' => 'App\Controllers\NodeConnectionController',
        'Action' => 'nstatus'
    ]);
    $map->get('nhistory', '/{id}/nhistory', [
        'Controller' => 'App\Controllers\NodeConnectionController',
        'Action' => 'nhistory'
    ]);
    $map->get('ncurrent', '/{id}/ncurrent', [
        'Controller' => 'App\Controllers\NodeConnectionController',
        'Action' => 'ncurrent'
    ]);
});
