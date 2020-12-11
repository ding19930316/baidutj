<?php

namespace Cding\Baidutj;

class Auth
{
    private $config;

    private $headers;

    public $ucid = null;

    public $st = null;

    public function __construct($config)
    {
        $this->config = $config;
        $this->headers = [
            'UUID: ' . $this->uuid,
            'account_type: ' . $this->account_type,
            'Content-Type:  data/gzencode and rsa public encrypt;charset=UTF-8'
        ];

        return $this->login();
    }

    public function __get($name)
    {
        return isset($this->config[$name]) ? $this->config[$name] : null;
    }

    /**
     * @param $data
     * @return null
     */
    private function pubEncrypt($data)
    {
        if (!is_string($data)) {
            return null;
        }
        $ret = openssl_public_encrypt($data, $encrypted, $this->public_key);
        if ($ret) {
            return $encrypted;
        } else {
            return null;
        }
    }

    /**
     * @param $data
     * @return string
     *
     * generate post data
     */
    private function genPostData($data)
    {
        $gzData = gzencode(json_encode($data), 9);
        for ($index = 0, $enData = ''; $index < strlen($gzData); $index += 117) {
            $gzPackData = substr($gzData, $index, 117);
            $enData .= $this->pubEncrypt($gzPackData);
        }
        return $enData;
    }

    private function post($url, $data)
    {
        $data = $this->genPostData($data);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $tmpInfo = curl_exec($curl);
        if (curl_errno($curl)) {
        }
        curl_close($curl);

        $res['code'] = ord($tmpInfo[0]) * 64 + ord($tmpInfo[1]);

        if ($res['code'] === 0) {
            $res['data'] = substr($tmpInfo, 8);
        }

        return $res;
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

        $res = $this->post($this->login_url, $preLoginData);

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

    protected function login()
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
        $res = $this->post($this->login_url, $loginData);

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
        $res = $this->post($this->login_url, $logoutData);

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