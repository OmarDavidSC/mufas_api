<?php 

namespace App\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Database\Capsule\Manager as DB;

class CourseRepository {

    public function getCourseByMeetingId(int $meeting_id) : ?\stdClass  {
        return DB::table('course AS COUR')
                    ->join('section AS SECT', 'COUR.id', '=', 'SECT.course_id')
                    ->join('content AS CONT', 'SECT.id', '=', 'CONT.section_id')
                    ->select([
                        'COUR.*',
                    ])
                    ->whereNull('COUR.deleted_at')
                    ->where('CONT.meeting_id', $meeting_id)
                    ->first();
    }

}