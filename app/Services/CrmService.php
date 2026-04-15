<?php 

namespace App\Services;

use Curl;
use DateTime;
use Laminas\Diactoros\UploadedFile;

class CrmService {
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
