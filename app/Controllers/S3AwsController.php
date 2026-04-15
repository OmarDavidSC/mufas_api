<?php

namespace App\Controllers;

use App\Dows\S3AwsDow;

class S3AwsController extends BaseController {

    private $dow;

    public function __construct() {
        $this->dow = new S3AwsDow;
    }

    public function upload($request) {
        return Response::json($this->dow->upload($request));
    }

}
