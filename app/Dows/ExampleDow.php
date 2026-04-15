<?php

namespace App\Dows;

use App\Models\ReportLog;
use App\Utilities\FG;
use Illuminate\Database\Capsule\Manager as DB;
use Jenssegers\Mongodb\Eloquent\Builder;
use MongoDB\BSON\ObjectId;
use App\Dows\StorageDow;
use App\Services\PhpSheetDow;
use App\Utilities\Mailer;
use App\Utilities\Twig;

class ExampleDow
{

    public function insert($request)
    {
        $response = FG::responseDefault();
        try {
            $upload = $request->getUploadedFiles();

            $storage = new StorageDow();
            $str = $storage->upload('plantilla', $upload, 'localhost', 'plantilla', 1);

            if (!$str["success"]) {
                $str['status'] = 401;
                throw new \Exception("Error inesperado al cargar el archivo. " . $str["message"], 1);
            }


            //LEER EXCEL
            $PathSheet = __DIR__ . "/../../public" . $str["data"]->path;
            $sheet = new PhpSheetDow();
            //leer excel
            //    $inputFileType = 'Xls';
            //    $inputFileType = 'Xlsx';
            //    $inputFileType = 'Xml';
            //    $inputFileType = 'Ods';
            //    $inputFileType = 'Slk';
            //    $inputFileType = 'Gnumeric';
            //    $inputFileType = 'Csv';

            /*//////////////BASE CERTIFICADOS/////////////*/
            $dataCert = $sheet->load('Xlsx', $PathSheet, 'usuarios', 'A2:F1000');

            if (!$dataCert['success']) {
                throw new \Exception("Error inesperado al leer el archivo " . $dataCert["message"], 1);
                $rsp["status"] = 403;
            }


            //REGISTRO DE PARTICIPANTES EN MONGO
            $reportM = new ReportLog();
            $reportM->name = "LISTA participante";
            $reportM->company_id = 1;
            $reportM->user_created = 1;
            $reportM->lista = $dataCert;

            $reportM->save();

            $response['success'] = true;
            $response['data'] = $reportM;
            $response['message'] = 'Datos insertados con éxito en la colección report_logs.';
        } catch (\Exception $e) {
            $response['message'] = 'Error al insertar en la colección: ' . $e->getMessage();
        }

        return $response;
    }

    public function email($request)
    {
        $response = FG::responseDefault();
        try {
            $input = $request->getParsedBody();

            $mailer = new Mailer();
            $body = Twig::render('mail/recover.user_credentials.twig', [
                'fullname' => "Omar David Serquen Coronado",
                'company_name' => "Omar Dev",
                'username' => "omar",
                'email' => "serquencoronadoomardavid@gmail.com",
                'password' => "12334"
            ]);

            $params = array(
                'subject' => 'Tus credenciales de acceso',
                'body' => "$body",
                'recipients' => array(
                    array('email' => "serquencoronadoomardavid@gmail.com", 'name' => "Omar David Serquen Coronado")
                ),
                'company' => "Omar Dev"
            );
            $result = $mailer->sendEmail($params);
            if (!$result['success']) {
                throw new \Exception('No se pudo enviar el correo electrónico.');
            }

            $response['success'] = true;
            $response['data'] = $result;
            $response['message'] = "Usuario Administrador registrado con éxito";
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }
        return $response;
    }
}
