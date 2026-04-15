<?php

namespace App\Services;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class AwsS3 {

    public function save($filePath, $keyName, $delete = false) {
        $rsp = [];
        $rsp["success"] = false;
        // Cargar variables de entorno
        $key = $_ENV["TOKEN_KEY"];

        // Configurar el cliente de S3
        $s3 = new S3Client([
            'version' => 'latest',
            'region'  => $_ENV['AWS_REGION'],
            'credentials' => [
                'key'    => $_ENV['AWS_ACCESS_KEY_ID'],
                'secret' => $_ENV['AWS_SECRET_ACCESS_KEY'],
            ]
        ]);

        $bucketName = $_ENV['AWS_BUCKET'];
        //$filePath = 'ruta/local/archivo.jpg';
        //$keyName = 'uploads/archivo.jpg';  // Ruta dentro del bucket

        try {
            $result = $s3->putObject([
                'Bucket' => $bucketName,
                'Key'    => $keyName,
                'SourceFile' => $filePath                
            ]);

            if (file_exists($filePath) && $delete) {
                unlink($filePath);
            } 
            $rsp["success"] = true;
            $rsp["data"] = $result['ObjectURL'];
            $rsp["mensaje"] = "Archivo subido exitosamente: " . $result['ObjectURL'] . "\n";
        } catch (AwsException $e) {
            $rsp["mensaje"] = "Error al subir archivo: " . $e->getMessage() . "\n";
        }
        return $rsp;}

}

?>