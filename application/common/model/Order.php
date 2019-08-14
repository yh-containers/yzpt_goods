<?php
/**
 * Date: 2019/8/12
 * Time: 16:51
 */
namespace app\common\model;
use think\model\concern\SoftDelete;
class Order extends BaseModel
{
    use SoftDelete;
    public static $fields_step = [
        ['name'=>'支付流程','field'=>'status'],
        ['name'=>'发货流程','field'=>'is_send'],
        ['name'=>'收货流程','field'=>'is_receive'],
        ['name'=>'交易已完成','field'=>'status']
    ];
    protected $table = 'gd_order';
    public function ownGoods()
    {
        return $this->hasMany('OrderGoods','oid')->field('id,oid,name,img')->order('id asc');
    }
    public function getOrderSn(){
        return date('YmdHis').mt_rand(1000,9999).self::count();
    }
}