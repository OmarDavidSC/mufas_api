<?php 

namespace App\Dows;

use Illuminate\Database\Capsule\Manager as DB;
use App\Utilities\FG;

class HomeDow {

    public function index($request) {
		$rsp = FG::responseDefault();
        try {

            $users = []; 
            
            $rsp['success'] = true;
            $rsp['data']    = compact('users');
            $rsp['message'] = 'Se guardo correctamente';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
	}

}