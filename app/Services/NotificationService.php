<?php

namespace App\Services;

use App\Utilities\Twig;
use App\Utilities\Mailer;
use App\Repositories\ParticipantRepository;
use App\Repositories\TeacherRepository;
use App\Repositories\ModuleRepository;
use App\Repositories\CourseRepository;
use App\Repositories\MeetingRepository;
use App\Services\MeetingService;
use App\Utilities\FG;

class NotificationService {
    private MeetingRepository $meetingRepository;
    private CourseRepository $courseRepository;
    private ModuleRepository $moduleRepository;
    private ParticipantRepository $participantRepository;
    private TeacherRepository $teacherRepository;
    private MeetingService $meetingService;
    private Mailer $mailer;

    public function __construct() {
        $this->meetingRepository = new MeetingRepository();
        $this->courseRepository = new CourseRepository();
        $this->moduleRepository = new ModuleRepository();
        $this->participantRepository = new ParticipantRepository();
        $this->teacherRepository = new TeacherRepository();
        $this->meetingService = new MeetingService();
        $this->mailer = new Mailer();
    }

    public function notifyMeeting(int $meeting_id, $company) {
        $meeting = $this->meetingRepository->getMeetingById($meeting_id);
        if (!$meeting) {
            throw new \Exception('No se encuentra la reunión');
        }

        $course = $this->courseRepository->getCourseByMeetingId($meeting->id);
        if (!$course) {
            throw new \Exception('No se encontró el curso');
        }

        $module = $this->moduleRepository->getModuleByMeetingId($meeting->id);
        if (!$module) {
            throw new \Exception('No se encontró el módulo');
        }

        $sents = [];

        $teachers = $this->teacherRepository->getTeachersByMeetingId($meeting->id);
        foreach ($teachers as $teacher) {
            $result = $this->sendEmailToTeacher($teacher, $meeting, $course, $module, $company);
            $sents[] = [
                'teacher' => $teacher,
                'mail'  => $result
            ];
        }

        $participants = $this->meetingService->getParticipantsByMeetingId($meeting->id);
        foreach ($participants as $participant) {
            $result = $this->sendEmailToParticipant($participant, $meeting, $course, $module, $company);
            $sents[] = [
                'participant' => $participant,
                'mail'  => $result
            ];
        }

        return $sents;
    }

    private function sendEmailToParticipant($participant, $meeting, $course, $module, $company) {
        $response = FG::responseDefault();
        try {
            $fullname = trim("{$participant->name} {$participant->paternal_surname} {$participant->maternal_surname}");
            $body = Twig::render('mail/meeting/participant.notify.twig', [
                'email' => $participant->email,
                'fullname' => $fullname,
                'meeting' => $meeting,
                'course' => $course,
                'module' => $module,
                'company' => $company
            ]);

            $params = [
                'subject' => 'Invitación a la clase en línea',
                'body' => $body,
                'recipients' => [
                    ['email' => $participant->email, 'name' => $fullname]
                ],
                'company' => $company
            ];

            $result = $this->mailer->sendEmail($params);
            if (!$result['success']) {
                throw new \Exception('No se pudo enviar el correo electrónico.');
            }
            $response['success'] = true;
            $response['data']    = $result['data'];
            $response['message'] = 'Correo electrónico enviado correctamente.';
        } catch (\Exception $e) {
            $response['message'] = "Error al enviar email: " . $e->getMessage();
        }
        return $response;
    }

    private function sendEmailToTeacher($teacher, $meeting, $course, $module, $company) {
        $response = FG::responseDefault();
        try {
            $fullname = trim("{$teacher->name} {$teacher->paternal_surname} {$teacher->maternal_surname}");
            $body = Twig::render('mail/meeting/teacher.notify.twig', [
                'email' => $teacher->email,
                'fullname' => $fullname,
                'meeting' => $meeting,
                'course' => $course,
                'module' => $module,
                'company' => $company
            ]);

            $params = [
                'subject' => 'Información de su clase en línea',
                'body' => $body,
                'recipients' => [
                    ['email' => $teacher->email, 'name' => $fullname]
                ],
                'company' => $company
            ];

            $result = $this->mailer->sendEmail($params);
            if (!$result['success']) {
                throw new \Exception($result['message']);
            }
            $response['success'] = true;
            $response['data']    = $result['data'];
            $response['message'] = 'Correo electrónico enviado correctamente.';
        } catch (\Exception $e) {
            $response['message'] = "Error al enviar email: " . $e->getMessage();
        }
        return $response;
    }
}
