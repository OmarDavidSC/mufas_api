<?php

namespace App\Controllers;

use App\Dows\ProfileDow;

class ProfileController extends BaseController
{

	private $dow;

	public function __construct()
	{
		$this->dow = new ProfileDow();
	}

	public function index($request)
	{
		return Response::json($this->dow->index($request));
	}

	public function update($request)
	{
		return Response::json($this->dow->update($request));
	}

	public function password($request)
	{
		return Response::json($this->dow->password($request));
	}

	public function email($request)
	{
		return Response::json($this->dow->email($request));
	}
}
