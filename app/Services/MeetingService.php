<?php

namespace App\Services;

use App\Repositories\CompanyRepository;
use App\Repositories\MeetingRepository;
use App\Services\ZoomService;
use App\Utilities\FG;

class MeetingService {

    private CompanyRepository $companyRepository;
    private MeetingRepository $meetingRepository;
    private ZoomService $zoomService;

    public function __construct()
    {
        $this->meetingRepository = new MeetingRepository();
        $this->companyRepository = new CompanyRepository();
    }

    public function createMeeting($body) : ?object {
        // Variables de entrada
        $topic = trim($body['topic']);
        $company_id = trim($body['company_id']);
        // Cuenta de la reunión
        $meeting_account = $this->companyRepository->getTypeAccountBySelection($company_id, 2);
        if (!$meeting_account) {
            throw new \Exception('No se encuentra la cuenta de la reunión');
        }
        // Cuenta de la grabación
        $recording_account = $this->companyRepository->getTypeAccountBySelection($company_id, 1);
        if (!$recording_account) {
            throw new \Exception('No se encuentra la cuenta de grabación');
        }

        $data_meeting = [
            'topic' => $topic,
            'meeting_account_id' => $meeting_account->id,
            'recording_account_id' => $recording_account->id,
            'company_id' => $company_id
        ];

        switch (intval($meeting_account->cloud_service_id)) {
            case 2: // Zoom
                $this->zoomService = new ZoomService();
                $created_meeting = $this->zoomService->createMeetingByCloudAccount($meeting_account, $data_meeting);
                $insert = [
                    'host_id' => $created_meeting['host_id'],
                    'host_email' => $created_meeting['host_email'],
                    'uid' => $created_meeting['id'],
                    'password' => $created_meeting['password'],
                    'type' => $created_meeting['type'],
                    'topic' => $created_meeting['topic'],
                    'status' => $created_meeting['status'],
                    'start_url' => $created_meeting['start_url'],
                    'join_url' => $created_meeting['join_url'],
                    'uuid' => $created_meeting['uuid'],
                    'meeting_json' => json_encode($created_meeting),
                    'datetime_at' => FG::getDateHour(),
                    'meeting_account_id' => $data_meeting['meeting_account_id'],
                    'recording_account_id' => $data_meeting['recording_account_id'],
                    'company_id' => $data_meeting['company_id']
                ];
                return $this->meetingRepository->insertMeeting($insert);
            break;
        }
        return null;
    }

    public function getMeetingById(int $meeting_id,  bool $all = true): ?object {
        return $this->meetingRepository->getMeetingById($meeting_id, $all);
    }

    public function getParticipantsByMeetingId(int $meeting_id) {
        return $this->meetingRepository->getParticipantsByMeetingId($meeting_id);
    }

    public function getMeetingUsedLicensedUser(string $licensed_user_id) {
        return $this->meetingRepository->getMeetingUsedLicensedUser($licensed_user_id);
    }

}
