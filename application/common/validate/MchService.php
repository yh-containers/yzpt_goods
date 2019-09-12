<?php
namespace app\common\validate;

use think\Validate;

class MchService extends Validate
{
    //验证规则
    protected $rule = [
        'name'      =>  'require',
        'img'       =>  'require',
    ];
    //提示信息
    protected $message = [
        'name.require'      => '名称必须输入',
        'img.require'       => '请上传封面图图',
    ];

}