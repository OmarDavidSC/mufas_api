<?php

namespace App\Dows;

use Illuminate\Database\Capsule\Manager as DB;
use App\Utilities\FG;
use App\Middlewares\Application;
use App\Models\StorageFile;

class StorageDow
{

    public static function upload($path = 'tmp', $uploads, $bucket = 'localhost', $name_input)
    {

        $rsp = FG::responseDefault();
        try {

            if (!$name_input) {
                throw new \Exception("Nombre de input requerido", 1);
            }
            if (!$uploads) {
                throw new \Exception("Archivo requerido", 1);
            }

            # FILES
            $baseurl = "uploads/$path";
            $basepath = __DIR__ . "/../../public/";
            $fullpath = $basepath . $baseurl;
            if (!is_dir($fullpath)) {
                mkdir($fullpath, 0777, true);
            }

            $file = $uploads[$name_input];
            if ($file->getError() == UPLOAD_ERR_OK) {

                $uniqid = uniqid(time());
                $fileName = strtolower($file->getClientFilename());
                $path = $baseurl . "/$uniqid-$fileName";
                $file->moveTo($path);

                $storageFile = new StorageFile();
                $storageFile->name = $file->getClientFilename();
                $storageFile->path = "/$path";
                $storageFile->type = $file->getClientMediaType();
                $storageFile->size_b = $file->getSize();
                $storageFile->size = FG::getZiseConvert($file->getSize());
                $storageFile->format = pathinfo($storageFile->name, PATHINFO_EXTENSION);
                $storageFile->bucket = $bucket;
                $storageFile->save();
            }
            $rsp['success'] = true;
            $rsp['data'] = $storageFile;
            $rsp['message'] = 'Almacenado correctamente';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public static function uploadMultiple($path = 'tmp', $uploads, $bucket = 'localhost', $name_input)
    {
        $rsp = FG::responseDefault();
        try {
            if (!$name_input) {
                throw new \Exception("Nombre de input requerido", 1);
            }
            if (!$uploads) {
                throw new \Exception("Archivo requerido", 1);
            }

            # FILES
            $baseurl = "uploads/$path";
            $basepath = __DIR__ . "/../../public/";
            $fullpath = $basepath . $baseurl;
            if (!is_dir($fullpath)) {
                mkdir($fullpath, 0777, true);
            }

            $storageFiles = [];

            // Verifica si el input contiene varios archivos
            foreach ($uploads[$name_input] as $file) {
                if ($file->getError() == UPLOAD_ERR_OK) {

                    $uniqid = uniqid(time());
                    $fileName = strtolower($file->getClientFilename());
                    $path = $baseurl . "/$uniqid-$fileName";
                    $file->moveTo($path);

                    $storageFile = new StorageFile();
                    $storageFile->name = $file->getClientFilename();
                    $storageFile->path = "/$path";
                    $storageFile->type = $file->getClientMediaType();
                    $storageFile->size_b = $file->getSize();
                    $storageFile->size = FG::getZiseConvert($file->getSize());
                    $storageFile->format = pathinfo($storageFile->name, PATHINFO_EXTENSION);
                    $storageFile->bucket = $bucket;
                    $storageFile->save();

                    $storageFiles[] = $storageFile;
                }
            }

            $rsp['success'] = true;
            $rsp['data'] = $storageFiles;
            $rsp['message'] = 'Archivos almacenados correctamente';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }

        return $rsp;
    }

    public static function uploadFull($path = 'tmp', $uploads, $bucket = 'localhost', $name_input)
    {
        $rsp = FG::responseDefault();
        try {
            if (!$name_input) {
                throw new \Exception("Nombre de input requerido", 1);
            }
            if (!$uploads) {
                throw new \Exception("Archivo requerido", 1);
            }

            # FILES
            $baseurl = "uploads/$path";
            $basepath = __DIR__ . "/../../public/";
            $fullpath = $basepath . $baseurl;
            if (!is_dir($fullpath)) {
                mkdir($fullpath, 0777, true);
            }

            $storageFiles = [];

            foreach ($uploads[$name_input] as $file) {
                if ($file->getError() == UPLOAD_ERR_OK) {

                    $uniqid = uniqid(time());
                    $fileName = strtolower($file->getClientFilename());
                    $path = $baseurl . "/$uniqid-$fileName";
                    $file->moveTo($path);

                    $storageFile = new StorageFile();
                    $storageFile->name = $file->getClientFilename();
                    $storageFile->path = "/$path";
                    $storageFile->type = $file->getClientMediaType();
                    $storageFile->size_b = $file->getSize();
                    $storageFile->size = FG::getZiseConvert($file->getSize());
                    $storageFile->format = pathinfo($storageFile->name, PATHINFO_EXTENSION);
                    $storageFile->bucket = $bucket;

                    $storageFiles[] = $storageFile;
                }
            }

            foreach ($storageFiles as $sf) {
                $sf->save();
            }

            $rsp['success'] = true;
            $rsp['data'] = $storageFiles;
            $rsp['message'] = 'Archivos almacenados correctamente';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }

        return $rsp;
    }
}
