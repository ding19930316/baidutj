<?php

namespace Cding\Baidutj;

class Services
{
    protected $auth;

    protected $api;

    public function __construct()
    {
        $this->auth = new Auth(baidu_config());
        $this->api = new Api(array_merge(baidu_config(), $this->auth));
    }

    public function logout()
    {
        return $this->auth->logout();
    }

    public function getSiteList()
    {
        return $this->api->getSiteList();
    }

    public function getData($params)
    {
        return $this->api->getData($params);
    }

    // 下面基于getData扩展一些方法
}
