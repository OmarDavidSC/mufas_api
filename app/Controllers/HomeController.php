<?php

namespace App\Controllers;

use App\Dows\{HomeDow, ReportLogDow};
use App\Middlewares\Application;

class HomeController extends BaseController
{
	public function index($request)
	{
		return 'Bienvenido al api de mufas';
	}
}
