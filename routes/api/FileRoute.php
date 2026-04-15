<?php

$map->attach('file.', '/file', function($map) {
    $map->post('upload', '/upload', [
        'Controller' => 'App\Controllers\FileController',
        'Action' => 'upload'
    ]);
});

?>
