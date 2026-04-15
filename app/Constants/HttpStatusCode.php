<?php
namespace App\Constants;

class HttpStatusCode
{
    // Respuestas exitosas
    public const OK = 200;
    public const CREATED = 201;
    public const NO_CONTENT = 204;

    // Errores del cliente
    public const BAD_REQUEST = 400;
    public const UNAUTHORIZED = 401;
    public const FORBIDDEN = 403;
    public const NOT_FOUND = 404;

    // Errores del servidor
    public const INTERNAL_SERVER_ERROR = 500;
    public const SERVICE_UNAVAILABLE = 503;
}