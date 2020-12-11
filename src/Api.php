<?php

namespace Cding\Baidutj;

class Api
{
    private $config;

    private $headers;

    private $data_header;

    public function __construct($config)
    {
        $this->config = $config;
        $this->headers = [
            'UUID: ' . $this->uuid,
            'USERID: ' . $this->ucid,
            'Content-Type:  data/json;charset=UTF-8'
        ];

        $this->data_header = [
            'username' => $this->username,
            'password' => $this->st,
            'token' => $this->token,
            'account_type' => $this->account_type
        ];
    }

    public function __get($name)
    {
        return isset($this->config[$name]) ? $this->config[$name] : null;
    }

    private function genPostData($data)
    {
        return json_encode($data);
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

        $tmpRet = curl_exec($curl);
        if (curl_errno($curl)) {
        }
        curl_close($curl);
        $tmpArray = json_decode($tmpRet, true);
        if (isset($tmpArray['header']) && isset($tmpArray['body'])) {
            return [
                'header' => $tmpArray['header'],
                'body' => $tmpArray['body'],
                'raw' => $tmpRet,
            ];
        } else {
        }
        return null;
    }

    public function getSiteList()
    {
        $data = [
            'header' => $this->data_header,
            'body' => null,
        ];
        return $this->post($this->api_url . '/getSiteList', $data);
    }

    /**
     * @param array $parameters
     * @return array
     */
    public function getData($parameters)
    {
        $data = [
            'header' => $this->data_header,
            'body' => $parameters,
        ];
        return $this->post($this->api_url . '/getData', $data);
    }
}
