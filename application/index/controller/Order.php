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
        $total = 0;
        $cart_list = $cart_model->alias('c')->leftJoin(['gd_goods'=>'g'],'c.gid=g.id')->where('c.uid='.session('uid').' and c.is_checked=1')->field('c.*,g.goods_name,g.goods_image,g.price,g.status')->select();
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
            if($cart['status'] != 1){
                $this->error('购物车中存在下架的商品');
            }
            $total += $cart['price'] * $cart['num'];
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
        return view('order',['goods_list'=>$cart_list,'addr_list'=>$addr_list,'addr_default'=>$addr_default,'total'=>$total]);
    }
    //创建订单
    public function createorder(){
        if($this->request->isAjax()) {
            $res = ['code' => 0, 'msg' => ''];
            $php_input = $this->request->param();
            $order_model = new \app\common\model\Order();
            $cart_model = new \app\common\model\Cart();
            $og_model = new \app\common\model\OrderGoods();
            $od_model = new \app\common\model\OrderAddr();
            try{
                //检查购物车商品状态及库存
                $goods_info = $cart_model->checkCartGoods();
                $inserts = array();
                $inserts['no'] = $order_model->getOrderSn();
                $inserts['uid'] = session('uid');
                $inserts['money'] = $goods_info['total'];
                $inserts['goods_money'] = $goods_info['total'];
                $inserts['pay_way'] = ($php_input['pay'] == 'alipay') ? 1: 2;
                $inserts['remark'] = $php_input['remark'];
                $order_model->actionAdd($inserts);
                $og_model->createOrderGoods($goods_info['goods_list'],$order_model->id);
                $od_model->createOrderAddr($php_input['address'],$order_model->id);
                $res['order_id'] = $order_model->id;
            } catch (\Exception $e){
                $res['msg'] = $e->getMessage();
                echo json_encode($res);die;
            }
            $res['code'] = 1;
            echo json_encode($res);die;
        }
    }
    //支付界面
    public function payorder(){
        $id = $this->request->param('order_id');
        $order_model = new \app\common\model\Order();
        $order = $order_model->field('no,money')->get($id);
        return view('pay_order',['order'=>$order]);
    }

}