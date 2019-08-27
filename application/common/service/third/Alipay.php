<?php
namespace app\common\service\third;

class Alipay implements IPay
{

    protected $aop;

    public function __construct()
    {
//        dump(\think\facade\Env::get('vendor_path'));
        //引入第三方资源
        require_once \think\facade\Env::get('vendor_path').'alipay/AopSdk.php';
        $aop = new \AopClient();

        //正式环境
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = config('third.alipay.app_id');
        $aop->rsaPrivateKey = config('third.alipay.rsa');
        $aop->alipayrsaPublicKey=config('third.alipay.pk');

        //测试环境
//        $aop->gatewayUrl = 'https://openapi.alipaydev.com/gateway.do';
//        $aop->appId = '2016091700532079';
//        $aop->rsaPrivateKey = 'MIIEowIBAAKCAQEAmLsL21wfP+vOLdUHOWUMmelxiZV3Brh7SFkJIdg4pegC8kYLV5jGAAhODfzwQBd8jB7lLplcBWJGopKZvndWX5K+ExOYfnRx48WY4toRwSTN3gTVGB9UhKOn+EQnenZE2zNPbaHsKRM3vGjAKPrFeWmRH2gx++9XWmro4GXLwXzRWQ/XbYiTPcOkY5BtUudJM6Cl1keoHIYgor6njqmNDRWeYFkXYsP8ApZZ3fW3WdXPlGIEgPZhcWMzL+61lVxCv7LWLwK6eBjSelW9UWRLGNlNezBVps/AO21QsRb7rFcxW5PhA9Vq1PctS3Nb5JiB8IoJOekUUAW33i/KWCpEcwIDAQABAoIBADcyWj2j1HNggDoWJC888snpESxdBRA7uepSqzc9EnP7Hp16bPzybJR0a+koQZeYJV3qiH9H03bqpoZ4nvGz5VZTDTiNu23wHXzo27EYiJQZ/gDAFFdMc1ogX0MdNntOpOHncEw52cSaemkwHkpxHs8bNOR53p6jSBmYS6NVhsbdBrXyjdPddywBjq0FjkgkjPuQ5X/gXnUhe40EccQ+1pK3b1jr1yUMF1rAW4tJtGM3YO+xvtLVgalU6hK89g6g16kZGkEg50tgfCA5tN6Lv9bQJCtVtcUsgQDuxiiZpcuED4fsPTaIfQ1DJdu/PpYD5nYhhTicqilptJ4meaNql9ECgYEA/owFw6vC8YVX4f7ITqWZkZgnRYtnx2F+6+wNzkTP41cRLzqVqqoxu1uBqpO27oYl0zriYbUdx7FRwIMc+QDVFs0DRvW8baUOhLhGRyvi95U05j+Ohhi7Mjw2Ps9GyogCXjRKRSK7kYt5rKcqGZ95dQvwoWfLlam6ac2Gvf3uJnsCgYEAmZo8heP0yfdcpwfx2+0/p2o1zO5zxHBzL8IHbWw2/x0vRIaQAf+wWZbyYBE9BrTiuun6yF8LO7I6HbaGZIGEU26j+s8v6/+guHlJ05glAaT0TNKTERljcbeUHpbAB2iObEcvnOiYMbQaeTTkcbHs7RKDmddZB9HdYcKAvBbqtGkCgYBmicFIsUg2QqDESP4nsE3MeJ5ZRW7owj1+i/iDvvR/f/NMMy1XMngWISZ6sEZgj2ltTasj8PGuH5/vDOH+7HbqWGuZiiP9hx/yFsk4olUrps9IcRHYst21vsubQaQisedCS44fi35DgwvgoPY0nCkxHT4xxr4b6+NL+57rqf7lQQKBgE8n0Zq5/5L4+3FEQdoxKAVxUWpbU6New61P0x4Lj0fm1U39/kZZapqwlBT3rThAjTr/ivIpMJPB4/sd7aHrsLKCKNT+Yla+9Cc8sdPt6twvEopoVcuRBtM6ZIVi8HCg7AxWnu3AW3X5t07Q+AyzQUJmsRTdig7ikrBnWIoVUTxJAoGBAIJEXNrF+9/24T1tBOO6w9SBJIhB8kDdFjtrPAV364iyCuMXx/U2pLh59qCqp+ZNjoGLF3leF8oNsvDPdzvOZHRhebrmGl9LwxygRXSj3EOjB9l/ieFdBI5Yg+G0WoiqIVePLIH9ydBD8i4NyiqaNsz4x0m85SUz6aFtms+2cOmL';
//        $aop->alipayrsaPublicKey='MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA0sIg4SBvImSJWa/bHcgTOl211ZFZ3kjORBZHs6wTL89gZss9zZESRK91Ti4EkXztU08MuWuHkNlbxL2VVHVyIkS9CoHcY5Gwht8pfhFQmqmbbEHhrqmigcbGAnKpEXhW99IBIM/X7hmNvHi5eaQGKTC2DfKwdqRtuSILnVzmV26T/zbu7EDdQzedD6YyUkdo97unVKpLgpA4QWg++YUKBmG7pemcmtFQdRbCPt9AoroD907aBo5WIv5+ii+Y8eXKwlMOdCjRFh3SEFzu62A2UzVzVHlEbTLaPJEAEM1EHMjMXCnsdGivYPBvmYyHb7aN4U84npIum2JETe0WAHQijwIDAQAB';


        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset='utf-8';
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
        $request = new \AlipayTradePrecreateRequest ();

        $this->handleOrderInput($model, $request);

        $result = $this->aop->execute ( $request);

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
            return [
                'out_trade_no'=>$result->$responseNode->out_trade_no,
                'qr_code'=>$result->$responseNode->qr_code,
            ];
        } else {
            exception($result->$responseNode->sub_msg);
        }

    }

    //支付中心
    public function webPay(\think\Model $model)
    {
        $request = new \AlipayTradePagePayRequest ();
        $this->handleOrderInput($model, $request,['product_code'=>'FAST_INSTANT_TRADE_PAY']);
        $result = $this->aop->pageExecute ( $request);
//        dump($result);exit;
        return $result;

    }


    protected function handleOrderInput($model,$request,array $mer_content = [])
    {
        $pay_info = $model->getOrderPayInfo('alipay');



        $content = json_encode(array_merge([
            'out_trade_no'=>$pay_info['no'],
            'total_amount'=>$pay_info['pay_money'],
            'subject'=>$pay_info['body'],
            'body'=>'订单支付',
            'timeout_express'=>($pay_info['expire_time']/60).'m',
        ],$mer_content),JSON_UNESCAPED_UNICODE);
        //设置通知地址
        $request->setNotifyUrl($pay_info['notify_url']);
        //设置支付内容
        $request->setBizContent($content);

    }



    public static function notify()
    {
        $pay = new self();
        $data = $_POST;
        unset($data['mode']);
        $flag = $pay->aop->rsaCheckV1($data, NULL, $pay->aop->signType);
        //验证成功
        if($flag){
            if($_POST['trade_status'] == 'TRADE_SUCCESS') { //交易完成
                \app\common\model\Order::handleNotify($_POST["out_trade_no"],$_POST);

            }else {
                trace('支付宝订单交易失败,相关数据:'.json_encode($_POST));
            }
        }else{

            trace('支付宝签验失败!相关数据:'.json_encode($_POST));
        }
        return 'success';
    }


}