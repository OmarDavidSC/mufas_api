<?php 

namespace App\Repositories;

use App\Models\Participant;
use Illuminate\Support\Collection;
use Illuminate\Database\Capsule\Manager as DB;

class TeacherRepository {

    public function getTeachersByMeetingId(int $meeting_id) : ?Collection  {
/*        return DB::table('participants AS PART')
                    ->join('process_modules AS PRMO', 'PART.process_id', '=', 'PRMO.process_id')
                    ->join('module_course AS MOCU', 'PRMO.module_id', '=', 'MOCU.module_id')
                    ->join('section AS SECT', 'MOCU.course_id', '=', 'SECT.course_id')
                    ->join('content AS CONT', 'SECT.id', '=', 'CONT.section_id')
                    ->select([
                        'PART.*',
                    ])
                    ->whereNull('PART.deleted_at')
                    ->where('CONT.meeting_id', $meeting_id)
                    ->orderBy('PART.id', 'desc')
                    ->get();

*/
        return DB::table('users AS USER')
                        ->join('course_user AS COUS', 'USER.id', '=', 'COUS.user_id')
                        ->join('user_company_role as USCR', 'USER.id', '=', 'USCR.user_id')
                        // ->join('process_modules AS PRMO', 'COUS.process_id', '=', 'PRMO.process_id')
                        // ->join('module_course AS MOCU', 'COUS.module_id', '=', 'MOCU.module_id')
                        ->join('section AS SECT', 'COUS.course_id', '=', 'SECT.course_id')
                        ->join('content AS CONT', 'SECT.id', '=', 'CONT.section_id')
                        ->where('USCR.role_id', 4)
                        ->whereNull('USER.deleted_at')
                        ->whereNull('COUS.deleted_at')
                        ->where('CONT.meeting_id', $meeting_id)
                        ->select([
                            'USER.*'
                        ])->distinct()
                        ->get();
    
    }

}