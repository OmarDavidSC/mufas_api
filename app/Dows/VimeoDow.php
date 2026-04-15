<?php

namespace App\Dows;

use Illuminate\Database\Capsule\Manager as DB;
use App\Utilities\FG;
use App\Middlewares\Application;
use App\Models\StorageFile;
use App\Services\VimeoService;

class VimeoDow
{

    public function upload($request)
    {
        return $this->fileUpload($request->getUploadedFiles(), 'file');
    }

    public function fileUpload($uploads, $input = null, $company_id = null)
    {
        $response = FG::responseDefault();
        try {

            if (!$uploads) {
                throw new \Exception("Archivo requerido", 1);
            }

            $files = $uploads[$input];

            if (gettype($files) == 'array') {
                $data = [];
                foreach ($files as $k => $file) {
                    $data[] = $this->insertUpload($file, $company_id);
                }
            } else {
                $data = $this->insertUpload($files, $company_id);
            }

            $response['success'] = true;
            $response['data']    = $data;
            $response['message'] = 'Se guardo correctamente';
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }
        return $response;
    }

    public function insertUpload($file, $company_id)
    {
        
        if ($file->getError() != UPLOAD_ERR_OK) {
            throw new \Exception("No se encontró el archivo", 1);
        }

        if (!$company_id) {
            $company_id = Application::getItem('company')->id;
        }

        $uniqid = strtolower(uniqid(time()));
        $filename = $uniqid . '-' . strtolower($file->getClientFilename());

        $vimeoService = new VimeoService();
        $result = $vimeoService->upload($filename, $file);
      
        if (!$result['success']) {
            throw new \Exception($result['message'], 1);
        }

        $fileUpload = $result['data'];

        $storageFile = new StorageFile();
        $storageFile->name = $file->getClientFilename();
        $storageFile->path = $fileUpload['link'];
        $storageFile->uri  = $fileUpload['uri'];
        $storageFile->type = $file->getClientMediaType();
        $storageFile->size_b = $file->getSize();
        $storageFile->size = FG::getZiseConvert($file->getSize());
        $storageFile->format = pathinfo($storageFile->name, PATHINFO_EXTENSION);
        $storageFile->embedded = $fileUpload['player_embed_url'];
        $storageFile->bucket = 'vimeo';
        $storageFile->company_id = $company_id;
        $storageFile->save();
        return $storageFile;
    }
}
