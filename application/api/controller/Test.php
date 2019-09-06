<?php
namespace app\api\controller;


use function Qiniu\base64_urlSafeEncode;

class Test extends Common
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        dump(config('chat.url'));
        $config_chat = config('chat.url');
        $config_chat_route = config('chat.route');
        dump($config_chat);
        dump($config_chat_route);
        //实例化Redis类
//        $redis = new \Redis();
//        //选择指定的redis数据库连接，默认端口号为6379
//        $con=$redis->connect('47.107.44.121', 6379);
//        dump($con);
//        dump($redis);

    }


    public function pay()
    {
//        new \app\common\service\third\Wechat();
    }

    public function sh()
    {
        $shell = "ls -la";
        echo "<pre>";
        system($shell, $status);
        echo "</pre>";
        //注意shell命令的执行结果和执行返回的状态值的对应关系
        $shell = "<font color='red'>$shell</font>";
        if( $status ){
            echo "shell命令{$shell}执行失败";
        } else {
            echo "shell命令{$shell}成功执行";
        }
    }


    public function qiniu()
    {
        dump(\Qiniu\base64_urlSafeEncode('qbucket:qkey'));
    }

    public function down()
    {
        $audio = input('audio');
        dump(save_music($audio));
    }
}