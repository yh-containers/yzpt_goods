<?php
/**
 * Date: 2019/8/9
 * Time: 9:20
 */
namespace app\index\controller;

use \app\index\controller\Common;
use think\Request;

class Order extends Common
{
    public function initialize()
    {
        parent::initialize();
        $this->checklogin();
    }
    //购物车
    public function cart(){
        $cart_model = new \app\common\model\Cart();
        if($this->request->isAjax()) {//改变购物车的数量
            $res = ['code' => 0, 'msg' => ''];
            $php_input = $this->request->param();
            if($php_input['type'] == 1){
                $cart_model->where(['id'=>$php_input['id']])->update(['num'=>$cart_model->raw('num-1')]);
            }elseif($php_input['type'] == 2){
                $cart_model->where(['id'=>$php_input['id']])->update(['num'=>$cart_model->raw('num+1')]);
            }elseif($php_input['type'] == 3){
                $cart_model->where(['id'=>$php_input['id']])->update(['num'=>($php_input['num'] ? $php_input['num']:1)]);
            }
            $res['code'] = 1;
            echo json_encode($res);die;
        }
        $sku_model = new \app\common\model\GoodsSpecStock();
        $spec_model = new \app\common\model\GoodsSpecValue();
        $collect_model = new \app\common\model\Collect();
        $goods_model = new \app\common\model\Goods();
        $cart_list = $cart_model->alias('c')->leftJoin(['gd_goods'=>'g'],'c.gid=g.id')->where('c.uid='.session('uid'))->field('c.*,g.goods_name,g.goods_image,g.price,g.status')->select();
        foreach ($cart_list as &$cart){
            if($cart['sid']){
                $sku = $sku_model->where(['id'=>$cart['sid']])->field('price,sv_ids')->find();
                $cart['price'] = $sku['price'];
                $spec = $spec_model->where('id in('.$sku['sv_ids'].')')->field('value_name')->select();
                $cart['spec_name'] = ' [ ';
                foreach ($spec as $spv){
                    $cart['spec_name'] .= $spv['value_name'].',';
                }
                $cart['spec_name'] = trim($cart['spec_name'],',');
                $cart['spec_name'] .= ' ]';
            }else{
                $cart['spec_name'] = '';
            }
            $cart['goods_image'] = $goods_model->getGoodsImageAttr($cart['goods_image']);
            //是否收藏
            $cart['is_collect'] = $collect_model->where(['gid'=>$cart['gid'],'uid'=>session('uid')])->count();
        }
        //print_r($cart_list);
        //mobile 推荐
        $tuijian = \app\common\model\Goods::where(['status'=>1,'is_hot'=>1])->field('id,goods_name,price,original_price,goods_image')->limit(2)->select();
        return view('mycart',['cart_list'=>$cart_list,'cart_count'=>count($cart_list),'isCart'=>1,'tuijian'=>$tuijian]);
    }
    //改变购物车
    public function changecart(){
        if($this->request->isAjax()) {
            $res = ['code' => 0, 'msg' => ''];
            $cart_model = new \app\common\model\Cart();
            $collect_model = new \app\common\model\Collect();
            $php_input = $this->request->param();
            try{
                if($php_input['type'] == 'col_del' || $php_input['type'] == 'del'){
                    if($php_input['type'] == 'col_del'){
                        $gids = $cart_model->where('id in('.$php_input['ids'].')')->field('gid')->select();
                        foreach($gids as $gc){
                            $ac = array();
                            $ac['uid'] = session('uid');
                            $ac['gid'] = $gc['gid'];
                            $col = $collect_model->where($ac)->field('id')->find();
                            if($col['id']) {
                                $ac['id'] = $col['id'];
                            }else{
                                $ac['col_time'] = date('Y-m-d H:i:s');
                            }
                            $collect_model->actionAdd($ac);
                        }
                    }
                    $cart_model->where('id in('.$php_input['ids'].')')->delete();
                }elseif($php_input['type'] == 'order'){
                    $cart_model->where('id in('.$php_input['ids'].')')->update(['is_checked'=>1]);
                    $cart_model->where('id not in('.$php_input['ids'].')')->update(['is_checked'=>0]);
                }
            } catch (\Exception $e) {
                $res['msg'] = $e->getMessage();
                echo json_encode($res);die;
            }
            $res['code'] = 1;
            echo json_encode($res);die;
        }
    }
    //支付订单详情
    public function detail(){
        $cart_model = new \app\common\model\Cart();
        $sku_model = new \app\common\model\GoodsSpecStock();
        $spec_model = new \app\common\model\GoodsSpecValue();
        $goods_model = new \app\common\model\Goods();
        $total = 0;
        $integral = 0;
        $fare = 0;
        $cart_list = $cart_model->alias('c')->leftJoin(['gd_goods'=>'g'],'c.gid=g.id')->where('c.uid='.session('uid').' and c.is_checked=1')->field('c.*,g.goods_name,g.goods_image,g.price,g.status,g.integral,g.fare')->select();
        if(count($cart_list) == 0){
            $this->error('未选择购物车商品');
        }
        foreach ($cart_list as &$cart){
            if($cart['sid']){
                $sku = $sku_model->where(['id'=>$cart['sid']])->field('price,sv_ids')->find();
                $cart['price'] = $sku['price'];
                $spec = $spec_model->where('id in('.$sku['sv_ids'].')')->field('value_name')->select();
                $cart['spec_name'] = ' [ ';
                foreach ($spec as $spv){
                    $cart['spec_name'] .= $spv['value_name'].',';
                }
                $cart['spec_name'] = trim($cart['spec_name'],',');
                $cart['spec_name'] .= ' ]';
            }else{
                $cart['spec_name'] = '';
            }
            $cart['goods_image'] = $goods_model->getGoodsImageAttr($cart['goods_image']);
            if($cart['status'] != 1){
                $this->error('购物车中存在下架的商品');
            }
            $total += $cart['price'] * $cart['num'];
            $integral += $cart['integral'] * $cart['num'];
            $fare += $cart['fare'] * $cart['num'];
        }
        //查询用户可用积分
        $use = \app\common\model\Users::field('raise_num')->get(session('uid'));
        if($use['raise_num'] && $integral){
            if($use['raise_num'] < $integral) $integral = $use['raise_num'];
        }else{
            $integral = 0;
        }
        $aid = $this->request->param('aid');
        $addr_default = '';
        $addr_list = '';
        if($aid){
            $addr_default = \app\common\model\UserAddress::where(['id'=>$aid])->find();
        }else{
            $addr_list =\app\common\model\UserAddress::where(['uid'=>session('uid')])->select();
            if(count($addr_list)){
                foreach ($addr_list as $adv){
                    if($adv['is_default']==1){
                        $addr_default = $adv;
                    }
                }
                if(!$addr_default) $addr_default = $addr_list[0];
            }
        }
        return view('order',['goods_list'=>$cart_list,'addr_list'=>$addr_list,'addr_default'=>$addr_default,'total'=>$total,'integral'=>$integral,'use_integral'=>$use['raise_num'],'fare'=>$fare]);
    }
    //创建订单
    public function createorder(){
        if($this->request->isAjax()) {
            $res = ['code' => 0, 'msg' => ''];
            $php_input = $this->request->param();
            $integral = $this->request->param('integral');
            $order_model = new \app\common\model\Order();
            $cart_model = new \app\common\model\Cart();
            $og_model = new \app\common\model\OrderGoods();
            $od_model = new \app\common\model\OrderAddr();
            try{
                \think\Db::startTrans();
                //检查购物车商品状态及库存
                $goods_info = $cart_model->checkCartGoods($integral);
                /*if(!empty($php_input['address'])){
                    $res['msg'] = '未选择收货地址';
                    echo json_encode($res);die;
                }*/
                $inserts = array();
                $inserts['no'] = $order_model->getOrderSn();
                $inserts['uid'] = session('uid');
                $inserts['money'] = $goods_info['total']+$goods_info['fare'];
                $inserts['goods_money'] = $goods_info['total'];
                $inserts['pay_money'] = $goods_info['total'] + $goods_info['fare'] - $goods_info['dis_money'];
                $inserts['dis_money'] = $goods_info['dis_money'];
                $inserts['freight_money'] = $goods_info['fare'];
                $inserts['pay_way'] = ($php_input['pay'] == 'alipay') ? 1: 2;
                $inserts['remark'] = $php_input['remark'];
                $order_model->actionAdd($inserts);
                if($goods_info['dis_money']){
                    \app\common\model\UsersRaiseLogs::recordLog(session('uid'),-($goods_info['dis_money']*100),'','商品折扣：'.($goods_info['dis_money']*100));
                    \app\common\model\Users::where(['id'=>session('uid')])->update(['raise_num'=>\app\common\model\Users::raw('raise_num-'.($goods_info['dis_money']*100))]);

                }
                $og_model->createOrderGoods($goods_info['goods_list'],$order_model->id);
                $od_model->createOrderAddr($php_input['address'],$order_model->id);
                $res['order_id'] = $order_model->id;
                \think\Db::commit();
            } catch (\Exception $e){
                \think\Db::rollback();
                $res['msg'] = $e->getMessage();
                echo json_encode($res);die;
            }
            $res['code'] = 1;
            echo json_encode($res);die;
        }
    }
    //支付界面
    public function payorder(){
        $order_model = new \app\common\model\Order();
        if($this->request->isAjax()){
            $no = $this->request->param('no');
            $orderState = $order_model->where('no='.$no)->field('status')->find();
            return json(['state'=>$orderState['status']]);
        }
        $id = $this->request->param('order_id');
        $order = $order_model->field('no,money')->get($id);
        return view('pay_order',['order'=>$order]);
    }
    public function redurl(){
        sleep(5);
        $order_model = new \app\common\model\Order();
        $id = $this->request->param('oid');
        $order = $order_model->field('id,no,money,pay_way,status')->get($id);
        return view('pay',['order'=>$order]);
    }
    //订单详情
    public function order_detail(){
        $id = $this->request->param('id');
        $order_model = new \app\common\model\Order();
        $sku_model = new \app\common\model\GoodsSpecStock();
        $spec_model = new \app\common\model\GoodsSpecValue();
        $wlModel = new \app\common\model\OrderLogistics();

        $order = $order_model->with('ownGoods,ownAddr')->get($id);
        if(empty($order)) $this->error('订单不存在');
        $state = $order_model->getPropInfo('fields_mobile_step', $order['step_flow']);
        if(isMobile()) {
            $order['handle'] = is_array($state['handle']) ? $state['handle'][$order['status']] : $state['handle'];
        }else{
            $order['handle'] = is_array($state['w_handle']) ? $state['w_handle'][$order['status']] : $state['w_handle'];
        }
        $order['handle'] = str_replace('{order_id}',$order['id'],$order['handle']);
        $order['state'] = is_array($state['name']) ? $state['name'][$order['status']] : $state['name'];
        if($order['status'] == 0) $order['step'] = 1;
        if($order['status'] == 1) $order['step'] = 2;
        if($order['status'] == 2) $order['step'] = 1;
        if($order['step_flow'] == 1) $order['step'] = 3;
        if($order['step_flow'] == 2) $order['step'] = 4;
        if($order['status'] == 4) $order['step'] = 5;
        if($order['status'] == 5) $order['step'] = 1;
//        print_r($order['status']);
        $order['wl'] = '';
        if($order['step_flow'] > 2){
            $order['wl'] = $wlModel->where(['oid'=>$id])->find();
        }
        $order['number'] = 0;
        foreach ($order['own_goods'] as &$goods) {
            $order['number'] += $goods['num'];
            $extra = explode(':',$goods['extra']);
            if($extra[1]){
                $sku = $sku_model->where(['id'=>$extra[1]])->field('price,sv_ids')->find();
                $spec = $spec_model->where('id in('.$sku['sv_ids'].')')->field('value_name')->select();
                $goods['spec_name'] = ' ';
                foreach ($spec as $spv){
                    $goods['spec_name'] .= $spv['value_name'].' + ';
                }
                $goods['spec_name'] = trim($goods['spec_name'],' + ');
            }else{
                $goods['spec_name'] = '';
            }
        }
        //print_r($order['own_addr']);

        return view('order_detail',['active'=>'order','order'=>$order]);
    }
}