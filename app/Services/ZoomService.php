<?php
namespace App\Services;

use App\Utilities\FG;
use App\Clients\ZoomClient;
use App\Services\MeetingService;

class ZoomService {

    private MeetingService $meetingService;

    public function __construct() {
        $this->meetingService = new MeetingService();
    }

    public function findAvailableLicensedUser($licensed_users) {
        if (count($licensed_users)) {
            foreach ($licensed_users as $klicensed_user) {
                if (isset($klicensed_user['id'])) {
                    $flicensed_user = $this->meetingService->getMeetingUsedLicensedUser($klicensed_user['id']);
                    FG::debug($flicensed_user);
                    if (!$flicensed_user) {
                        return $klicensed_user;
                    }
                }
            }
        } 
        return null;
    }

    public function createMeetingByCloudAccount(object $account, array $data_meeting): ?array {
        $zoomClient = new ZoomClient($account->credencials);
        $licensed_users = $zoomClient->getAvailableLicensedUsers();
        $licensed_user = $this->findAvailableLicensedUser($licensed_users);
        if (!$licensed_user) {
            $new_licensed_user = $zoomClient->createUserIfPossible();
            if (!isset($new_licensed_user['id'])) {
                throw new \Exception('No se logro obtener un usuario para crear la reunión');
            }
            $licensed_user = $new_licensed_user;
        }
        $without_topic = 'Sin Tema ' . time(); 
        $data = array(
            'topic' => $without_topic,
            'type' => 1,
            'agenda' => $without_topic,
            'password' => FG::quickRandom(8),
            'settings' => array(
                'host_video' => true,
                'participant_video' => false,
                'join_before_host' => true,
                'mute_upon_entry' => true,
                'audio' => "both",
                'auto_recording' => "cloud"
            )
        );

        if (isset($data_meeting['topic']) && $data_meeting['topic']) {
            $data['topic'] = $data_meeting['topic'];
            $data['agenda'] = $data_meeting['topic'];
        }

        $created_meeting = $zoomClient->createMeeting($licensed_user['id'], $data);
        if (!isset($created_meeting['id'])) {
            throw new \Exception('No se logro crear la reunión');
        }
        return $created_meeting;
    }
}