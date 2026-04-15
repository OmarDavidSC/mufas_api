<?php 

namespace App\Utilities;

use \Firebase\JWT\JWT;

class FirebaseJWT {

    private static $key;

    public function __construct() {
        self::$key = $_ENV['JWT_SECRET'] ?? null;
    }

    static public function encode($payload, $key = null) {
        $key = $key ?? (self::$key ?? ($_ENV['JWT_SECRET'] ?? null));
        if (!$key) {
            throw new \Exception('JWT secret no configurada (JWT_SECRET)');
        }
        return JWT::encode($payload, $key);
    }

    static public function decode($token, $key = null) {
        $key = $key ?? (self::$key ?? ($_ENV['JWT_SECRET'] ?? null));
        if (!$key) {
            throw new \Exception('JWT secret no configurada (JWT_SECRET)');
        }
        // var_dump($key);exit;
        return JWT::decode($token, $key, array('HS256'));
    }

}