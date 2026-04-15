<?php

$map->attach('s3.', '/s3', function($map) {
    $map->post('upload', '/upload', [
        'Controller' => 'App\Controllers\S3AwsController',
        'Action' => 'upload'
    ]);
});

?>
