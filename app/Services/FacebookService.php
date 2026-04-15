<?php 

namespace App\Services;

use Facebook\Authentication\AccessToken;

class FacebookService {

    private $facebook;
    private $accessToken;

    function __construct() {
        $this->facebook = new \Facebook\Facebook([
            'app_id' => $this->getClientId(),
            'app_secret' => $this->getClientSecrect(),
            'default_graph_version' => 'v17.0' // 'v2.10',
        ]);
    }

    public function getRedirectUrl() {
        return $_ENV['FACEBOOK_REDIRECT_URI'];
    }

    public function getClientId() {
        return $_ENV['FACEBOOK_CLIENT_ID'];
    }

    public function getClientSecrect() {
        return $_ENV['FACEBOOK_CLIENT_SECRET'];
    }

    public function getFacebook() {
        return $this->facebook;
    }

    public function setAccessToken($token) {
        $token = json_decode($token);
        $value = $token->value;
        $time = strtotime($token->expiresAt->date);
        $this->accessToken = new AccessToken($accessToken, $time);
        $this->facebook->setDefaultAccessToken($this->accessToken);
    }

    public function getAccessToken() {
        return $this->accessToken;
    }

    public function getRedirectLogin() {
        $helper = $this->getFacebook()->getRedirectLoginHelper();
        $permissions = [
            'email',
            'whatsapp_business_messaging',
            'whatsapp_business_management',

            'instagram_manage_comments',
            'instagram_basic',

            'pages_messaging'
        ]; // Optional permissions
        return $helper->getLoginUrl($this->getRedirectUrl(), $permissions);
    }

    public function getAccessTokenByCode($query) {
        $helper = $this->getFacebook()->getRedirectLoginHelper();
        $helper->getPersistentDataHandler()->set('code', $query['code']);
        $helper->getPersistentDataHandler()->set('state', $query['state']);
        return $helper->getAccessToken($this->getRedirectUrl());
        // $_SESSION['fb_access_token'] = (string) $accessToken;
        // $appCreds['default_access_token'] = $_SESSION['fb_access_token'];
    
    }

    public function me() {
        // return $this->getFacebook()->get("/me?fields=name,email");
        $result = $this->getFacebook()->get("/me?fields=id,name,email,first_name,last_name,picture");
        return $result->getGraphUser()->asArray();
    }

}