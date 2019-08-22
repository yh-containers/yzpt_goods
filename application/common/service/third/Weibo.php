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

    /**
     * 获取code换access_token
     * @param string $mode 平台 app/web
     * @param string $code 用户换取access_token的code
     * @throws
     * @return array
     * */
    public static function codeToAct($mode,$code)
    {
        //换取微信信息
        $param = [
            'client_id' => self::config($mode,'app_id'),
            'client_secret' => self::config($mode,'app_secret'),
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri'=>request()->domain(),
        ];
//        dump($param);exit;
        $result = \app\common\HttpCurl::req('https://api.weibo.com/oauth2/access_token',$param,'POST');
        $info = json_decode($result,true);
        if(!empty($info['error_code'])){
            //报错
            exception('授权异常:'.$info['error'].' 错误代码:'.$info['error_code']);
        }else{
//            dump($info);
//            $user_info = self::userInfo($info['uid'],$info['access_token']);

            return $info;
        }
    }

    /**
     * 获取用户信息
     * */
    public static function actToUserInfo($access_token,$uid)
    {
        //换取微信信息
        $param = [
            'access_token' => $access_token,
            'uid' => $uid,
        ];
        $result = \app\common\HttpCurl::req('https://api.weibo.com/2/users/show.json',$param);
        $info = json_decode($result,true);
        if(!empty($info['errcode'])){
            //报错
            exception('授权异常:'.$info['errmsg'].' 错误代码:'.$info['errcode']);
        }else{
            return $info;
        }
    }
}