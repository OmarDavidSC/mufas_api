<?php

namespace App\Utilities;

use App\Middlewares\Application;
use App\Extension\TwigExtension;

class Twig
{
    public static function render($filename, $data = [], $global = [])
    {
        $loader = new \Twig\Loader\FilesystemLoader('../resources/views');
        $twig = new \Twig\Environment($loader, [
            'debug' =>  true,
            'cache' => false,
        ]);
        $param = [
            'SESSION' => $_SESSION,
            'URI'     => $_SERVER["REQUEST_URI"],
            'GET'     => $_GET,
            'BASEURL' => $_ENV['APP_URL'],
            // 'ROUTE'   => isset(Application::globals()->route) ? Application::globals()->route : null,
            // 'COMPANY' => isset(Application::globals()->company) ? Application::globals()->company : null,
            'APP_CRM_WS_HOST' => $_ENV['WHATSAPP_WEBSERVICE_API_HOST']
        ];
        if (count($global)) {
            foreach ($global as $key => $item) {
                $param[$key] = $item;
            }
        }
        $twig->addGlobal("APP", $param);
        $twig->addExtension(new TwigExtension());
        return $twig->render($filename, $data);
    }



    

    public $store = [
        '01' => [
            '01' => 'FACTURA',
            '03' => 'BOLETA DE VENTA',
            '07' => 'NOTA DE CRÉDITO',
            '08' => 'NOTA DE DÉBITO',
            '09' => 'GUÍA DE REMISIÓN REMITENTE',
            '20' => 'RETENCIÓN',
            '31' => 'GUÍA DE REMISIÓN TRANSPORTISTA',
            '40' => 'PERCEPCIÓN',
        ],
        '02' => [
            'PEN' => 'S/',
            'USD' => '$',
            'EUR' => '€',
        ],
        '021' => [
            'PEN' => 'SOLES',
            'USD' => 'DÓLARES AMERICANOS',
            'EUR' => 'EUROS',
        ],
        '06' => [
            '0' => 'N/D',
            '1' => 'DNI',
            '6' => 'RUC',
        ],
        '18' => [
            '01' => 'PÚBLICO',
            '02' => 'PRIVADO'
        ],
        '19' => [
            '1' => 'ADICIONAR',
            '2' => 'MODIFICAR',
            '3' => 'ANULADO'
        ]
    ];

    const PNG_HEAD = "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A";
    const JPEG_HEAD = "\xFF\xD8\xFF";
    const GIF_HEAD = "GIF";

    public static function getMimeType($raw)
    {
        $text = substr($raw, 0, 8);
        if ($text == "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A") {
            return 'png';
        }

        $text = substr($text, 0, 3);
        if ($text == self::GIF_HEAD) {
            return 'gif';
        }

        return 'jpeg';
    }





    private function getQrImage(string $content) {
        // $renderer = new ImageRenderer(
        //     new RendererStyle(120, 0),
        //     new SvgImageBackEnd()
        // );
        // $writer = new Writer($renderer);
        // return $writer->writeString($content, 'UTF-8', ErrorCorrectionLevel::Q());
    }
}
?>