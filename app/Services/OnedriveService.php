<?php

namespace App\Services;

use App\Middlewares\Application;
use App\Models\Account;
use Illuminate\Database\Capsule\Manager as DB;
use TheNetworg\OAuth2\Client\Provider\Azure;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use App\Utilities\FG;
use Curl;
use DateTime;

// https://github.com/TheNetworg/oauth2-azure/issues/83

class OnedriveService {

    private $azure;
    private $scope = [
        'openid',
        'profile',
        'email',
        'offline_access',
        'files.read',
        'files.read.all',
        'files.readwrite',
        'files.readwrite.all',
        'https://graph.microsoft.com/User.Read',

        'Files.ReadWrite'
    ];
    private $token;

    function __construct() {
        $this->azure = new Azure([
            'clientId'      => $_ENV['ONEDRIVE_CLIENT_ID'],
            'clientSecret'  => $_ENV['ONEDRIVE_CLIENT_SECRET'],
            'redirectUri'   => $_ENV['ONEDRIVE_REDIRECT_URI'],
            //'metadata'    => 'https://login.microsoftonline.com/xxx.onmicrosoft.com/v2.0/.well-known/openid-configuration',
            //'grant_type'    => 'client_credentials',
            'scope'         => $this->scope,
            'defaultEndPointVersion' => Azure::ENDPOINT_VERSION_2_0 //'2.0'
        ]);
    }

    public function getAzure() {
       return $this->azure;
    }

    public function getGraph() {
        $graph = new Graph();
        $graph->setAccessToken($this->token['access_token']);
        return $graph;
     }

    public function setToken($token, $id) {
        $token = json_decode($token, true);
        if (is_array($token) && $this->isExpired($token['expires'])) {
            $newToken = $this->azure->getAccessToken('refresh_token', [
                'scope'         => $this->scope,
                'refresh_token' => $token['refresh_token']
            ]);
            DB::table("accounts")->where('id', $id)->update(['token' => json_encode($newToken)]);
            $token = json_decode(json_encode($newToken), true);
        }
        $this->token = $token;
    }

    public function folders($id = null) {
        $url = empty($id) ? "/me/drive/root/children" : "/me/drive/items/{$id}/children";
        $response = $this->getGraph()->createRequest("GET", $url)->execute();
        return $response->getBody();
    }

    public function me() {
        $response = $this->getGraph()->createRequest("GET", "/me")->execute();
        return $response->getBody();
    }

    public function folder($path = null) {
        $response = $this->getGraph()->createRequest("GET", "/me/drive/root:/{$path}")->execute();
        return $response->getBody();
    }

    public function uploadFilePath($filepath, $folderId = null) {

        $size_limit = 1024*1024*4; // 4;
        $fileTree = explode("/", $filepath);
        $filename = array_pop($fileTree);
        $filesize = filesize($filepath);
        if ($size_limit >= $filesize) {
            $url = empty($folderId) ?  "/me/drive/root:/".$filename.":/content" : "/me/drive/items/{$folderId}:/".$filename.":/content";
            $response = $this->getGraph()->createRequest("PUT", $url)
                                            ->attachBody([
                                                'items' => [
                                                    "@odata.type" => "microsoft.graph.driveItemUploadableProperties",
                                                    "@microsoft.graph.conflictBehavior" => "rename",
                                                    "name" => $filename
                                                ]
                                            ])
                                            ->upload($filepath);
            $fileupload = $response->getBody();
            unlink($filepath);
        } else {
            $url = empty($folderId) ? "/me/drive/root:/".$filename.":/createUploadSession" : "/me/drive/items/".$folderId.":/".$filename.":/createUploadSession";
            $response = $this->getGraph()->createRequest("POST", $url)
                            ->attachBody([
                                "fileSystemInfo"=> [ "@odata.type"=> "microsoft.graph.driveItemUploadableProperties" ],
                                "@microsoft.graph.conflictBehavior" => "rename",
                                "name" => $filename
                            ])
                            ->execute();
            $response = $response->getBody(); 
            $uploadUrl = @$response['uploadUrl'];
            if (!$uploadUrl){
                throw new Exception('No se encontró la url que permite subir archivos de gran tamaño.');
            }

            $url = $uploadUrl;
            $fragSize = 1024*1024*4;
            $file = file_get_contents($filepath);
            $fileSize = strlen($file);
            $numFragments = ceil($fileSize / $fragSize);
            $bytesRemaining = $fileSize;
            $i = 0;
            $response = null;

            while ($i < $numFragments) {
                $chunkSize = $numBytes = $fragSize;
                $start = $i * $fragSize;
                $end = $i * $fragSize + $chunkSize - 1;
                $offset = $i * $fragSize;

                if ($bytesRemaining < $chunkSize) {
                    $chunkSize = $numBytes = $bytesRemaining;
                    $end = $fileSize - 1;
                }

                if ($stream = fopen($filepath, 'r')) {
                    // get contents using offset
                    $data = stream_get_contents($stream, $chunkSize, $offset);
                    fclose($stream);
                }

                $contentRange = " bytes " . $start . "-" . $end . "/" . $fileSize;
                $headers = array(
                    "Content-Length: $numBytes",
                    "Content-Range: $contentRange"
                );

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                $response = curl_exec($ch);
                $info = curl_getinfo($ch);
                curl_close($ch);

                $bytesRemaining = $bytesRemaining - $chunkSize;
                $i++;
            }
            unlink($filepath);
            return json_decode($response, true);
        }

        /*$response = $this->getGraph()->createRequest("GET", "/me/drive/items/".$fileupload['id']."?select=id,name,webUrl,size,@microsoft.graph.downloadUrl&expand=thumbnails")
                        ->execute();
        $fileupload = $response->getBody();*/
        
        return $fileupload;
        /*$response = $graph->createRequest("POST", "/me/drive/items/{$fileupload['id']}/createLink")
                        ->attachBody([ "type" => "edit", "scope" => "anonymous" ])
                        ->execute();
        $folderlink = $response->getBody();*/

    }





    public function copyFile($cid, $filename = null, $pid = null) {
        $graph = $this->getGraph();
        $folderParent = self::file($cid);
        $path = pathinfo($folderParent['name']);
        $copyname = $filename ? ($filename . '.' . $path['extension']) : uniqid() . "-" . $path['filename'] . "." . $path['extension'];

        $driveId = $folderParent['parentReference']['driveId'];
        $id = $folderParent['parentReference']['id'];
        if ($pid) {
            $response = $graph->createRequest("GET", "/me/drive/items/".$pid)->execute();
            $folderParent = $response->getBody();
            $driveId = $folderParent['parentReference']['driveId'];
            $id = $folderParent['id'];
        }
        $response = $graph->createRequest("POST", "/me/drive/items/".$cid."/copy")
                        ->attachBody([
                            "parentReference" => [
                                "driveId"=> $driveId,
                                "id" => $id,
                            ],
                            "name" => $copyname
                        ])
                        ->execute();
        $folder = [];
        if ($response->getStatus() == 202) {
            $folder = $response->getBody();
            $pathname = $response->getHeaders()['Location'][0];
            if ($pathname) {
                $response = $this->getFileStatus($pathname);
                $id = $response['resourceId'];
                $response = $graph->createRequest("GET", "/me/drive/items/".$id."?select=id,name,webUrl,size,@microsoft.graph.downloadUrl&expand=thumbnails")->execute();
                $folder = $response->getBody();
                $folderlink = $this->createLink($id);
                if (count($folderlink) > 0) {
                    $folder['webUrl'] = $folderlink['link']['webUrl'];
                }
            }
        }
        return $folder;
    }

    public function getFileStatus($url) {
        sleep(2);
        $curl = new Curl\Curl();
        $result = $curl->get($url, $args);
        if (!$curl->error) {
            $response = json_decode($result->response, true);
            $status = $response['status'];
            if ($status != 'completed') {
                $response = $this->getFileStatus($url);
            }
            return $response;
        } else {
            throw new \Exception('Hubo un error en el servicio');
        }
    }

    public function isExpired($expires) {
        return ($expires > (time() + 500)) ? false : true;
    }

    public function childrens($parentId) {
        $graph = $this->getGraph();
        $response = $graph->createRequest("GET", "/me/drive/items/".$parentId."/children?expand=thumbnails")->execute();
        return $response->getBody();
    }

    public function createFolder($name, $parentId = null) {
        $graph = $this->getGraph();
        $url = $parentId ? "/me/drive/items/".$parentId."/children" : "/me/drive/root/children";
        $response = $graph->createRequest("POST", $url)
                        ->attachBody([
                            "folder" => (Object)[],
                            "name" => $name,
                            "@microsoft.graph.conflictBehavior"=> "replace"
                        ])
                        ->execute();
        $folder = [];
        if ($response->getStatus() == 201) {
            $folder = $response->getBody();
        }
        return $folder;
    }

    public function export($id) {
        $graph = $this->getGraph();
        $response = $graph->createRequest("GET", "/drive/items/".$id."/content")->execute();
        $pathname = null;
        if ($response->getStatus() == 200) {
            $pathname = $response->getHeaders()['Content-Location'][0];
        }
        return $pathname;
    }

    public function file($id, $query = null) {
        $url = "/me/drive/items/".$id;
        if ($query) {
            $url = $url . "?" . $query;
        }
        $response = $this->getGraph()->createRequest("GET", $url)->execute();
        return $response->getBody();
    }

    public function createLink($id) {
        $response = $this->getGraph()->createRequest("POST", "/me/drive/items/{$id}/createLink")
                        ->attachBody([ "type" => "embed", "scope" => "anonymous" ])
                        ->execute();
        return $response->getBody();
    }


    




    // temp methods for services - refactor
    public function setupFirstRepositoryAccount() {
        $account = Account::where('companyId', Application::globals()->company->id)
            ->whereIn('repositoryId', [2, 3])
            ->where('status', 1)
            ->where('deletedAt')
            ->latest('createdAt')
            ->first();
        $this->setToken($account->token, $account->id);
        return $this;
    }

    public function uploadSmallFile($file, $folderName) {
        $basepath = __DIR__.'/../../public/';
		$filePathInfo = pathinfo($file->getClientFileName());
		$filename = $filePathInfo['filename'];
		$extension = $filePathInfo['extension'];
		$filepath = $basepath . $filename . '_' . (new DateTime())->format('YmdHisv') . '.' . $extension;

		file_put_contents($filepath, $file->getStream()->getContents());

        
        
        $fileTree = explode("/", $filepath);
        $filename = array_pop($fileTree);
        $url = empty($folderName) ?  "/me/drive/root:/".$filename.":/content" : "/me/drive/root:/{$folderName}/" . $filename . ":/content";
        $response = $this->getGraph()
            ->createRequest("PUT", $url)
            ->attachBody([
                "items" => [
                    "@odata.type" => "microsoft.graph.driveItemUploadableProperties",
                    "@microsoft.graph.conflictBehavior" => "rename",
                    "name" => $filename
                ]
            ])
            ->upload($filepath);
        $fileupload = $response->getBody();
        unlink($filepath);
        return $fileupload;
    }

    public function getWorksheetCellsRange($fileId) {
        $response = $this->getGraph()
            ->createRequest("GET", "/me/drive/items/{$fileId}/workbook/worksheets/Sheet1/usedRange(valuesOnly=true)?" . '$select=values')
            ->execute();
        return $response->getBody();
    }
}
