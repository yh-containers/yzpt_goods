<?php
namespace app\index\controller;


class Test extends Common
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {

        dump(str_replace(":","",strtolower('03:32:38:B6:8C:A0:A6:1C:25:27:A8:D5:F8:AC:BE:20:6E:CD:78:12')));

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
//        new \app\common\service\third\Alipay();
//        $access_token = \app\common\service\third\Wechat::accessToken();
//        dump($access_token);
//        $wechat = new \app\common\service\third\Wechat();
//        $wechat->nativePay(\app\common\model\Order::get(3));
    }

    public function phpinfo()
    {
        phpinfo();
    }
}