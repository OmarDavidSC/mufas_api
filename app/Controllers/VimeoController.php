<?php

namespace App\Controllers;

use App\Dows\VimeoDow;

class VimeoController extends BaseController {

    private $dow;

    public function __construct() {
        $this->dow = new VimeoDow;
    }

    public function upload($request) {
        return Response::json($this->dow->upload($request));
    }

}
