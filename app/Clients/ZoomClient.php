<?php

namespace App\Clients;

use Firebase\JWT\JWT;
use App\Utilities\FG;

class ZoomClient {

	private $access_key;
	private $secret_key;
	private $version;
	private $zoom_account_id;
	private $zoom_client_id;
	private $zoom_client_secret;

	public function __construct($input) {
		$this->setInput($input);
	}
	
	public function setInput($input) {
		$input = json_decode($input, true);
		$this->access_key    	  = $input['access_key'];
		$this->secret_key    	  = $input['secret_key'];
		$this->version       	  = isset($input['version']) ? $input['version'] : 2;
		$this->zoom_account_id    = $input['zoom_account_id'];
		$this->zoom_client_id     = $input['zoom_client_id'];
		$this->zoom_client_secret = $input['zoom_client_secret'];
    }

	public function initialize($account) {
        $this->setInput($account);
    }

	public function getUrl() {
		return $_ENV['ZOOM_API_URL'];
	}

	function getBasicAuth() {
        return base64_encode($this->zoom_client_id . ":" . $this->zoom_client_secret);
    }

	public function getToken() {
        if ($this->version == 1) {
            return $this->getTokenJWT();
        } else {
            return $this->getTokenServerOAuth();
        }
    }

	private function getTokenJWT() {
        $key = $this->access_key;
        $secret = $this->secret_key;
        $token = array(
            'iss' => $key,
            'exp' => time() + 360
        );
        return JWT::encode($token, $secret);
	}

	public function getTokenServerOAuth($input = []) {
        $input['account_id'] = $this->zoom_account_id;
        $input['grant_type'] = 'account_credentials';
        $token = $this->request('POST', 'https://zoom.us/oauth/token', $input, 'basic', true);
        return $token['access_token'];
    }

	public function request(string $method, string $endpointUrl, array $jsonData = [], string $auth = "bearer", string $pathname = "") {
		$uri = $this->getUrl();
		$url = $pathname ? $endpointUrl : $uri.$endpointUrl; 
		$curl_handle = curl_init($url);
		$request_headers = array();
		// We are sending/receiving JSON data
		// REQUIRED: Without a valid authorization token, Square Endpoints will reject
		// the request
		if (strtolower($auth) == 'basic') { 
			$basicAuth = $this->getBasicAuth();
			$request_headers[] = "Authorization: Basic $basicAuth";
			$request_headers[] = "Content-Type: application/x-www-form-urlencoded";
			if (count($jsonData)) {
				$encodedData = http_build_query($jsonData);
			}
		} else if (strtolower($auth) == 'bearer') {
			$authzToken = $this->getToken();
			$request_headers[] = "Authorization: Bearer $authzToken";
			$request_headers[] = "Content-Type: application/json";
			$request_headers[] = "Accept: application/json";
			if (count($jsonData)) {
				$encodedData = json_encode($jsonData);
			}
		}
		// Encode the JSON data and set the message length
		if (count($jsonData)) {
			curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $encodedData);
			$request_headers[] = "Content-Length: " . strlen($encodedData);
		}
		curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $request_headers);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);        
		// Save the response and close the curl handle
		$jsonResponse = curl_exec($curl_handle);
		$httpcode = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);
		// http_response_code($httpcode);
		curl_close($curl_handle);
		// echo ($httpcode);exit;
		// averiguar que codigo devuelve cuando hay una videoconfeencia y cuandi no hay
		/*if($httpcode < 200 || $httpcode >= 300) {
			throw new \Exception(json_encode($jsonResponse));
		}*/
		/*return [
			'code' => $httpcode,
			'data' => json_decode($jsonResponse, true)
		];*/
		return json_decode($jsonResponse, true);
	}

	public function getMeetings($userId) {
        return $result = $this->request('GET', '/users/'.$userId.'/meetings?page_size=300');
	}

	public function getMeeting($meetingId) {
        return $this->request('GET', '/meetings/'.$meetingId);
	}

	public function getRecordings($meetingId) {
        return $this->request('GET', '/meetings/'.$meetingId.'/recordings');
	}

	public function getAllRecordings($userId) {
        return $this->request('GET', '/users/'.$userId.'/recordings');
	}

	public function getRemoveRecording($meetingId) {
        return $this->request('DELETE', '/meetings/'.$meetingId.'/recordings');
	}

	/**
	 * Obtiene los usuarios
	 */
	public function getUsers() {
		return $this->request("GET", "/users");
	}

	public function createUser($data) {
		return $this->request("POST", "/users", $data);
	}

	public function getAvailableLicensedUsers() {
		$users = $this->getUsers();
		FG::debug($users);
		if (!isset($users['users'])) {
			return [];
		}
		$licened_users = [];
		foreach ($users['users'] as $user) {
			if ($user['type'] == 2 && $this->isUserAvailable($user['id'])) {
				$licened_users[] = $user;
			}
		}
		return $licened_users; // No hay usuarios disponibles
	}

	/**
	 * Verifica si un usuario está libre, sin reuniones activas.
	 */
	private function isUserAvailable(string $userId) {
		$meetings = $this->getUserMeetingLives($userId);
		if (!isset($meetings['meetings']) || empty($meetings['meetings'])) {
			return true; // Usuario libre (sin reuniones activas)
		}
	
		/*foreach ($meetings['meetings'] as $meeting) {
			if ($meeting['status'] === 'waiting' || $meeting['status'] === 'started') {
				return false; // Usuario ocupado en una reunión activa
			}
		}*/
	
		return false; // Usuario ocupado
	}

	/**
	 * Obtiene las reuniones del usuario.
	 */
	private function getUserMeetingLives(string $userId) {
		return $this->request("GET", "/users/$userId/meetings?type=live");
	}	

    public function createUserIfPossible() {
        $users = $this->getUsers();
		$maxUsers = $this->getMaxUsers();
        if (!isset($users['users']) || count($users['users']) >= $maxUsers) {
            return null;
        }

		$uid = time();
        $email = $uid . "@devdigitalcloud.com";
		$data = array(
			'action' => "custCreate",
			'user_info' => array(
				'email' => $email,
				'type' => 2, // 1 free
				'first_name' => $uid,
				'last_name' => "PAYED"
			)
		);
        $newUser = $this->createUser($data);

        return $newUser;
    }

	/**
     * Crea una reunión con el usuario disponible
     */
    public function createMeeting(string $user_id, array $data) {
		return $this->request("POST", "/users/$user_id/meetings", $data);
	}


    /**
     * Obtiene el número máximo de usuarios permitidos según el plan.
     */
    public function getMaxUsers() {
		$response = $this->getPlanUsage();
        return $response['plan_base']['users'] ?? null;
    }

	public function getPlanUsage() {
		return $this->request("GET", "/accounts/me/plans/usage");
    }

	public function getFilePathDownloaded($url, $fullpath) {
        try {
			$basepath = __DIR__.'/../../public';
			if (!is_dir($basepath)) {
                mkdir($basepath, 0777, true);
            }
			$fullpath = $basepath . $fullpath;
            // $fz = new FuncionesZoom;
            // $url .= "?access_token=" . $fz->getJWT();
            $url .= "?access_token=" . $this->getToken();
            $ch = curl_init();
            //Set the URL that you want to GET by using the CURLOPT_URL option.
            curl_setopt($ch, CURLOPT_URL, $url);
            //Set CURLOPT_RETURNTRANSFER so that the content is returned as a variable.
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            //Set CURLOPT_FOLLOWLOCATION to true to follow redirects.
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            //$ckfile  = tempnam (__DIR__."/../logs", '$random');
            $ckfile  = __DIR__ . '/../logs/$ra8977.tmp';
    
            // curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie: cmb=" . $random));
            curl_setopt($ch, CURLOPT_COOKIEFILE, $ckfile);
            curl_setopt($ch, CURLOPT_VERBOSE, true);
            //Execute the request.
            // $data       = curl_exec($ch);
            file_put_contents($fullpath, curl_exec($ch));

            $httpcode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            //Close the cURL handle.
            curl_close($ch);
            //Print the data out onto the page.
            return $fullpath;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

}