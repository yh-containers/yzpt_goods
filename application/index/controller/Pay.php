<?php
namespace app\index\controller;


class Pay extends Common
{
    public function __construct()
    {
        parent::__construct();
    }

    public function info()
    {
        $mode = input('mode','wechat','trim');
        $order_id = input('order_id',0,'intval');
        if($mode=='wechat'){
            $pay =new \app\common\service\third\Wechat();
        }elseif($mode=='alipay'){
            $pay =new \app\common\service\third\Alipay();
        }
        //查询订单
        $model = \app\common\model\Order::get($order_id);
        if(empty($model)){
            exception('订单错误:id');
        }

        $result=$pay->appPay($model);
        dump($result);
    }

    public function notify()
    {
        $mode = input('mode','wechat','trim');
        if($mode=='wechat'){
            \app\common\service\third\Wechat::notify();
        }elseif($mode=='alipay'){

        }
    }

}