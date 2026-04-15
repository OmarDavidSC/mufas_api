<?php

namespace App\Controllers;

use App\Dows\FileDow;

class FileController extends BaseController {

    private $dow;

    public function __construct() {
        $this->dow = new FileDow;
    }

    public function upload($request) {
        return Response::json($this->dow->upload($request));
    }

}
