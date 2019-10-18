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
        if($model['pay_money'] <= 0){
            \app\common\model\Order::where(['id'=>$order_id])->update(['step_flow'=>1,'status'=>1,'pay_time'=>time()]);
            print_r('<script>alert("订单金额为0，成功");window.location.href="/"</script>');
            //header('Location:/');
            die;
        }
        $code = input('code');
        if($code){
            $auth_info = \app\common\service\third\OpenWx::codeToAct('mobile',$code);
            session('openid',$auth_info['openid']);
        }
        if($model['pay_way'] == 1){
            $mode = 'alipay';
            if(isMobile()){
                $pay_way = 'wapPay';
            }else{
                $pay_way = 'webPay';
            }
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
            if(strpos($_SERVER['HTTP_USER_AGENT'],'MicroMessenger')){
                if(empty(session('openid'))){
                    $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.\app\common\service\third\OpenWx::config('mobile','app_id').'&redirect_uri='.rawurlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].$_SERVER['QUERY_STRING']).'&response_type=code&scope=snsapi_userinfo&state=#wechat_redirect';
                    //print_r($url);die;url('pay/info',['mode'=>'wechat','order_id'=>$order_id],false,true)
                    header('Location:'.$url);exit;
                }
                $html = $pay->jssdkPay($model,session('openid'));
            }else{
                $html = $pay->$pay_way($model);
            }
            if($mode=='wechat'){
                $redirect_url = urlencode('http://'.$_SERVER['SERVER_NAME'].'/order/redurl?oid='.$order_id);
                if(isMobile() && !strpos($_SERVER['HTTP_USER_AGENT'],'MicroMessenger')){
                    //$this->redirect($html['mweb_url'].'&redirect_url='.$redirect_url);
                    header('Location:'.$html['mweb_url'].'&redirect_url='.$redirect_url);
                }
                if(strpos($_SERVER['HTTP_USER_AGENT'],'MicroMessenger')){
                    return view('order/wechatpay', ['code_url' => $html]);
                }else {
                    return view('order/pay_order', ['order' => $model, 'code_url' => $html, 'payinfo' => $html, 'redirect_url' => $redirect_url]);
                }
            }
            //elseif($mode=='alipay' && isMobile()){//

            //}
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