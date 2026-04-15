<?php 

namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Capsule\Manager as DB;

class ModuleRepository {
    
    public function getModuleByMeetingId(int $meeting_id)  {
        return DB::table('modules AS MODU')
                    ->join('module_course AS MOCU', 'MODU.id', '=', 'MOCU.module_id')
                    ->join('section AS SECT', 'MOCU.course_id', '=', 'SECT.course_id')
                    ->join('content AS CONT', 'SECT.id', '=', 'CONT.section_id')
                    ->select([
                        'MODU.*',
                    ])
                    ->where('MODU.deleted_at')
                    ->where('CONT.meeting_id', $meeting_id)
                    ->first();
    }
}