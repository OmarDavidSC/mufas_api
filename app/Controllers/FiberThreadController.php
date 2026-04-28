<?php

namespace App\Controllers;

use App\Dows\FiberThreadDow;

class FiberThreadController extends BaseController
{

    private $dow;

    public function __construct()
    {
        $this->dow = new FiberThreadDow();
    }

    public function index($request)
    {
        return Response::json($this->dow->index($request));
    }

    public function bytube($request)
    {
        return Response::json($this->dow->bytube($request));
    }

    public function show($request)
    {
        return Response::json($this->dow->show($request));
    }

    public function store($request)
    {
        return Response::json($this->dow->store($request));
    }

    public function update($request)
    {
        return Response::json($this->dow->update($request));
    }

    public function status($request)
    {
        return Response::json($this->dow->status($request));
    }

    public function trace($request)
    {
        return Response::json($this->dow->trace($request));
    }
}
