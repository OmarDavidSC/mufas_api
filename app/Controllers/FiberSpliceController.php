<?php

namespace App\Controllers;

use App\Dows\FiberSpliceDow;

class FiberSpliceController extends BaseController
{

    private $dow;

    public function __construct()
    {
        $this->dow = new FiberSpliceDow();
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

    public function update($request)
    {
        return Response::json($this->dow->update($request));
    }

    public function remove($request)
    {
        return Response::json($this->dow->remove($request));
    }

    public function bythread($request)
    {
        return Response::json($this->dow->bythread($request));
    }
}
