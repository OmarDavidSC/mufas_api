<?php

namespace App\Middlewares;

class Application {

    private static $data = [];

    public static $redirect_web = "/";
    public static $redirect_admin = "/admin";
    public static $redirect_admin_login = "/admin/login";

    public static function setItem($name = null, $data = []) {
        if ($name && is_string($name)) {
            self::$data[$name] = $data;
        }
    }

    public static function getItem($name = null) {
        if ($name && is_string($name)) {
            return self::$data[$name];
        }
    }

    public static function globals() {
        return json_decode(json_encode(self::$data));
    }

    public static function abort($status = null, $message = "", $redirectUrl = "") {
        $filename = '/error/404.twig';
        switch (intval($status)) {
            case 404:
                $filename = '/error/404.twig';
            break;
            case 500:
                $filename = '/error/500.twig';
            break;
        }
        $response = new \Laminas\Diactoros\Response\HtmlResponse(\App\Utilities\Twig::render($filename, compact('message', 'redirectUrl')), $status);
        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header(sprintf('%s: %s', $name, $value), false);
            }
        }
        // http_response_code($response->getStatusCode());
        echo $response->getBody();
        exit;
    }
}