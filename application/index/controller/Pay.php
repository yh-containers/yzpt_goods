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
        $mode = input('mode','alipay','trim');
        $pay_way = input('pay_way','nativePay','trim');
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
        try{
            $result=$pay->$pay_way($model);
        }catch (\Exception $e){
            dump($e->getMessage());
        }
        dump($result);
    }

    public function notify()
    {
        \think\facade\Log::write("wechat-notify:" .json_encode(input()),'-------input-----');
        \think\facade\Log::write("wechat-notify:" .file_get_contents("php://input"),'-----file_get_contents-----');

        $mode = input('mode','wechat','trim');
        if($mode=='wechat'){
            \app\common\service\third\Wechat::notify();
        }elseif($mode=='alipay'){
            return \app\common\service\third\Alipay::notify();
        }
    }

    public function webpay()
    {
        $mode = input('mode','alipay','trim');
        $pay_way = 'webPay';
        $order_id = input('order_id',0,'intval');

        $pay =new \app\common\service\third\Alipay();
        //查询订单
        $model = \app\common\model\Order::get($order_id);
        $html = $pay->$pay_way($model);
        return $html;

    }
}