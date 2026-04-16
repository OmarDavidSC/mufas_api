<?php

namespace App\Utilities;

use Carbon\Carbon;

class FG
{

    public static function getDefaultErrorMessage()
    {
        return "Este servicio no se encuentra disponible en estos instantes. Inténtelo más tarde.";
    }

    public static function responseDefault()
    {
        return ['success' => false, 'data' => null, 'message' => self::getDefaultErrorMessage(), 'status' => 200, 'errors' => []];
    }

    public static function responseJSONDefault()
    {
        return json_decode(json_encode(self::responseDefault()));
    }

    public static function isEmail($str = "")
    {
        return filter_var($str, FILTER_VALIDATE_EMAIL);
    }

    public static function rand_string($lenght = 10, $characters = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789")
    {
        $string = '';
        $max = strlen($characters) - 1;
        for ($i = 0; $i < $lenght; $i++) {
            $string .= $characters[mt_rand(0, $max)];
        }
        return $string;
    }

    function getMinuts($fecha1, $fecha2)
    {
        $fecha1 = str_replace('/', '-', $fecha1);
        $fecha2 = str_replace('/', '-', $fecha2);
        $fecha1 = strtotime($fecha1);
        $fecha2 = strtotime($fecha2);
        return round(($fecha2 - $fecha1) / 60);
    }

    function aux_date_diff($strDateA, $strDateB)
    {
        $datetime1 = new DateTime($strDateA);
        $datetime2 = new DateTime($strDateB);
        return $datetime1->diff($datetime2);
    }

    public static function getRealIP()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP']))
            return $_SERVER['HTTP_CLIENT_IP'];

        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
            return $_SERVER['HTTP_X_FORWARDED_FOR'];

        return $_SERVER['REMOTE_ADDR'];
    }

    public static function isDate($date, $format = 'Y-m-d H:i:s')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    public static function getDateHour($format = "Y-m-d H:i:s")
    {
        date_default_timezone_set('America/Lima');
        $fecha = date($format);
        return $fecha;
    }

    public static function getHour($format = "H:i:s")
    {
        date_default_timezone_set('America/Lima');
        $fecha = date($format);
        return $fecha;
    }

    public static function getFormatDateTime($fecha, $format = 'Y-m-d H:i:s')
    {
        $date = Carbon::createFromFormat('Y-m-d H:i:s', $fecha);
        $date = $date->format($format);
        return $date;
    }

    public static function getDateString($date)
    {
        $arrayFN = ['Error', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        $mes = (int) substr($date, 5, 2);
        $anio = (int) substr($date, 0, 4);
        $mes = isset($arrayFN[$mes]) ? $arrayFN[$mes] : 'No encontrado';
        $salida = $mes . ' ' . $anio;
        return $salida;
    }

    public static function getSizeRound($size, $unit = 'B', $precision = 2)
    {
        switch (strtoupper($unit)) {
            case 'KB':
                $size = round($size / 1024, 4);
                break;
            case 'MB':
                $size = round($size / 1024 / 1024, 4);
                break;
            case 'GB':
                $size = round($size / 1024 / 1024 / 1024, 4);
                break;
        }
        return round($size, $precision);
    }

    public static function getRound($valor)
    {
        return round($valor, 2);
    }

    public static function fixSpecialCharecters($str, $except = "", $replace = "")
    {
        $replaced = str_replace(
            ["á", "é", "í", "ó", "ú", "Á", "É", "Í", "Ó", "Ú", "ñ"],
            ["a", "e", "i", "o", "u", "A", "E", "I", "O", "U", "n"],
            $str
        );
        return preg_replace('/[^A-Za-z0-9\\' . $except . ']/', $replace, $replaced);
    }

    public static function getZiseConvert($bytes)
    {
        $bytes = floatval($bytes);
        $arBytes = array(
            0 => array(
                "UNIT" => "TB",
                "VALUE" => pow(1024, 4)
            ),
            1 => array(
                "UNIT" => "GB",
                "VALUE" => pow(1024, 3)
            ),
            2 => array(
                "UNIT" => "MB",
                "VALUE" => pow(1024, 2)
            ),
            3 => array(
                "UNIT" => "KB",
                "VALUE" => 1024
            ),
            4 => array(
                "UNIT" => "B",
                "VALUE" => 1
            ),
        );

        foreach ($arBytes as $arItem) {
            if ($bytes >= $arItem["VALUE"]) {
                $result = $bytes / $arItem["VALUE"];
                $result = str_replace(".", ".", strval(round($result, 2))) . " " . $arItem["UNIT"];
                break;
            }
        }
        return $result;
    }

    public static function slugify($str, $pattern = '-')
    {
        $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'Ð', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', '?', '?', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', '?', '?', 'L', 'l', 'N', 'n', 'N', 'n', 'N', 'n', '?', 'O', 'o', 'O', 'o', 'O', 'o', 'Œ', 'œ', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'Š', 'š', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Ÿ', 'Z', 'z', 'Z', 'z', 'Ž', 'ž', '?', 'ƒ', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', '?', '?', '?', '?', '?', '?');
        $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
        return strtolower(preg_replace(array('/[^a-zA-Z0-9 -]/', '/[ -]+/', '/^-|-$/'), array('', $pattern, ''), str_replace($a, $b, $str)));
    }

    public static function getDomain()
    {
        return str_replace("www.", "", $_SERVER['HTTP_HOST']);
    }

    public static function getFechaHora_text()
    {
        date_default_timezone_set('America/Lima');
        $fecha = date("d F, Y");
        return $fecha;
    }

    public static function getYear()
    {
        date_default_timezone_set('America/Lima');
        $fecha = date("Y");
        return $fecha;
    }

    public static function debug($data = "", $debug = false)
    {
        $data = is_object($data) || is_array($data) ? json_encode($data) : $data;
        self::recordErrorLog($data, (($debug) ? $debug : debug_backtrace()));
    }

    public static function recordErrorLog($msg = "", $debug = false)
    {
        try {
            $mydebug = debug_backtrace();
            array_shift($mydebug);
            $debug = ($debug) ? $debug : $mydebug;

            $folder = __DIR__ . "/../../logs/";
            $fullpath = "{$folder}debug.log";

            // Crear el directorio si no existe
            if (!is_dir($folder)) {
                mkdir($folder, 0777, true); // El tercer parámetro `true` permite crear subdirectorios
            }

            // Verificar si el archivo existe antes de abrirlo
            if (!file_exists($fullpath)) {
                $log = fopen($fullpath, "w"); // Usamos "w" en lugar de "c"
                if ($log !== false) {
                    fclose($log);
                }
            }

            // Asegurarse de que $debug[0] existe antes de usarlo
            $debug = isset($debug[0]) ? $debug[0] : [];

            // Obtener la fecha
            $date = self::getDateHour();
            $fullmessage = "--- BEGIN " . $date . " ---\r\n";
            $fullmessage .= "FILE: " . ($debug["file"] ?? 'N/A') . "\r\n";
            $fullmessage .= "LINE: " . ($debug["line"] ?? 'N/A') . "\r\n";
            $fullmessage .= "CLASS: " . ($debug["class"] ?? 'N/A') . "\r\n";
            $fullmessage .= "FUNCTION: " . ($debug["function"] ?? 'N/A') . "\r\n";
            $fullmessage .= "MESSAGE: " . $msg . "\r\n";
            $fullmessage .= "--- END " . $date . " ---\r\n\r\n";

            // Agregar al archivo sin necesidad de leerlo primero
            file_put_contents($fullpath, $fullmessage, FILE_APPEND | LOCK_EX);
        } catch (Exception $e) {
            error_log("Error en recordErrorLog: " . $e->getMessage());
        }
    }

    public static function step($message)
    {
        return ['datetime' => FG::getDateHour(), 'message' => $message];
    }

    public static function removePortFromUrl($url)
    {
        $parsedUrl = parse_url($url);

        // Si la URL no tiene un puerto, simplemente retorna la URL original
        if (!isset($parsedUrl['port'])) {
            return $url;
        }

        // Reconstruir la URL sin el puerto
        $cleanUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];

        // Añadir el path si existe
        if (isset($parsedUrl['path'])) {
            $cleanUrl .= $parsedUrl['path'];
        }

        // Añadir el query string si existe
        if (isset($parsedUrl['query'])) {
            $cleanUrl .= '?' . $parsedUrl['query'];
        }

        // Añadir el fragmento si existe
        if (isset($parsedUrl['fragment'])) {
            $cleanUrl .= '#' . $parsedUrl['fragment'];
        }

        return $cleanUrl;
    }

    public static function uniqid()
    {
        return strtolower(md5(uniqid(time())));
    }

    public static function keyTypeLabel($type)
    {
        switch ($type) {
            case 'radio':
                return 'Selectiva';
            case 'checkbox':
                return 'Multiselectiva';
            case 'libre':
                return 'Abierta';
            default:
                return 'Personalizada';
        }
    }

    public static function formatTimestamp($timestamp)
    {
        $date = Carbon::parse($timestamp);
        $day = $date->day;
        $month = $date->translatedFormat('F'); // Nombre del mes traducido
        $time = $date->format('g:i'); // Hora en formato de 12 horas
        $meridiem = $date->format('A') === 'AM' ? 'de la mañana' : ($date->hour < 19 ? 'de la tarde' : 'de la noche');

        return "{$day} de {$month} a las {$time} {$meridiem}";
    }

    public static function quickRandom($length = 16)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return substr(str_shuffle(str_repeat($pool, 5)), 0, $length);
    }

    public static function convertDatetimeIsoToDateHour($recordingStart)
    {
        // $recordingStart = "2025-01-07T20:21:20Z";
        date_default_timezone_set('America/Lima');
        $dateTime = new \DateTime($recordingStart);
        $dateTime->setTimezone(new \DateTimeZone('UTC')); // Asegura que la zona horaria es UTC
        return $dateTime->format('Y-m-d H:i:s');
    }

    public static function formatDateTimeHuman($datetime)
    {
        if (!$datetime) return null;

        return Carbon::parse($datetime)->locale('es')->isoFormat('D [de] MMMM [del] YYYY [a las] h:mm A');
    }
}
