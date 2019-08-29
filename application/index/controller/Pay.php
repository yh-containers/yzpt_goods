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
        $order_id = input('order_id',0,'intval');
        $model = \app\common\model\Order::get($order_id);
        if($model['pay_way'] == 1){
            $mode = 'alipay';
            if(isMobile()){
                $pay_way = 'appPay';
            }else{
                $pay_way = 'webPay';
            }
            $pay_way = 'webPay';
        }else{
            $mode = 'wechat';
            if(isMobile()){
                $pay_way = 'h5Pay';
            }else{
                $pay_way = 'nativePay';
            }
        }
        //$mode = input('mode','alipay','trim');
        //$pay_way = input('pay_way','webPay','trim');
        if($mode=='wechat'){
            $pay =new \app\common\service\third\Wechat();
        }elseif($mode=='alipay'){
            $pay =new \app\common\service\third\Alipay();
        }
        //查询订单

        if(empty($model)){
            exception('订单错误:id');
        }
        try{
            $html = $pay->$pay_way($model);
            if($mode=='wechat'){
                //print_r($html['mweb_url']);
                //&redirect_url=https%3A%2F%2Fwww.wechatpay.com.cn
                $redirect_url = urlencode('http://'.$_SERVER['SERVER_NAME'].'/order/redurl?oid='.$order_id);
                return view('order/pay_order',['order'=>$model,'code_url'=>$html,'payinfo'=>$html,'redirect_url'=>$redirect_url]);
            }
            return $html;
        }catch (\Exception $e){
            dump($e->getMessage());
        }
        //return $html;
        //print_r($htm3l);
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