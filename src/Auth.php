<?php
namespace Cding\Baidutj;

use Cding\Baidutj\Post;

class Auth {
    private $config;


    public $ucid = null;

    public $st = null;

    const LOGIN_URL = 'https://api.baidu.com/sem/common/HolmesLoginService';

    const PUBLIC_KEY = <<<publicKey
-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDHn/hfvTLRXViBXTmBhNYEIJeG
GGDkmrYBxCRelriLEYEcrwWrzp0au9nEISpjMlXeEW4+T82bCM22+JUXZpIga5qd
BrPkjU08Ktf5n7Nsd7n9ZeI0YoAKCub3ulVExcxGeS3RVxFai9ozERlavpoTOdUz
EH6YWHP4reFfpMpLzwIDAQAB
-----END PUBLIC KEY-----
publicKey;


    public function __construct($config)
    {
        $this->post = new Post($config);
        $this->config = $config;
        $this->headers = [
            'UUID: ' . $this->uuid,
            'account_type: ' . $this->account_type,
            'Content-Type:  data/gzencode and rsa public encrypt;charset=UTF-8'
        ];
    }

    public function __get($name)
    {
        return isset($this->config[$name]) ? $this->config[$name] : null;
    }

    private function post($url, $data)
    {
        return $this->post->post($url, $data);
    }

    private function preLogin()
    {
        $preLoginData = [
            'username' => $this->username,
            'token' => $this->token,
            'functionName' => 'preLogin',
            'uuid' => $this->uuid,
            'request' => [
                'osVersion' => 'windows',
                'deviceType' => 'pc',
                'clientVersion' => '1.0',
            ],
        ];

        $res = $this->post(self::LOGIN_URL, $preLoginData);

        if ($res['code'] === 0) {
            $retData = gzdecode($res['data']);
            $retArray = json_decode($retData, true);
            if (!isset($retArray['needAuthCode']) || $retArray['needAuthCode'] === true) {
                return false;
            } else if ($retArray['needAuthCode'] === false) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }

    }


    public function login()
    {
        $this->preLogin();

        $loginData = [
            'username' => $this->username,
            'token' => $this->token,
            'functionName' => 'doLogin',
            'uuid' => $this->uuid,
            'request' => [
                'password' => $this->password,
            ],
        ];
        $res = $this->post(self::LOGIN_URL, $loginData);

        if ($res['code'] === 0) {
            $retData = gzdecode($res['data']);
            $retArray = json_decode($retData, true);
            if (!isset($retArray['retcode']) || !isset($retArray['ucid']) || !isset($retArray['st'])) {
                return null;
            } else if ($retArray['retcode'] === 0) {
                $this->ucid = $retArray['ucid'];
                $this->st = $retArray['st'];
                return [
                    'ucid' => $retArray['ucid'],
                    'st' => $retArray['st'],
                ];
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    public function logout()
    {
        if ($this->ucid == null && $this->st == null)
            return null;
        $logoutData = [
            'username' => $this->username,
            'token' => $this->token,
            'functionName' => 'doLogout',
            'uuid' => $this->uuid,
            'request' => [
                'ucid' => $this->ucid,
                'st' => $this->st,
            ],
        ];
        $res = $this->post(self::LOGIN_URL, $logoutData);

        if ($res['code'] === 0) {
            $retData = gzdecode($res['data']);
            $retArray = json_decode($retData, true);
            if (!isset($retArray['retcode'])) {
                return false;
            } else if ($retArray['retcode'] === 0) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}