<?php

namespace App\Utilities;

class Response
{
    /**
     * Formatea una respuesta JSON.
     */
    public static function json($data = [], int $statusCode = 200): \Laminas\Diactoros\Response\JsonResponse {
        $data['message']    = isset($data['message'])   ? $data['message']  : '';
        $data['errors']     = isset($data['errors'])    ? $data['errors']   : [];
        $data['success']    = isset($data['success'])   ? $data['success']  : false;
        $data['data']       = isset($data['data'])      ? $data['data']     : '';
        return new \Laminas\Diactoros\Response\JsonResponse($data, $statusCode);
    }
}