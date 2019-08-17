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
            'name'=>['待支付','已支付','已取消'],
            'handle'=>['<a href="javascript:;" class="cancel" onclick="orderCancel({order_id});">取消订单</a><a href="/order/payorder/order_id/{order_id}" class="red">立即付款</a>','<a href="javascript:;" class="cancel" onclick="orderCancel({order_id});">取消订单</a><a href="javascript:;" class="red" onclick="remindOrder({order_id});">提醒发货</a>','<a href="javascript:;" class="cancel">已取消</a>'],
            'w_handle'=>['<a href="/Order/payorder?order_id={order.id}" class="fukuan orange">立即付款</a><a href="javascript:;" class="cancel_order" onclick="orderCancel({order_id});">取消订单</a>','<a href="javascript:;" class="tixing orange_bg" onclick="remindOrder({order_id});">提醒发货</a>','<a href="javascript:;" class="cancel_order">已取消</a>'],
            'field'=>'status'
        ],
        [
            'name'=>'待发货',
            'handle'=>'<a href="javascript:;" class="cancel" onclick="orderCancel({order_id});">取消订单</a><a href="javascript:;" class="red" onclick="remindOrder({order_id});">提醒发货</a>',
            'w_handle'=>'<a href="javascript:;" class="tixing orange_bg" onclick="remindOrder({order_id});">提醒发货</a>',
            'field'=>'is_send'
        ],
        [
            'name'=>'待收货',
            'handle'=>'<a href="refund_reason.html">申请退款</a><a href="javascript:;" onclick="receiveOrder({order_id});" class="red">确认收货</a>',
            'w_handle'=>'<a href="javascript:;" class="confirm orange_bg">确认收货</a><a href="javascript:;" class="sqth">申请退货</a>',
            'field'=>'is_receive'
        ],
        [
            'name'=>['','','','待评价','已完成'],
            'handle'=>['','','','<a href="refund_reason.html">申请退款</a><a href="/Member/comment/order_id/{order_id}" class="red">评价</a>','<a href="javascript:;">已完成</a>'],
            'w_handle'=>['','','','<a href="/Member/comment/order_id/{order_id}" class="orange">去评价</a>','<a href="javascript:;">已完成</a>'],
            'field'=>'status'
        ]
    ];
    protected $table = 'gd_order';
    public function ownGoods()
    {
        return $this->hasMany('OrderGoods','oid')->field('id,gid,oid,name,img,price,extra,num')->order('id asc');
    }
    public function getOrderSn(){
        return date('YmdHis').mt_rand(1000,9999).self::count();
    }
    //取消订单
    public function cancelOrder($uid,$order_id){
        if(empty($order_id) || !is_numeric($order_id) || $order_id<=0) exception('订单信息异常:id');
        if(empty($uid)) exception('用户资料异常');
        $model = self::find($order_id);
        if($model['uid'] != $uid || empty($model)) exception('订单信息异常');
        if(($model['step_flow']!=0) && ($model['step_flow']!=1)) exception('无法操作');
        $model->cancel_time = time();
        $model->status = 2;
        $model->step_flow = 0;
        $bool = $model->save();
        //可能产生的退款操作

        !$bool && exception('操作异常');
        return $model;
    }
    //提醒发货
    public function orderRemind($uid,$order_id){
        if(empty($order_id) || !is_numeric($order_id) || $order_id<=0) exception('订单信息异常:id');
        if(empty($uid)) exception('用户资料异常');
        $model = self::find($order_id);
        if($model['uid'] != $uid || empty($model)) exception('订单信息异常');
        if($model['step_flow']!=1) exception('无法操作');
        if($model['is_remind']==1) exception('已提醒过了');
        $model->is_remind = 1;
        $bool = $model->save();
        !$bool && exception('操作异常');
        return $model;
    }
    //确认收货
    public function orderReceive($uid,$order_id){
        if(empty($order_id) || !is_numeric($order_id) || $order_id<=0) exception('订单信息异常:id');
        if(empty($uid)) exception('用户资料异常');
        $model = self::find($order_id);
        if($model['uid'] != $uid || empty($model)) exception('订单信息异常');
        if($model['step_flow']!=2) exception('无法操作');
        //确认收货
        $model->step_flow = 3; //完成订单流程
        $model->is_receive=1;//收货成功
        $model->receive_end_time = time();
        //交易完成
        $model->status=3;
        $model->complete_time = time();//交易完成
        $bool = $model->save();
        !$bool && exception('操作异常');
        return $model;
    }
}