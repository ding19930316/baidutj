<?php
namespace Cding\Baidutj;
class Post {

    const PUBLIC_KEY = <<<publicKey
    -----BEGIN PUBLIC KEY-----
    MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDHn/hfvTLRXViBXTmBhNYEIJeG
    GGDkmrYBxCRelriLEYEcrwWrzp0au9nEISpjMlXeEW4+T82bCM22+JUXZpIga5qd
    BrPkjU08Ktf5n7Nsd7n9ZeI0YoAKCub3ulVExcxGeS3RVxFai9ozERlavpoTOdUz
    EH6YWHP4reFfpMpLzwIDAQAB
    -----END PUBLIC KEY-----
    publicKey;

    private $headers;

    static function hello($msg="dsa")
    {
        echo $msg;
    }

    public function __construct($config)
    {
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

    public function post($url, $data)
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

        /**
     * @param $data
     * @return null
     */
    public function pubEncrypt($data)
    {
        if (!is_string($data)) {
            return null;
        }
        $ret = openssl_public_encrypt($data, $encrypted, self::PUBLIC_KEY);
        if ($ret) {
            return $encrypted;
        } else {
            return null;
        }
    }
}