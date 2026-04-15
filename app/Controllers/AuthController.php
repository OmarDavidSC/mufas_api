<?php

namespace App\Controllers;

use App\Dows\AuthDow;

class AuthController extends BaseController
{

	private $dow;

	public function __construct()
	{
		$this->dow = new AuthDow();
	}

	public function signin($request)
	{
		return Response::json($this->dow->signin($request));
	}

	public function signout($request)
	{
		return Response::json($this->dow->signout($request));
	}

	public function signup($request)
	{
		return Response::json($this->dow->signup($request));
	}

	public function forgotPassword($request)
	{
		return Response::json($this->dow->forgotPassword($request));
	}

	public function verifyKeyPassword($request)
	{
		return Response::json($this->dow->verifyKeyPassword($request));
	}

	public function restorePassword($request)
	{
		return Response::json($this->dow->restorePassword($request));
	}

	public function verifyToken($request)
	{
		$dow = new AuthDow();
		return Response::json($dow->verifyToken($request));
	}
}
