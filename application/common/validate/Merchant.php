<?php
namespace app\common\validate;

use think\Validate;

class Merchant extends Validate
{
    //验证规则
    protected $rule = [
        'name'      =>  'require',
        'up_name'   =>  'require',
        'up_phone'  =>  'require|validPhone',
    ];
    //提示信息
    protected $message = [
        'name.require'      => '店铺名必须输入',
        'up_name.require'   => '联系人名必须输入',
        'up_phone.require'   => '联系人手机号必须输入',

    ];

    protected function validPhone($value,$rule,$data=[])
    {
        if(!validPhone($value)){
            return '请输入正确的号码';
        }
        return true;
    }
}