<?php 

namespace App\Services;

use Aws\S3\S3Client; 
use Aws\Exception\AwsException;

class S3AwsService {

    private $s3Client;

    function __construct() {
        $this->s3Client = new S3Client([
            'version' => '2006-03-01', 
            'region' => 'us-west-2', 
            'credentials' => [ 
                'key' => $this->getAccessKey(), 
                'secret' => $this->getSecrectKey(), 
            ], 
        ]);
    }

    public function getBucket() {
        return $_ENV['AWS_BUCKET_S3'];
    }

    public function getAccessKey() {
        return $_ENV['AWS_ACCESS_S3'];
    }

    public function getSecrectKey() {
        return $_ENV['AWS_SECRET_S3'];
    }

    public static function getArrBuckets() {
        return [
            "plataforma-educativa",
            "owlgroup",
            "owlgroupvideos",
            "plataforma-proeducative",
            "plataforma-proeducative-videos"
        ];
    }

    public function getUriFile($uri) {
        $bucket = $this->getBucket() ? $this->getBucket() : "plataforma-proeducative";
        switch ($bucket) {
            case "plataforma-proeducative-videos":
                $dns = "https://s3-us-west-2.amazonaws.com/plataforma-proeducative-videos/" . $uri;
                break;
            case "plataforma-proeducative":
                $dns = "https://s3-us-west-2.amazonaws.com/plataforma-proeducative/" . $uri;
                break;
            case "plataforma-educativa":
                $dns = "https://s3-us-west-2.amazonaws.com/plataforma-educativa/" . $uri;
                break;
            case "owlgroup":
                $dns = "https://d2mv2wiw5k8g3l.cloudfront.net/" . $uri;
                break;
            case "owlgroupvideos":
                $dns = "https://d13jgz3expz2zs.cloudfront.net/" . $uri;
                break;
        }
        if ($dns) {
            return $dns;
        } else {
            return false;
        }
    }

    public function putFile($file, $uri) {
        return $this->s3Client->putObject([
            'Bucket' => $this->getBucket(), 
            'Key' => $uri, 
            'Body' => $file, 
            'ACL' => 'public-read'
        ]);
    }

}