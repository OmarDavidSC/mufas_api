<?php

namespace App\Controllers;

use App\Constants\HttpStatusCode;
use App\Utilities\Response;
use Laminas\Diactoros\Response\JsonResponse;
use Rakit\Validation\Validator;
use App\Middlewares\Application;
use App\Services\NotificationService;
use App\Services\MeetingService;
use App\Validators\MeetingValidator;

class MeetingController {

	private NotificationService $notificationService;
	private MeetingService $meetingService;

	public function __construct() {
		$this->notificationService = new NotificationService();
		$this->meetingService = new MeetingService();
	}

	public function store($request): JsonResponse {
		try {
			// Variables de entrada
			$body = $request->getParsedBody();
			// Validamos las variables de entrada
			$body['company_id'] = Application::globals()->company->id;
			$errors = MeetingValidator::store($body);
			if (!empty($errors)) {
				return Response::json('Hay campos que son obligatorios', HttpStatusCode::BAD_REQUEST);
			}

			$meeting = $this->meetingService->createMeeting($body);

			if (!$meeting) {
				return Response::json(['message' => 'No se pudo crear la videoconferencia'], HttpStatusCode::INTERNAL_SERVER_ERROR);
			}

			return Response::json(['success' => true, 'data' => compact('meeting'), 'message' => 'La videoconferencia se creo correctamente'], HttpStatusCode::CREATED);
		} catch (\Exception $e) {
			return Response::json(['message' => $e->getMessage()], HttpStatusCode::INTERNAL_SERVER_ERROR);
		}
	}

	public function show($request): JsonResponse {
		try {
			// Variables de entrada
			$meeting_id = $request->getAttribute('meeting_id');
			// Validamos las variables de entrada
			$validator = new Validator;
			$rules = [
				'meeting_id' => 'required|numeric'
			];
			$validation = $validator->make(compact('meeting_id'), $rules);
			$validation->validate();
			if ($validation->fails()) {
				$errors = $validation->errors();
				return Response::json(['errors' => $errors->all(), 'message' => 'Hay campos que son obligatorios'], HttpStatusCode::BAD_REQUEST);
			}
			$meeting = $this->meetingService->getMeetingById($meeting_id);
			return Response::json(['success' => true, 'data' => compact('meeting'), 'message' => 'Show of meeting'], HttpStatusCode::OK);
		} catch (\Exception $e) {
			return Response::json(['message' => $e->getMessage()], HttpStatusCode::INTERNAL_SERVER_ERROR);
		}
	}

	public function notify($request): JsonResponse {
		try {
			// Variables de entrada
			$body = $request->getParsedBody();
			$meeting_id = $request->getAttribute('meeting_id');
			$company = Application::getItem('company');
			// Validamos las variables de entrada
			$validator = new Validator;
			$rules = [
				'meeting_id' => 'required|numeric'
			];
			$validation = $validator->make(compact('meeting_id'), $rules);
			$validation->validate();
			if ($validation->fails()) {
				$errors = $validation->errors();
				return Response::json(['errors' => $errors->all(), 'message' => 'Hay campos que son obligatorios'], HttpStatusCode::BAD_REQUEST);
			}
			$sents = $this->notificationService->notifyMeeting($meeting_id, $company);
			return Response::json(['success' => true, 'data' => $sents, 'message' => 'Se notificó a los integrantes de la videoconferencia'], HttpStatusCode::OK);
		} catch (\Exception $e) {
			return Response::json(['message' => $e->getMessage()], HttpStatusCode::INTERNAL_SERVER_ERROR);
		}
	}

	public function participants($request): JsonResponse {
		try {
			// Variables de entrada
			$body = $request->getParsedBody();
			$meeting_id = $request->getAttribute('meeting_id');
			$company = Application::getItem('company');
			// Validamos las variables de entrada
			$validator = new Validator;
			$rules = [
				'meeting_id' => 'required|numeric'
			];
			$validation = $validator->make(compact('meeting_id'), $rules);
			$validation->validate();
			if ($validation->fails()) {
				$errors = $validation->errors();
				return Response::json(['errors' => $errors->all(), 'message' => 'Hay campos que son obligatorios'], HttpStatusCode::BAD_REQUEST);
			}
			
			$meeting = $this->meetingService->getMeetingById($meeting_id);
			if (!$meeting){
				return Response::json(['message' => 'No se encontró la videoconferencia'], HttpStatusCode::BAD_REQUEST);
			}

			$participants = $this->meetingService->getParticipantsByMeetingId($meeting_id);
			return Response::json(['success' => true, 'data' => compact('participants', 'meeting'), 'message' => 'Se notificó a los integrantes de la videoconferencia'], HttpStatusCode::OK);
		} catch (\Exception $e) {
			return Response::json(['message' => $e->getMessage()], HttpStatusCode::INTERNAL_SERVER_ERROR);
		}
	}
}
