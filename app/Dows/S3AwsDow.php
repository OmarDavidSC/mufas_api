<?php

namespace App\Dows;

use Illuminate\Database\Capsule\Manager as DB;
use App\Utilities\FG;
use App\Middlewares\Application;
use App\Models\StorageFile;
use App\Services\S3AwsService;

class S3AwsDow {

    public function upload($request) {
        return $this->fileUpload('uploads', $request->getUploadedFiles(), 'file');
    }

    public function fileUpload($path = 'tmp', $uploads, $input, $company_id = null) {
        $response = FG::responseDefault();
        try {

            if (!$input) {
                throw new \Exception("Nombre de input requerido", 1);
            }
            if (!$uploads) {
                throw new \Exception("Archivo requerido", 1);
            }

            $files = $uploads[$input];

            if (gettype($files) == 'array') {
                $data = [];
                foreach ($files as $k => $file) {
                    $data[] = $this->insertUpload($file, $path, $company_id);
                }
            } else {
                $data = $this->insertUpload($files, $path, $company_id);
            }

            $response['success'] = true;
            $response['data']    = $data;
            $response['message'] = 'Se guardo correctamente';
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }
        return $response;
    }

    public function insertUpload($file, $path, $company_id) {
        $pathname = $path;
        if ($file->getError() != UPLOAD_ERR_OK) {
            throw new \Exception("No se encontró el archivo", 1); 
        }

        if (!$company_id) {
            $company_id = Application::getItem('company')->id;
        }

        $uniqid = strtolower(uniqid(time()));
        $filename = strtolower($file->getClientFilename());
        $uri = $pathname . "/$uniqid-$filename";
        $stream = $file->getStream()->getContents();
        
        $s3AwsService = new S3AwsService();
        $putFile = $s3AwsService->putFile($stream, $uri);
        if (!$putFile) {
            throw new \Exception("No se logró subir el archivo al S3", 1); 
        }

        $storageFile = new StorageFile();
        $storageFile->name = $file->getClientFilename();
        $storageFile->path = $s3AwsService->getUriFile($uri);
        $storageFile->uri  = $uri;
        $storageFile->type = $file->getClientMediaType();
        $storageFile->size_b = $file->getSize();
        $storageFile->size = FG::getZiseConvert($file->getSize());
        $storageFile->format = pathinfo($storageFile->name, PATHINFO_EXTENSION);
        $storageFile->bucket = $s3AwsService->getBucket();
        $storageFile->company_id = $company_id;
        $storageFile->save();
        return $storageFile;
    }

}
