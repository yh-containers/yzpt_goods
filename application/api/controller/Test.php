<?php
namespace app\api\controller;


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
        new \app\common\service\third\Wechat();
    }

}