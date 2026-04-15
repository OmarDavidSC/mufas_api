<?php

namespace App\Services;

use App\Repositories\ParticipantRepository;

class ParticipantService {

    private ParticipantRepository $participantRepository;

    public function __construct() {
        $this->participantRepository = new ParticipantRepository();
    }

    public function getParticipantById(int $participant_id) {
        return $this->participantRepository->getParticipantById($participant_id);
    }

    public function getMeetingsByParticipantId(int $participant_id)  {
        return $this->participantRepository->getMeetingsByParticipantId($participant_id);
    }

}
