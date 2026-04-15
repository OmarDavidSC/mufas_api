<?php 

namespace App\Repositories;

use App\Utilities\FG;
use Illuminate\Database\Capsule\Manager as DB;
use App\Models\Meeting;
use App\Models\CloudAccount;
use App\Models\CloudService;
use App\Models\Repository;  
use App\Repositories\ZoomRepository;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\AccountRepository;
use App\Repositories\LocalhostRepository;

class MeetingRepository {

    function insertMeeting(array $data): Meeting {
        return DB::transaction(function () use ($data) {
            $newMeeting = Meeting::create($data);
            return $newMeeting->fresh();
        });
    }

    function updateMeetingById(int $id, array $data): ?Meeting {
        return DB::transaction(function () use ($id, $data) {
            $meeting = Meeting::find($id);
    
            if (!$meeting) {
                throw new \Exception("El contenido con ID $id no existe.");
            }    
            // Actualizar el contenido
            $meeting->update($data);
            // Retornar el modelo actualizado
            return $meeting->fresh();
        });
    }

    public function getMeetingById(int $meeting_id, bool $all = true) : ?object {
        return Meeting::with($all ? ['recordings.recording_files', 'meeting_account', 'recording_account'] : [])->where('id', $meeting_id)->first();
    }

    public function getMeetingsByStack() : Collection {
        return Meeting::where('stack_status', 'waiting')->orderBy('id', 'desc')->limit(5)->get();
    }

    public function getMeetingUsedLicensedUser(string $licensed_user_id = '') {
        return !empty($licensed_user_id) ? 
                DB::table('meetings AS MEET')
                    ->join('content AS CONT', 'MEET.id', '=', 'CONT.meeting_id')
                    ->select([
                        'MEET.*',
                    ])
                    ->where('MEET.deleted_at')
                    ->where('CONT.deleted_at')
                    ->whereIn('MEET.status', ['waiting'])
                    ->where('MEET.host_id', $licensed_user_id)
                    ->first()
                : null;
    }

    public function getParticipantsByMeetingId(int $meeting_id) {
        return DB::table('participants AS PART')
                    ->join('process_modules AS PRMO', 'PART.process_id', '=', 'PRMO.process_id')
                    ->join('module_course AS MOCU', 'PRMO.module_id', '=', 'MOCU.module_id')
                    ->join('section AS SECT', 'MOCU.course_id', '=', 'SECT.course_id')
                    ->join('content AS CONT', 'SECT.id', '=', 'CONT.section_id')
                    ->select([
                        'PART.*',
                    ])
                    ->where('PART.deleted_at')
                    ->where('CONT.meeting_id', $meeting_id)
                    ->get();
    }
}