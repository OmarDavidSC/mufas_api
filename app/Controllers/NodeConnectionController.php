<?php

namespace App\Controllers;

use App\Dows\NodeConnectionDow;

class NodeConnectionController extends BaseController
{

    private $dow;

    public function __construct()
    {
        $this->dow = new NodeConnectionDow();
    }

    public function index($request)
    {
        return Response::json($this->dow->index($request));
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

    public function nstatus($request)
    {
        return Response::json($this->dow->nstatus($request));
    }

    public function nhistory($request)
    {
        return Response::json($this->dow->nhistory($request));
    }

    public function nc3urrent($request)
    {
        return Response::json($this->dow->ncurrent($request));
    }
}
