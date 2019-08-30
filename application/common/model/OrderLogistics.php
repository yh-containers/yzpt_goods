<?php
/**
 * Date: 2019/8/29
 * Time: 18:56
 */
namespace app\common\model;
use think\model\concern\SoftDelete;
class OrderLogistics extends BaseModel
{
    use SoftDelete;
    protected $table = 'gd_order_logistics';
}