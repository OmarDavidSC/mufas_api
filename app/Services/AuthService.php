<?php 

namespace App\Services;

use Firebase\JWT\JWT;
use Curl;

class AuthService {

    public function getUrl() {
        return $_ENV['API_URL_AUTH'];
    }

    public function getAccessKey() {
        return $_ENV['API_ACCESS_KEY_AUTH'];
    }

    public function getSecrectKey() {
        return $_ENV['API_SECRET_KEY_AUTH'];
    }

    public function getToken($time = 120) {
        $token = array(
            'iat' =>  time(),
            'exp' =>  time() + $time,
            'key' =>  $this->getAccessKey()
        );
        $jwt = JWT::encode($token, $this->getSecrectKey());
        return $jwt;
    }

    public function post($uri, $args = []) {
        $token = $this->getToken();
        $curl = new Curl\Curl();
        $resp = array();
        $url =  $this->getUrl() . $uri;
        $curl->setOpt(CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $token));
        $data = $curl->post($url, $args);
        if (!$curl->error) {
            $response = json_decode($data->response, true);
        } else {
            $response["success"] = false;
            $response["message"] = "Servicio no disponible";
        }
        return $response;
    }

}