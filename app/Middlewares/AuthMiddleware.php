<?php

namespace App\Middlewares;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Utilities\FirebaseJWT;
use App\Middlewares\Application;
use App\Middlewares\Authenticate;
use Firebase\JWT\ExpiredException;
use App\Utilities\Crypt;
use App\Utilities\FG;

class AuthMiddleware {

    public function verifyToken($pathname = null, $route = null, $exceptions = []) {
        $farray = explode('.', $route->name);
        if ($farray) {
            Application::setItem("authorization", strtolower(array_shift($farray)));
        }
        if ($route && $pathname) {
            if (!(in_array($route->name, $exceptions))) {
                $array = explode('.', $route->name);
                if (array_shift($array) == $pathname) {
                    $headers = array_change_key_case(getallheaders(), CASE_LOWER);
                    if (!isset($headers['authorization'])) {
                        http_response_code(401);
                        echo json_encode(['message' => 'authorization header missing', 'success'=>false]);
                        exit;
                    }
                    
                    $authHeader = explode(' ', $headers['authorization']);
                    if (count($authHeader) !== 2 || $authHeader[0] !== 'Bearer') {
                        http_response_code(401);
                        echo json_encode(['message' => 'Invalid authorization format', 'success'=>false]);
                        exit;
                    }
                    
                    $token = $authHeader[1];
                    
                    try {
                        if (!$token) {
                            http_response_code(401);
                            echo json_encode(['message' => 'Token empty', 'success'=>false]);
                            exit;    
                        }
                        return $token;
                    } catch (ExpiredException $e) {
                        http_response_code(401);
                        echo json_encode(['meesage' => 'Token has expired', 'success'=>false]);
                        exit;
                    } catch (\Exception $e) {
                        http_response_code(401);
                        echo json_encode(['message' => 'Invalid token', 'success'=>false]);
                        exit;
                    }
                }
            }
        }
    }
    
    public function callbackApi($token) {
        try {
            $decoded = FirebaseJWT::decode($token, Authenticate::keySecretApi());
            if (isset($decoded->data)) {
                $data = $decoded->data;
                $secret = Crypt::decrypt($decoded->secret);
                $user_agent = trim(strtolower(array_shift(explode('__', $secret))));
                if ($user_agent != trim(strtolower($_SERVER['HTTP_USER_AGENT']))) {
                    throw new \Exception('El token es corructo');
                }
                Application::setItem("user_id", $data->user_id);
            }
        } catch (\Exception $e) {
            http_response_code(401);
            echo json_encode(['message' => 'Invalid token', 'success'=>false]);
            exit; 
        }
    }

    public function verifyTokenApi($pathname = null, $route = null, $exceptions = []) {
        $token = $this->verifyToken($pathname, $route, $exceptions);
        if ($token) {
            $this->callbackApi($token);
        }
    }
    
    public function verifyTokenDeveloper($pathname = null, $route = null, $exceptions = []) { 
        $dev_exceptions = [];
        foreach ($exceptions as $ke => $ve) {
            $dev_exceptions[] = $pathname . $ve;
        }
        $token = $this->verifyToken($developer, $route, $dev_exceptions);
        if ($token) {
            $this->callbackDeveloper($token);
        }
    }

    public function callbackDeveloper($token) {
        $decoded = FirebaseJWT::decode($token, Authenticate::keySecretDeveloper());
        if (isset($decoded->data)) {
            $decrypted = Crypt::decrypt($decoded->data);
            Application::setItem("user_id", $decrypted->user_id);
        }
    }

}