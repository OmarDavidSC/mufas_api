<?php 

namespace App\Controllers;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\Response\TextResponse;
use App\Utilities\Twig;

class BaseController
{
	public function __construct()
    {

    }
}

class Response {
	
	public static function json($data = [], $status = 200) {
		// add params []
		return new JsonResponse($data, $status);
	}
	
	public static function view($filename, $data = []) {
		// add params []
		return new HtmlResponse(Twig::render($filename, $data));
	}
}

?>