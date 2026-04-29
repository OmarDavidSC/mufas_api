<?php

namespace App\Controllers;

use App\Dows\FiberAssignmentDow;

class FiberAssignmentController extends BaseController
{

    private $dow;

    public function __construct()
    {
        $this->dow = new FiberAssignmentDow();
    }

    public function index($request)
    {
        return Response::json($this->dow->index($request));
    }

    public function store($request)
    {
        return Response::json($this->dow->store($request));
    }

    public function show($request)
    {
        return Response::json($this->dow->show($request));
    }

    public function release($request)
    {
        return Response::json($this->dow->release($request));
    }

    public function client($request)
    {
        return Response::json($this->dow->client($request));
    }

    public function remove($request)
    {
        return Response::json($this->dow->remove($request));
    }
}
