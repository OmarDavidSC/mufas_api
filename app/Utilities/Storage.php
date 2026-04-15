<?php

namespace App\Utilities;

use App\Middlewares\Application;
use App\Services\AwsS3;
use App\Models\StorageFile;
use App\Services\VimeoService;
use App\Utilities\FG;

class Storage
{

    public static function save($uploads, $baseurl, $storage = "localhost",)
    {

        $rsp = FG::responseDefault();
        try {
            // $company = FG::getCurrentCompany();
            $carpeta = Storage::Mkdir($baseurl);
            if (!$carpeta["success"]) {
                return $carpeta;
            }
            $companyId =  Application::globals()->company->id;

            if (!is_array($uploads)) {
                $uploads = [$uploads];
            }

            $files = [];
            foreach ($uploads as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $k => $v) {
                        foreach ($v as $ky => $val) {
                            array_push($files, $val);
                        }
                    }
                } else {
                    array_push($files, $value);
                }
            }

            $contentFiles = [];
            for ($i = 0; $i < count($files); $i++) {
                $file = $files[$i];
                $contentFile = new StorageFile();
                if ($file->getError() == UPLOAD_ERR_OK) {

                    $uniqid = uniqid(time());
                    $fileName = strtolower($file->getClientFilename());
                    $path = $baseurl . "$uniqid-$fileName";
                    // $file->moveTo($path);

                    $contentFile->company_id = $companyId;
                    // $contentFile->content_id = $content->id;
                    $contentFile->name = $file->getClientFilename();
                    //
                    $contentFile->format = pathinfo($contentFile->name, PATHINFO_EXTENSION);
                    if ($contentFile->format === 'mp4') {
                        $storage = "vimeo";
                    }else{
                        $file->moveTo($path);
                    }

                    switch ($storage) {
                        case 'aws':
                            $s3 = new AwsS3();
                            $subida = $s3->save(__DIR__ . "/../../public/" . $path, $path, true);

                            $contentFile->path = $subida["data"];
                            break;

                        case 'vimeo':
                            $vimeo = new VimeoService();
                            $result = $vimeo->upload($fileName, $file);
                            if (!$result["success"]) {
                                throw new \Exception($result['message']);
                            }
                            $fileUpload = $result['data'];
                            if (!$fileUpload) {
                                throw new \Exception('No found file upload');
                            }

                            $contentFile->folder = $vimeo->getFolder();                            
                            $contentFile->uri = $fileUpload["uri"];
                            $contentFile->path = $fileUpload["link"];
                            $contentFile->embedded = $fileUpload["player_embed_url"];
                            $contentFile->upload_file_json = json_encode($fileUpload);
                            break;

                        default:
                            $contentFile->path = "/$path";
                            break;
                    }

                    $contentFile->bucket = $storage;

                    $contentFile->type = $file->getClientMediaType();
                    $contentFile->size_b = $file->getSize();
                    $contentFile->size = FG::getZiseConvert($file->getSize());
                    $contentFile->format = pathinfo($contentFile->name, PATHINFO_EXTENSION);

                    $contentFile->save();
                }
                $rsp["success"] = true;
                $rsp["message"] = "Almacenado correctamente.";
                array_push($contentFiles, $contentFile);
            }
            $rsp["data"] = $contentFiles;
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public static function saveMultiple($uploads, $baseurl, $storage = "localhost",)
    {

        $rsp = FG::responseDefault();
        try {
            // $company = FG::getCurrentCompany();
            $carpeta = Storage::Mkdir($baseurl);
            if (!$carpeta["success"]) {
                return $carpeta;
            }
            $companyId =  Application::globals()->company->id;

            if (!is_array($uploads)) {
                $uploads = [$uploads];
            }

            $files = [];
            foreach ($uploads as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $k => $v) {
                        foreach ($v as $ky => $val) {
                            array_push($files, $val);
                        }
                    }
                } else {
                    array_push($files, $value);
                }
            }

            $contentFiles = [];
            foreach ($files as $file) {
                if ($file->getError() == UPLOAD_ERR_OK) {
                    $uniqid = uniqid(time());
                    $contentFile = new StorageFile();
                    $fileName = strtolower($file->getClientFilename());
                    $path = $baseurl . "$uniqid-$fileName";

                    $contentFile->company_id = $companyId;
                    // $contentFile->content_id = $content->id;
                    $contentFile->name = $file->getClientFilename();
                    $contentFile->format = pathinfo($contentFile->name, PATHINFO_EXTENSION);

                    $vimeoUrl = null;
                    if ($contentFile->format === 'mp4') {
                        $storage = "vimeo";
                    }else{
                        $file->moveTo($path);
                    }

                    switch ($storage) {
                        case 'aws':
                            $s3 = new AwsS3();
                            $subida = $s3->save(__DIR__ . "/../../public/" . $path, $path, true);

                            $contentFile->path = $subida["data"];
                            break;

                        case 'vimeo':
                            $vimeo = new VimeoService();
                            $result = $vimeo->upload($fileName, $file);
                            if (!$result["success"]) {
                                throw new \Exception($result['message']);
                            }
                            $fileUpload = $result['data'];
                            if (!$fileUpload) {
                                throw new \Exception('No found file upload');
                            }

                            $contentFile->folder = $vimeo->getFolder();                            
                            $contentFile->uri = $fileUpload["uri"];
                            $contentFile->path = $fileUpload["link"];
                            $contentFile->embedded = $fileUpload["player_embed_url"];
                            $contentFile->upload_file_json = json_encode($fileUpload);
                            break;

                        default:
                            $contentFile->path = "/$path";
                            break;
                    }

                    $contentFile->bucket = $storage;

                    $contentFile->type = $file->getClientMediaType();
                    $contentFile->size_b = $file->getSize();
                    $contentFile->size = FG::getZiseConvert($file->getSize());

                    $contentFile->save();
                    $contentFiles[] = $contentFile;
                }
            }
            if (!empty($contentFiles)) {
                $rsp["success"] = true;
                $rsp["message"] = count($contentFiles) . " archivos almacenados correctamente.";
                $rsp["data"] = $contentFiles;
            } else {
                throw new \Exception("No se pudieron almacenar los archivos.");
            }
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public static function Mkdir($targetDir){
        $rsp = FG::responseDefault();
        $rsp['message'] = "Exito: La carpeta '$targetDir' se creo correctamente.";
        // Verifica si la carpeta no existe
        if (!is_dir($targetDir)) {
            // Intenta crear la carpeta con permisos 755
            if (!mkdir($targetDir, 0755, true)) {
                $rsp['message'] = "Error: No se pudo crear la carpeta '$targetDir'. Verifica los permisos.";
                return $rsp;
            }
        }

        // Verifica si la carpeta es escribible
        if (!is_writable($targetDir)) {
            $rsp['message'] = "Error: La carpeta '$targetDir' no tiene permisos de escritura.";
            return $rsp;
        }

        $rsp["success"] = true;
        return $rsp;

    }
}
