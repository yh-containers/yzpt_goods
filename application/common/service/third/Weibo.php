<?php
namespace app\common\service\third;

class Weibo
{
    public static function config($app=null,$key=null)
    {
        $config = config('third.weibo');
        if(is_null($app) && !isset($config[$app])){
            return $config;
        }
        $config = $config[$app];
        if(is_null($key) && !isset($config[$key])){
            return $config;
        }
        return $config[$key];
    }


}