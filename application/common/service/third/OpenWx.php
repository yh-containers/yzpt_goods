<?php
namespace app\common\service\third;

class OpenWx
{



    protected static $config_instance;



    /**
     * 获取微信配置单例
     * */
    public static function configInstance()
    {
        $instance = self::$config_instance;
        if(empty($instance)){
            $instance = new WechatConfg();
        }
        return $instance;
    }

    /**
     * 获取code换access_token
     * @param string $code 用户换取access_token的code
     * @throws
     * @return array
     * */
    public static function codeToAct($code)
    {
        $wechat = self::configInstance();
        //换取微信信息
        $param = [
            'appid' => $wechat->config->GetAppId(),
            'secret' => $wechat->config->GetAppSecret(),
            'code' => $code,
            'grant_type' => 'authorization_code',
        ];
        $result = \app\common\HttpCurl::req('https://api.weixin.qq.com/sns/oauth2/access_token',$param);
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
    public static function actToUserInfo($access_token, $openid)
    {
        $param = [
            'access_token' => $access_token,
            'openid' => $openid,
        ];
        $result = \app\common\HttpCurl::req('https://api.weixin.qq.com/sns/userinfo',$param);
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