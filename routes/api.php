<?php

$map->attach('api.', '/api', function ($map) {
    $map->attach('aws.', '/aws', function ($map) {
        include __DIR__ . "/api/S3AwsRoute.php";
    });

    include __DIR__ . "/api/AuthRoute.php";
    include __DIR__ . "/api/VimeoRoute.php";
    include __DIR__ . "/api/CompanyRoute.php";
    include __DIR__ . "/api/FileRoute.php";
    include __DIR__ . "/api/UtiliesRoute.php";
    include __DIR__ . "/api/MeetingRoute.php";
    include __DIR__ . "/api/S3AwsRoute.php";
    include __DIR__ . "/api/ProfileRoute.php";

    include __DIR__ . "/api/NodeRoute.php";
    include __DIR__ . "/api/FiberRouter.php";
    include __DIR__ . "/api/TubeRouter.php";
    include __DIR__ . "/api/ClientRouter.php";
    include __DIR__ . "/api/SplitterRoute.php";
    include __DIR__ . "/api/FiberAssignmentRouter.php";
    include __DIR__ . "/api/FiberThreadRoute.php";
    include __DIR__ . "/api/FiberSpliceRoute.php";
    include __DIR__ . "/api/NodeConnectionRoute.php";
});
