<?php
namespace app\common\service\third;

class Alipay
{

    public function __construct()
    {
//        dump(\think\facade\Env::get('vendor_path'));
        //引入第三方资源
        require_once \think\facade\Env::get('vendor_path').'alipay\AopSdk.php';

    }



}