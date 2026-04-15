<?php

$map->attach('auth.', '/auth', function ($map) {

    $map->post('signin', '/signin', [
        'Controller' => 'App\Controllers\AuthController',
        'Action' => 'signin'
    ]);

    $map->post('signout', '/signout', [
        'Controller' => 'App\Controllers\AuthController',
        'Action' => 'signout'
    ]);

    $map->post('signup', '/signup', [
        'Controller' => 'App\Controllers\AuthController',
        'Action' => 'signup'
    ]);

    $map->post('password.forgot', '/password/forgot', [
        'Controller' => 'App\Controllers\AuthController',
        'Action' => 'forgotPassword'
    ]);

    $map->post('password.verify', '/password/verify', [
        'Controller' => 'App\Controllers\AuthController',
        'Action' => 'verifyKeyPassword'
    ]);

    $map->post('password.restore', '/password/restore', [
        'Controller' => 'App\Controllers\AuthController',
        'Action' => 'restorePassword'
    ]);

    $map->post('verify-token', '/verify-token', [
        'Controller' => 'App\Controllers\AuthController',
        'Action' => 'verifyToken'
    ]);
});
