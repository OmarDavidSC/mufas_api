<?php 
namespace App\Utilities;

class Crypt {

    private static $key = '3wEZriS+BDw7nV2fb+XSKTuqtJaZSmi9ymFfLUOZDTM=';

    public static function encrypt($data, $key = null) {
        $key = $key ?? self::$key;
        $encryption_key = base64_decode($key);
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $encryption_key, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }
 
    public static function decrypt($data, $key = null) {
        $key = $key ?? self::$key;
        $encryption_key = base64_decode($key);
        list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
        return openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
    }
    
}
?>
