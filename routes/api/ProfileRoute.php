<?php

$map->attach('profiles.', '/profile', function ($map) {

    $map->post('update', '/{id}/update', [
        'Controller' => 'App\Controllers\ProfileController',
        'Action' => 'update'
    ]);

    $map->post('password', '/{id}/password', [
        'Controller' => 'App\Controllers\ProfileController',
        'Action' => 'password'
    ]);

    $map->post('email', '/{id}/email', [
        'Controller' => 'App\Controllers\ProfileController',
        'Action' => 'email'
    ]);
});
