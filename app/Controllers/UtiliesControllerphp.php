<?php

namespace App\Controllers;

class UtiliesController extends BaseController
{
    public function index($request)
    {
        return "hola";
        //return Response::json($this->dow->index($request));
    }

    public function show($request)
    {
        return Response::json($this->dow->show($request));
    }
    
    public function remove($request)
    {
        return Response::json($this->dow->remove($request));
    }
}
