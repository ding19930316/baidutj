<?php

if (!function_exists('baidu_config')) {
    function baidu_config()
    {
        return require_once(__DIR__ . '/config.php');
    }
}
