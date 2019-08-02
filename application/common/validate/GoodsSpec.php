<?php
/**
 * Date: 2019/8/2
 * Time: 9:12
 */
namespace app\common\validate;

use think\Validate;

class GoodsSpec extends Validate
{
    //验证规则
    protected $rule = [
        'pid'        =>  'require',
        'spec_name'      =>  'require',
    ];

    //提示信息
    protected $message = [
        'pid.require'      => '请选择所属',
        'spec_name.require'    => '规格名称必须输入',
    ];

}