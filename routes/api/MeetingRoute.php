<?php

$map->attach('meeting.', '/meetings', function ($map) {

    $map->post('store', '/store', [
        'Controller' => 'App\Controllers\MeetingController',
        'Action' => 'store'
    ]);

    $map->attach('meeting_id.', '/{meeting_id}', function ($map) {

        $map->post('show', '/show', [
            'Controller' => 'App\Controllers\MeetingController',
            'Action' => 'show'
        ]);
    
        $map->post('notify', '/notify', [
            'Controller' => 'App\Controllers\MeetingController',
            'Action' => 'notify'
        ]);

        $map->post('participants', '/participants', [
            'Controller' => 'App\Controllers\MeetingController',
            'Action' => 'participants'
        ]);
    });
    

});
