<?php 

namespace App\Services;

use Vimeo\Vimeo;

class VimeoService {

    private $vimeo;
    private $vimeo_folder;
    private $allowed_types = [ 'video/mp4', 'video/avi', 'video/mpeg', 'video/quicktime', 'video/x-ms-wmv'];

    public function __construct($vclient = null, $vsecret = null, $vtoken = null) {
        $vclient = $vclient ? $vclient : $_ENV['VIMEO_CLIENT'];
        $vsecret = $vsecret ? $vsecret : $_ENV['VIMEO_SECRET'];
        $vtoken  = $vtoken ? $vtoken : $_ENV['VIMEO_TOKEN'];
        $this->vimeo = new Vimeo($vclient, $vsecret, $vtoken);
        $this->vimeo_folder = $_ENV['VIMEO_FOLDER'];
    }

    public function getFolder() {
        return $this->vimeo_folder;
    }

    public function upload($filename, $file) {
      $response = array(['success' => false, 'message' => 'Error']);
      try {

        $filetype = $file->getClientMediaType();
        if (!in_array($filetype, $this->allowed_types)) {
            throw new \Exception('File type not allowed');
        }
        $params = [
            "name" => $filename,
            "description" => $filename,
            "privacy" => [
                "embed" => "public",
                "view" => "disable",
                "comments" => "nobody"
            ],
            "embed"=> [
                "logos" => [
                    "vimeo" => false
                ],
                "buttons" => [
                    "embed" => false,
                    "share" => false,
                    "watchlater" => false,
                    "like" => false,
                ],
                "title" => [
                    "owner" => "hide",
                    "name" => "hide",
                    "portrait" => "hide"
                ]
            ]
        ];
        $parent_folder = $this->vimeo_folder;
        if ($parent_folder) {
            $params['folder_uri'] = $parent_folder;
        }
        $tmpName = $file->getStream()->getMetadata('uri');
        $uri_upload = $this->vimeo->upload($tmpName, $params);
        if (!$uri_upload) {
          throw new \Exception('An error was encountered while uploading the video.');
        }

        $result = $this->vimeo->request($uri_upload);
        if ($result['status'] != 200) {
            throw new \Exception('No information was found with the video code: ' . $uri_upload);
        } 
   
        $response['success'] = true;
        $response['message'] = 'Your video finished upload';
        $response['data']  = $result['body'];
      } catch (\Exception $e) {
        $response['message'] = $e->getMessage();
      }
      return $response;
    }

}