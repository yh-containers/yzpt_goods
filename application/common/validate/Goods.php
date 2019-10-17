<?php
/**
 * Date: 2019/8/3
 * Time: 12:09
 */
namespace app\common\validate;

use think\Validate;

class Goods extends Validate
{
    //验证规则
    protected $rule = [
        'cate_id'        =>  'require',
        'goods_name'      =>  'require',
        'goods_image'     =>  'require',
        'image_arr'       =>  'require',
        'price'       =>  'require',
        'original_price'       =>  'require',
        'stock'       =>  'require',
        'content'       =>  'require',
        'norm_brief'       =>  'require',
    ];

    //提示信息
    protected $message = [
        'cate_id.require'      => '请选择分类',
        'goods_name.require'    => '商品名称必须输入',
        'price.require'    => '商品价格必须输入',
        'original_price.require'    => '商品原价必须输入',
        'stock.require'    => '商品库存必须输入',
        'content.require'    => '商品详情必须输入',
        'norm_brief.require'    => '商品规格介绍必须输入',
        'goods_image.require'   => '商品图片必须上传',
        'image_arr.require'   => '商品图片必须上传',
        //'state.require'     => '选择状态',
    ];

}