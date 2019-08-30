<?php
namespace app\common\service\third;

class QQ
{

    public static function config($app=null,$key=null)
    {
        $config = config('third.qq');
        if(is_null($app) && !isset($config[$app])){
            return $config;
        }
        $config = $config[$app];
        if(is_null($key) && !isset($config[$key])){
            return $config;
        }
        return $config[$key];
    }
    //获取转跳信息

    //  https://graph.qq.com/oauth2.0/authorize

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
            'redirect_uri' => ''
        ];
        $result = \app\common\HttpCurl::req('https://graph.qq.com/oauth2.0/token',$param);
        $info = json_decode($result,true);
        if(empty($info)){
            exception('授权信息为空');
        }else{
            if(!empty($info['errcode'])){
                //报错
                exception('授权异常:'.$info['errmsg'].' 错误代码:'.$info['errcode']);
            }else{
                return $info;
            }
        }
    }

    /**
     * 获取accessToken获取用户资料
     * @param string $access_token 用户授权凭证
     * @throws
     * @return array
     * */
    public static function actToUserInfo($access_token, $openid,$mode)
    {
        //获取openid



        $param = [
            'access_token' => $access_token,
            'oauth_consumer_key' => self::config($mode,'app_id'),
            'openid' => $openid,
        ];
        $result = \app\common\HttpCurl::req('https://graph.qq.com/user/get_user_info',$param);
        $info = json_decode($result,true);
        if(empty($info)){
            exception('获取用户信息异常');
        }else{
            if(!empty($info['errcode'])){
                //报错
                exception('异常:'.$info['errmsg'].' 错误代码:'.$info['errcode']);
            }else{
                return $info;
            }
        }
    }
}