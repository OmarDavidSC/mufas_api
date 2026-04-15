<?php

$map->attach('vimeo.', '/vimeo', function($map) {
    $map->post('upload', '/upload', [
        'Controller' => 'App\Controllers\VimeoController',
        'Action' => 'upload'
    ]);
});

?>
