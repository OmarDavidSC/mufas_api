<?php

namespace App\Controllers;

use App\Dows\FiberDow;

class FiberController extends BaseController
{

	private $dow;

	public function __construct()
	{
		$this->dow = new FiberDow();
	}

	public function index($request)
	{
		return Response::json($this->dow->index($request));
	}

    public function adm($request)
	{
		return Response::json($this->dow->adm($request));
	}

    public function store($request)
	{
		return Response::json($this->dow->store($request));
	}

	public function update($request)
	{
		return Response::json($this->dow->update($request));
	}

	public function remove($request)
	{
		return Response::json($this->dow->remove($request));
	}
}
