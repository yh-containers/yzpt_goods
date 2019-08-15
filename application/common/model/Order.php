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
    public static $fields_mobile_step = [
        [
            'name'=>'待支付',
            'handle'=>'<a href="javascript:;" class="cancel">取消订单</a><a href="/order/payorder/order_id/{order_id}" class="red">立即付款</a>'
        ],
        [
            'name'=>'待发货',
            'handle'=>'<a href="javascript:;" class="cancel">取消订单</a><a href="javascript:;" class="red">提醒发货</a>'
        ],
        [
            'name'=>'待收货',
            'handle'=>'<a href="refund_reason.html">申请退款</a><a href="javascript:;" class="red">确认收货</a>'
        ],
        [
            'name'=>'已完成,待评价',
            'handle'=>'<a href="refund_reason.html">申请退款</a><a href="evaluation.html" class="red">评价</a>'
        ]
    ];
    protected $table = 'gd_order';
    public function ownGoods()
    {
        return $this->hasMany('OrderGoods','oid')->field('id,oid,name,img,price,extra,num')->order('id asc');
    }
    public function getOrderSn(){
        return date('YmdHis').mt_rand(1000,9999).self::count();
    }
}