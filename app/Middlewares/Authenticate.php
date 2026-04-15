<?php

namespace App\Middlewares;

use Illuminate\Database\Capsule\Manager as DB;
use Laminas\Diactoros\Response\JsonResponse;
use App\Middlewares\Application;
use \Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Utilities\Crypt;
use App\Utilities\FG;

class Authenticate {

    public static function factory() {
        $factory = null;
        $headers = $_SERVER;//getallheaders(); 

        // if (isset($headers['HTTP_ORIGIN'])) {
        //     $hostname = FG::removePortFromUrl($headers['HTTP_ORIGIN']); // $headers['HTTP_ORIGIN'];        
        //     $factory = DB::table('companies')->where('deleted_at')->where('host', $hostname)->first();
        // }
        // if (!$factory) {
        //     echo "Este host no esta registrado en el sistema"; exit;
        // }
        $factory = DB::table('companies')->where('deleted_at')->first();
        return $factory;
    }

    public static function keySecretToken() {
        if (Application::getItem("authorization") == "dev") {
            return self::keySecretDeveloper();
        }
        return self::keySecretApi();
    }

    public static function keySecretApi() {
        return trim(strtolower($_SERVER['HTTP_USER_AGENT'])) . '__' . trim($_ENV['JWT_SECRET']);
    }

    public static function keySecretDeveloper() {
        return Crypt::encrypt($_ENV['JWT_SECRET']);
    }

    public static function isDeveloper() {
        return isset($_ENV['DEVELOPER']) ? $_ENV['DEVELOPER'] == 'true' : false;
    }

    public static function payloadToken($data = []) {
        $payload = array (
            'iat' =>  time(),
            'exp' =>  time() + 216000, // 5 días
            'data' => $data
        );
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $secret = trim(strtolower($_SERVER['HTTP_USER_AGENT'])) . '__' . uniqid(time());
            $payload['secret'] = Crypt::encrypt($secret);
        }
        return $payload;
    }

}
