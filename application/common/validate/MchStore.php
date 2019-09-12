<?php
namespace app\common\validate;

use think\Validate;

class MchStore extends Validate
{
    //验证规则
    protected $rule = [
        'name'      =>  'require',
        'up_name'   =>  'require',
        'up_phone'  =>  'require|validPhone',
        'img'       =>  'require',
        'star'      =>  'egt:0|elt:5',
    ];
    //提示信息
    protected $message = [
        'name.require'      => '店铺名必须输入',
        'up_name.require'   => '联系人名必须输入',
        'up_phone.require'   => '联系人手机号必须输入',
        'img.require'       => '请上传店铺图',
        'star.egt'          => '评星不得低于:rule星',
        'star.elt'          => '评星不得高于:rule星',

    ];

    protected function validPhone($value,$rule,$data=[])
    {
        if(!validPhone($value)){
            return '请输入正确的号码';
        }
        return true;
    }
}