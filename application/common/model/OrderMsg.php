<?php
/**
 * Date: 2019/9/3
 * Time: 14:50
 */
namespace app\common\model;
class OrderMsg extends BaseModel
{
    protected $table = 'gd_order_msg';
    public function ownOrder()
    {
        return $this->hasOne('Order','oid')->order('id asc');
    }
}