<?php 

namespace App\Repositories;

use App\Models\Participant;
use Illuminate\Database\Capsule\Manager as DB;

class ParticipantRepository {
    
    public function getParticipantById(int $participant_id)  {
        return Participant::where('id', $participant_id)->first();
    }

    public function getMeetingsByParticipantId(int $participant_id)  {
        return DB::table('participants AS PART')
                    ->join('process_modules AS PRMO', 'PART.process_id', '=', 'PRMO.process_id')
                    ->join('module_course AS MOCU', 'PRMO.module_id', '=', 'MOCU.module_id')
                    ->join('section AS SECT', 'MOCU.course_id', '=', 'SECT.course_id')
                    ->join('content AS CONT', 'SECT.id', '=', 'CONT.section_id')
                    ->join('meetings AS MEET', 'CONT.meeting_id', '=', 'MEET.id')
                    ->select([
                        'MEET.*',
                    ])
                    ->whereNull('PART.deleted_at')
                    ->whereNull('MEET.deleted_at')
                    ->whereNull('CONT.deleted_at')
                    ->whereNull('SECT.deleted_at')
                    ->whereNull('PRMO.deleted_at')
                    ->where('PART.meeting_id', $participant_id)
                    ->orderBy('MEET.id', 'desc')
                    ->get();
    }

}