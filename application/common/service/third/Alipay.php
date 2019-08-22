<?php
namespace app\common\service\third;

class Alipay implements IPay
{

    protected $aop;

    public function __construct()
    {
//        dump(\think\facade\Env::get('vendor_path'));
        //引入第三方资源
        require_once \think\facade\Env::get('vendor_path').'alipay\AopSdk.php';
        $aop = new \AopClient();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = config('third.alipay.app_id');
        $aop->rsaPrivateKey = config('third.alipay.rsa');
        $aop->alipayrsaPublicKey=config('third.alipay.pk');
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset='UTF-8';
        $aop->format='json';

        $this->aop= $aop;
    }

    public function appPay(\think\Model $model)
    {
        $request = new \AlipayTradeAppPayRequest();
        $this->handleOrderInput($model,$request);

        $result = $this->aop->sdkExecute ($request);
        return $result;
    }

    //二维码支付
    public function nativePay(\think\Model $model)
    {
        $request = new \AlipayTradeCreateRequest();

        $this->handleOrderInput($model, $request);

        $result = $this->aop->execute ( $request);

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
            echo "成功";
        } else {
            echo "失败";
        }

    }

    protected function handleOrderInput($model,$request,array $mer_content = [])
    {
        $pay_info = $model->getOrderPayInfo('alipay');



        $content = array_merge([
            'out_trade_no'=>$pay_info['no'],
            'total_amount'=>$pay_info['pay_money'],
            'subject'=>$pay_info['body'],
            'body'=>'订单支付',
            'timeout_express'=>($pay_info['expire_time']/60).'m',
        ],$mer_content);
        //设置通知地址
        $request->setNotifyUrl($pay_info['notify_url']);
        //设置支付内容
        $request->setBizContent(json_encode($content,JSON_UNESCAPED_UNICODE));
    }



    public static function notify()
    {
        $pay = new self();
        $flag = $pay->aop->rsaCheckV1($_POST, NULL, $pay->aop->signType);

        //验证成功
        if($flag){
            if($_POST['trade_status'] == 'TRADE_SUCCESS') { //交易完成
                \app\common\model\Order::handleNotify($_POST["out_trade_no"],$_POST);
            }else {
                \Think\Log::Write('支付宝订单交易失败,相关数据:'.json_encode($_POST));
            }
        }else{
            \Think\Log::Write('支付宝签验失败!相关数据:'.json_encode($_POST));
        }
    }


}