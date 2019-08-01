<?php
/**
 * Date: 2019/8/1
 * Time: 18:12
 */
namespace app\common\validate;

use think\Validate;

class GoodsCategory extends Validate
{
    //验证规则
    protected $rule = [
        'pid'        =>  'require',
        'cate_name'      =>  'require',
        'image'     =>  'require',
        'state'       =>  'require',
    ];

    //提示信息
    protected $message = [
        'pid.require'      => '请选择分类',
        'cate_name.require'    => '分类名称必须输入',
        'image.require'   => '图片必须上传',
        'state.require'     => '选择状态',
    ];

}