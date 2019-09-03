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
    //订单有效时间--有效时间为1小时  单位秒
    const ORDER_EXP_TIME = 7200;
    //用户可操作常量
    const U_ORDER_HANDLE_PAY = 'pay';           //订单支付
    const U_ORDER_HANDLE_CANCEL = 'cancel';     //取消订单
    const U_ORDER_HANDLE_DEL = 'del';           //删除订单
    const U_ORDER_HANDLE_SURE_REC = 'receive'; //确认收货

    //管理员操作
    const M_ORDER_HANDLE_SURE_PAY = 'sure-pay';      //确定支付
    const M_ORDER_HANDLE_SEND = 'send';         //发送
    const M_ORDER_HANDLE_DEL = 'del';           //删除
    const M_ORDER_HANDLE_CANCEL = 'cancel';     //取消
    //const M_ORDER_HANDLE_EDIT_ADDR = 'edit-addr';     //编辑订单地址

    public $m_id_opt_del=0;  //删除订单
    public $m_id_opt_cancel=0;//取消订单

    //收货方式
    public static $fields_rec_mode = [
        ['name'=>'自提'],
        ['name'=>'快递'],
    ];

    //订单状态
    public static $fields_status = [
        ['name'=>'待付款','u_handle'=>[
            self::U_ORDER_HANDLE_PAY,
            self::U_ORDER_HANDLE_CANCEL,
        ],'m_handle'=>[
            self::M_ORDER_HANDLE_DEL,self::M_ORDER_HANDLE_CANCEL,self::M_ORDER_HANDLE_SURE_PAY
        ]
        ],
        ['name'=>'已付款','m_handle'=>[]],
        ['name'=>'已取消','u_handle'=>[self::U_ORDER_HANDLE_DEL],'m_handle'=>[self::M_ORDER_HANDLE_DEL]],
        ['name'=>'已完成,待评价','u_handle'=>[self::U_ORDER_HANDLE_DEL],'m_handle'=>[]],
        ['name'=>'已完成','u_handle'=>[self::U_ORDER_HANDLE_DEL],'m_handle'=>[]],
        ['name'=>'申请退款','u_handle'=>[],'m_handle'=>[]],
    ];
    //发货状态
    public static $fields_is_send = [
        ['name'=>'待发貨','m_handle'=>[
            self::M_ORDER_HANDLE_SEND,
        ]],
        ['name'=>'已发貨','m_handle'=>[]],
    ];
    //收货状态
    public static $fields_is_recive = [
        ['name'=>'待收貨','u_handle'=>[ self::U_ORDER_HANDLE_SURE_REC ],'m_handle'=>[]],
        ['name'=>'已收貨','m_handle'=>[]],
    ];
    public static $fields_step = [
        ['name'=>'支付流程','field'=>'status','prop_func'=>'fields_status'],
        ['name'=>'发货流程','field'=>'is_send','prop_func'=>'fields_is_send'],
        ['name'=>'收货流程','field'=>'is_receive','prop_func'=>'fields_is_recive'],
        ['name'=>'交易已完成','field'=>'status','prop_func'=>'fields_status']
    ];
    public static $fields_pay = ['','支付宝','微信'];
    public static $fields_mobile_step = [
        [
            'name'=>['待支付','已支付','已取消','','','申请退款'],
            'handle'=>['<a href="javascript:;" class="cancel" onclick="orderCancel({order_id});">取消订单</a><a href="/pay/info?order_id={order_id}" class="red">立即付款</a>','<a href="javascript:;" class="cancel" onclick="orderCancel({order_id});">取消订单</a><a href="javascript:;" class="red" onclick="remindOrder({order_id});">提醒发货</a>','<a href="javascript:;" onclick="orderDel({order_id});" class="red">删除订单</a>','','',''],
            'w_handle'=>['<a href="/Pay/info?order_id={order_id}" class="fukuan orange">立即付款</a><a href="javascript:;" class="cancel_order" onclick="orderCancel({order_id});">取消订单</a>','<a href="javascript:;" class="tixing orange_bg" onclick="remindOrder({order_id});">提醒发货</a>','<a href="javascript:;" class="cancel_order orange"  onclick="orderDel({order_id});">删除订单</a>','','',''],
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
            'handle'=>'<a href="javascript:;" onclick="retreatOrder({order_id});">申请退货</a><a href="javascript:;" onclick="receiveOrder({order_id});" class="red">确认收货</a>',
            'w_handle'=>'<a href="javascript:;" onclick="receiveOrder({order_id});" class="confirm orange_bg">确认收货</a><a href="javascript:;" onclick="retreatOrder({order_id});" class="sqth">申请退货</a>',
            'field'=>'is_receive'
        ],
        [
            'name'=>['','','','待评价','已完成'],
            'handle'=>['','','','<a href="javascript:;" onclick="retreatOrder({order_id});">申请退款</a><a href="/Member/comment/order_id/{order_id}" class="red">评价</a>','<a href="javascript:;">已完成</a><a href="javascript:;" onclick="orderDel({order_id});" class="red">删除订单</a>'],
            'w_handle'=>['','','','<a href="/Member/comment/order_id/{order_id}" class="orange">去评价</a>','<a href="javascript:;">已完成</a><a href="javascript:;" class="cancel_order orange"  onclick="orderDel({order_id});">删除订单</a>'],
            'field'=>'status'
        ]
    ];
    protected $table = 'gd_order';
    public function ownGoods()
    {
        return $this->hasMany('OrderGoods','oid')->field('id,gid,oid,name,img,price,extra,num')->order('id asc');
    }
    public function ownAddr()
    {
        return $this->hasMany('OrderAddr','oid')->order('id asc');
    }
    public function ownAddrs()
    {
        return $this->hasOne('OrderAddr','oid')->order('id asc');
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
    //退款退货
    public function orderRetreat($uid,$order_id){
        if(empty($order_id) || !is_numeric($order_id) || $order_id<=0) exception('订单信息异常:id');
        if(empty($uid)) exception('用户资料异常');
        $model = self::find($order_id);
        if($model['uid'] != $uid || empty($model)) exception('订单信息异常');
        if(($model['step_flow']!=2) && ($model['step_flow']!=3)) exception('无法操作');
        $model->cancel_time = time();
        $model->status = 5;
        $model->step_flow = 0;
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
    //管理员确认付款
    public function orderPay($uid,$order_id){
        if(empty($order_id) || !is_numeric($order_id) || $order_id<=0) exception('订单信息异常:id');
        if(empty($uid)) exception('用户资料异常');
        $model = self::find($order_id);
        if($model['uid'] != $uid || empty($model)) exception('订单信息异常');
        $handle_action = self::getHandle('m_handle',$model);
        if(!in_array(self::M_ORDER_HANDLE_SURE_PAY,$handle_action)) exception('订单状态未处于付款状态');
        $model->step_flow = 1;
        $model->status = 1;
        $model->pay_time = time();
        $bool = $model->save();
        !$bool && exception('操作异常');
        return $model;
    }
    //管理员确认发货
    public function orderSend($uid,$order_id){
        if(empty($order_id) || !is_numeric($order_id) || $order_id<=0) exception('订单信息异常:id');
        if(empty($uid)) exception('用户资料异常');
        $model = self::find($order_id);
        if($model['uid'] != $uid || empty($model)) exception('订单信息异常');
        $handle_action = self::getHandle('m_handle',$model);
        if(!in_array(self::M_ORDER_HANDLE_SEND,$handle_action)) exception('订单状态未处于待发货状态');
        $model->is_send = 1;
        $model->send_end_time = time();
        $model->step_flow = 2;
        $model->is_receive=0;//等待收货状态
        $model->receive_start_time=time();//开始收货时间
        $bool = $model->save();
        !$bool && exception('操作异常');
        return $model;
    }

    public function getHandle($mode,$order){
        $step = self::$fields_step[$order['step_flow']];
        $handle = self::getPropInfo($step['prop_func'],$order[$step['field']]);
        return $handle[$mode];
    }

    //订单数据
    public function getOrderPayInfo($mode)
    {
        return [
            'body' => '订单支付',
            'attach' => 'attach',
            'no' => $this->getAttr('no'),
//            'pay_money' => $this->getAttribute('pay_money'),
            'pay_money' => $this->getAttr('pay_money'),
            'expire_time' => self::ORDER_EXP_TIME,
            'goods_tag' => 'goods',
            'notify_url' => url('pay/notify',['mode'=>$mode],false,true),
            'return_url' => url('Index/index',['mode'=>$mode],false,true)
        ];
    }


    //调整订单支付已完成
    protected function order_success()
    {
//        if(!empty($this->step_flow)){
//            return true;
//        }
        //调整订单信息
        $this->step_flow = 1;
        $this->status = 1;
        $this->pay_time = time();
        return $this->save();
    }

    //订单回调通知
    public static function handleNotify($order_no,array $data)
    {
        $model = self::where(['no'=>$order_no])->find();
        if(empty($model)){
            return;
        }
        //保存第三方支付信息
        $model->setAttr('pay_info',json_encode($data));
        return $model->order_success();
    }
}