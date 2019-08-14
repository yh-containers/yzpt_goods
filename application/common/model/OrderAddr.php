<?php
/**
 * Date: 2019/8/12
 * Time: 16:53
 */
namespace app\common\model;
class OrderAddr extends BaseModel
{
    protected $table = 'gd_order_addr';
    public function createOrderAddr($addr_id,$order_id){
        empty($order_id) && exception('订单不存在');
        empty($addr_id) && exception('地址不存在');
        $addr =\app\common\model\UserAddress::where(['id'=>$addr_id])->find();
        empty($addr) && exception('地址不存在');
        $addrs = array();
        $addrs['oid'] = $order_id;
        $addrs['phone'] = $addr['phone'];
        $addrs['username'] = $addr['username'];
        $addrs['addr'] = $addr['addr'];
        $addrs['addr_extra'] = $addr['addr_extra'];
        $model = new self();
        $model->data($addrs);
        $bool = $model->save();
        !$bool && exception('数据异常');
        return $model;
    }
}