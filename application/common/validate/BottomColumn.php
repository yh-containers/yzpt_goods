<?php
/**
 * Date: 2019/8/29
 * Time: 15:10
 */
namespace app\common\validate;

use think\Validate;

class BottomColumn extends Validate
{
    //验证规则
    protected $rule = [
//        'pid'        =>  'require',
        'name'      =>  'require'
    ];

    //提示信息
    protected $message = [
//        'pid.require'      => '请选择分类',
        'name.require'    => '商品名称必须输入'
    ];

}