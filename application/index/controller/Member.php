<?php
/**
 * Date: 2019/8/8
 * Time: 15:50
 */
namespace app\index\controller;

use \app\index\controller\Common;
use think\Request;

class Member extends Common
{
    public function initialize()
    {
        parent::initialize();
        $this->checklogin();
    }
    public function index(){
        //self::orderlist();
        $this->redirect('Member/orderlist');
    }
    //我的订单列表
    public function orderlist(){
        $sql_where = 'uid='.session('uid').' and status<5 and is_del!=1';
        $step = $this->request->param('step');
        switch (intval($step)){
            case 1://待发货
                $sql_where .= ' and step_flow=1 and status=1';
                break;
            case 2://待收货
                $sql_where .= ' and step_flow=2';
                break;
            case 3://待评价
                $sql_where .= ' and step_flow=3 and status=3';
                break;
            case 4://待付款
                $sql_where .= ' and step_flow=0 and status=0';
                break;
            case 5://已完成
                $sql_where .= ' and step_flow=3 and status=4';
                break;
            case 6://已取消
                $sql_where .= ' and step_flow=0 and status=2';
                break;
            default:
                $step = 'default';
                break;
        }
        $order_model = new \app\common\model\Order();
        $sku_model = new \app\common\model\GoodsSpecStock();
        $spec_model = new \app\common\model\GoodsSpecValue();
        $order_list = $order_model->with('ownAddrs')->field('id,no,money,pay_money,pay_time,cancel_time,complete_time,send_start_time,receive_start_time,create_time,step_flow,status,is_send,is_receive')->with('ownGoods')->where($sql_where)->order('create_time desc')->paginate();
        $page = $order_list->render();
        foreach ($order_list as &$order){
            $state = $order_model->getPropInfo('fields_mobile_step', $order['step_flow']);
            if(isMobile()) {
                $order['handle'] = is_array($state['handle']) ? $state['handle'][$order['status']] : $state['handle'];
            }else{
                $order['handle'] = is_array($state['w_handle']) ? $state['w_handle'][$order['status']] : $state['w_handle'];
            }
            $order['handle'] = str_replace('{order_id}',$order['id'],$order['handle']);
            $order['status'] = is_array($state['name']) ? $state['name'][$order['status']] : $state['name'];
            $order['number'] = 0;
            foreach ($order['own_goods'] as &$goods){
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
        }
        //print_r($order_list);
        return view('myorder',['active'=>'order','order_list'=>$order_list,'status'=>$step,'page'=>$page]);
    }
    //订单操作
    public function handleorder(){
        if($this->request->isAjax()) {
            $res = ['code' => 0, 'msg' => ''];
            $oid = $this->request->param('order_id');
            $handle = $this->request->param('handle');
            $order_model = new \app\common\model\Order();
            try{
                \think\Db::startTrans();
                if($handle == 'cancel'){//取消订单
                    $order_model->cancelOrder(session('uid'),$oid);
                    $res['msg'] = '已取消';
                }else if($handle == 'remind'){//提醒发货
                    $order_model->orderRemind(session('uid'),$oid);
                    $res['msg'] = '已提醒';
                }else if($handle == 'receive'){//收货
                    $order_model->orderReceive(session('uid'),$oid);
                    $res['msg'] = '已确认';
                }else if($handle == 'retreat'){//退货
                    $order_model->orderRetreat(session('uid'),$oid);
                    $res['msg'] = '操作成功';
                }else if($handle == 'del'){//用户删除
                    $model = $order_model->get($oid);
                    if($model['dis_money'] && ($model['status'] != 2)){
                        $normal_content = \app\common\model\SysSetting::getContent('normal');
                        $normal_content = empty($normal_content)?[]:json_decode($normal_content,true);
                        $score = intval(($model['dis_money']/$normal_content['integral_money'])*100);
                        \app\common\model\UsersRaiseLogs::recordLog($model['uid'],$score,'','订单取消，退回养分：'.$score);
                        \app\common\model\Users::where(['id'=>$model['uid']])->update(['raise_num'=>\app\common\model\Users::raw('raise_num+'.$score)]);
                    }
                    $order_model->where(['id'=>$oid])->update(['is_del'=>1]);
                    $res['msg'] = '已删除';
                }else if($handle == 'cancel_retreat'){//取消退款
                    $arr = $order_model->where(['id'=>$oid])->find();
                    $data = array();
                    //if($arr['receive_end_time']){
                        $data['step_flow'] = 3;
                        $data['status'] = 3;
                    //}
                    $order_model->where(['id'=>$oid])->update($data);
                    $res['msg'] = '操作成功';
                }
                \think\Db::commit();
            }catch (\Exception $e){
                \think\Db::rollback();
                $res['msg'] = $e->getMessage();
                echo json_encode($res);die;
            }
            $res['code'] = 1;
            echo json_encode($res);die;
        }
    }
    //我的收藏
    public function collect(){
        $col_model = new \app\common\model\Collect();
        $col_list = $col_model->alias('c')->leftJoin(['gd_goods'=>'g'],'c.gid=g.id')->where('c.uid='.session('uid'))->field('c.*,g.goods_name,g.goods_image,g.price,g.original_price')->paginate();
        foreach ($col_list as &$v){
            $v['goods_image'] = $col_model->getGoodsImageAttr($v['goods_image']);
        }
        $page = $col_list->render();
        return view('mycollect',['col_list'=>$col_list,'page'=>$page,'active'=>'col']);
    }
    //移除收藏
    public function del_collect(){
        if($this->request->isAjax()) {
            $res = ['code' => 0, 'msg' => ''];
            $col_model = new \app\common\model\Collect();
            $ids = $this->request->param('ids');
            $ids = explode(',',$ids);
            foreach ($ids as $id){
                if($id) $col_model->actionDel(['id'=>['in',$id]]);
            }
            $res['msg'] = '已移除收藏';
            $res['code'] = 1;
            echo json_encode($res);die;
        }
    }
    //我的评论
    public function mycomment(){
        $com_model = new \app\common\model\Comment();
        $com_list = $com_model->alias('c')->leftJoin(['gd_goods'=>'g'],'c.gid=g.id')->field('c.gid,c.create_time,c.content,g.goods_image,g.goods_name')->where('c.uid='.session('uid').' and c.status=1')->paginate();
        $page = $com_list->render();
        foreach ($com_list as &$v){
            $v['goods_image'] = $com_model->getGoodsImageAttr($v['goods_image']);
        }
        //print_r($com_list);
        return view('mycom',['active'=>'com','com_list'=>$com_list,'page'=>$page]);
    }
    //商品评价展示
    public function comment(){
        $order_id = $this->request->param('order_id');
        $order_model = new \app\common\model\Order();
        $sku_model = new \app\common\model\GoodsSpecStock();
        $spec_model = new \app\common\model\GoodsSpecValue();
        $order = $order_model->field('id,no,create_time')->with('ownGoods')->where(['id'=>$order_id])->find();
        foreach ($order['own_goods'] as &$goods){
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
        //print_r($order);
        return view('handlecomment',['active'=>'order','goods_list'=>$order['own_goods'],'order_sn'=>$order['no'],'create_time'=>$order['create_time']]);
    }
    //商品评价操作
    public function handlecomment(){
        if($this->request->isAjax()) {
            $res = ['code' => 0, 'msg' => ''];
            $php_input = $this->request->param();
            $img = $this->request->param('img');
            $order_model = new \app\common\model\Order();
            try{
                foreach ($php_input['gid'] as $k=>$val){
                    $com_model = new \app\common\model\Comment();
                    $addInfo = array();
                    $addInfo['gid'] = $val;
                    $addInfo['uid'] = session('uid');
                    $addInfo['oid'] = $php_input['oid'][$k];
                    if($com_model->where($addInfo)->count()){
                        continue;
                    }
                    $addInfo['content'] = $php_input['content'][$k];
                    $addInfo['grade'] = $php_input['star'][$k];
                    if($img[$k]) {
                        $addInfo['imgs'] = implode(',',$img[$k]);
                        $addInfo['imgs'] = trim($addInfo['imgs'],',');
                    }
                    $com_model->actionAdd($addInfo);
                }
                $order_model->where(['id'=>$php_input['oid'][1]])->update(['status'=>4]);
            } catch (\Exception $e) {
                $res['msg'] = $e->getMessage();
                echo json_encode($res);
                die;
            }
            $res['code'] = 1;
            echo json_encode($res);die;
        }
    }
    //我的退款单
    public function myretreat(){
        $sql_where = 'uid='.session('uid').' and status=5';
        $order_model = new \app\common\model\Order();
        $sku_model = new \app\common\model\GoodsSpecStock();
        $spec_model = new \app\common\model\GoodsSpecValue();
        $returnOrderModel = new \app\common\model\OrderReturn();
        $order_list = $order_model->with('ownAddrs')->field('id,no,money,pay_money,pay_time,cancel_time,complete_time,send_start_time,receive_start_time,create_time,step_flow,status,is_send,is_receive')->with('ownGoods')->where($sql_where)->order('create_time desc')->paginate();
        $page = $order_list->render();
        foreach ($order_list as &$order){
            $state = $order_model->getPropInfo('fields_mobile_step', $order['step_flow']);
            $order['status'] = is_array($state['name']) ? $state['name'][$order['status']] : $state['name'];
            $order['number'] = 0;
            foreach ($order['own_goods'] as &$goods){
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
            $order['cancel'] = 1;
            $returnOrder = $returnOrderModel->where(['oid'=>$order['id']])->field('state')->find();
            if($returnOrder['state']>0){
                $order['cancel'] = 0;
            }
        }
        return view('myretreat',['active'=>'ret','order_list'=>$order_list,'page'=>$page]);
    }
    //退货单填写
    public function return_order(){
        $id = $this->request->param('id');
        $order_model = new \app\common\model\Order();
        $sku_model = new \app\common\model\GoodsSpecStock();
        $spec_model = new \app\common\model\GoodsSpecValue();
        $returnOrderModel = new \app\common\model\OrderReturn();
        if($this->request->isAjax()){
            $res = ['code' => 0, 'msg' => ''];
            try{
                \think\Db::startTrans();
                $php_input = $this->request->param();
                $rid = $this->request->param('id');
                $img = $this->request->param('img');
                unset($php_input['img']);
                if(empty($rid)){
                    $order_model->orderRetreat(session('uid'),$php_input['oid']);
                }
                if($img['image']) $php_input['image'] = trim(implode(',',$img['image']),',');
                $php_input['uid'] = session('uid');
                $returnOrderModel->actionAdd($php_input);
                \think\Db::commit();
            }catch (\Exception $e){
                \think\Db::rollback();
                $res['msg'] = $e->getMessage();
                return json($res);
            }
            $res['code'] = 1;
            return json($res);die;
        }
        $order = $order_model->with('ownGoods,ownAddrs')->get($id);
        if(empty($order)) $this->error('订单不存在');
        foreach ($order['own_goods'] as &$goods) {
            //$order['number'] += $goods['num'];
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
        $return_order = $returnOrderModel->where(['oid'=>$id])->find();
        $edit = $this->request->param('isedit');
        $isedit = $edit ? $edit:0;
        return view('order_return',['active'=>'ret','order'=>$order,'return_order'=>$return_order,'isedit'=>$isedit]);
    }
    //我的首页 mobile
    public function user(){
        $order_model = new \app\common\model\Order();
        $where['uid'] = session('uid');
        //待付款
        $where['step_flow'] = 0;
        $where['status'] = 0;
        $order_count['pay'] = $order_model->where($where)->count();
        unset($where['status']);
        //待发货
        $where['step_flow'] = 1;
        $order_count['send'] = $order_model->where($where)->count();
        //待评价
        $where['step_flow'] = 3;
        $where['status'] = 3;
        $order_count['com'] = $order_model->where($where)->count();
        //退货退款
        unset($where['step_flow']);
        $where['status'] = 5;
        $order_count['ret'] = $order_model->where($where)->count();
        return view('user',['order_count'=>$order_count]);
    }
    //我的基本信息
    public function center(){
        $user_model = new \app\common\model\Users();
        if($this->request->isAjax()) {
            $res = ['code' => 0, 'msg' => ''];
            $php_input = $this->request->param();
            $face = $this->request->param('face');
            $name = $this->request->param('name');
            try{
                $user_model->where(['id'=>session('uid')])->update($php_input);
                if($face || $name){
                    $uinfo = session('userinfo');
                    if($face) $uinfo['face'] = $user_model->getFaceAttr($face);
                    if($name) $uinfo['uname'] = $name;
                    session('userinfo',$uinfo);
                }
            } catch (\Exception $e) {
                $res['msg'] = $e->getMessage();
                echo json_encode($res);
                die;
            }
            $res['code'] = 1;
            echo json_encode($res);die;
        }
        $member = $user_model->field('name,phone,address,face,sex,real_name')->get(session('uid'));
        return view('center',['member'=>$member,'active'=>'user']);
    }
    //账户安全
    public function mysafe(){
        $user_model = new \app\common\model\Users();
        $member = $user_model->get(session('uid'));//->field('phone,salt,wx_openid')
        if($this->request->isAjax()) {
            $res = ['code' => 0, 'msg' => ''];
            $php_input = $this->request->param();
            $verify = $this->request->param('verify');
            try {
                if($php_input['type'] == 'phone'){
                    if($verify){
                        if (!captcha_check($verify)) {
                            $res['msg'] = '图形验证码错误';
                            echo json_encode($res);die;
                        }
                    }
                    if($user_model->where(['phone'=>$php_input['phone']])->count()){
                        $res['msg'] = '手机号也被使用';
                        echo json_encode($res);die;
                    }
                    \app\common\model\Sms::validVerify(1,$php_input['phone'],$php_input['code']);
                    $user_model->where(['id'=>session('uid')])->update(['phone'=>$php_input['phone']]);
                }elseif($php_input['type'] == 'pass'){
                    $user_model->handleLogin($member['phone'],$php_input['old_password']);
                    if($php_input['old_password'] == $php_input['new_password']){
                        $res['msg'] = '密码未做更改';
                        echo json_encode($res);die;
                    }
                    $password = $user_model->generatePwd($php_input['new_password'],$member['salt']);
                    $user_model->where(['id'=>session('uid')])->update(['password'=>$password]);
                }
                session('userinfo',null);
                session('uid',null);
            } catch (\Exception $e) {
                $res['msg'] = $e->getMessage();
                echo json_encode($res);
                die;
            }
            $res['code'] = 1;
            echo json_encode($res);die;
        }
        return view('mysafe',['member'=>$member,'active'=>'safe','type'=>$this->request->param('modify')]);
    }
    //账户验证
    public function checkuser(){
        if($this->request->isAjax()) {
            $res = ['code' => 0, 'msg' => ''];
            $php_input = $this->request->param();
            $verify = $this->request->param('verify');
            if($verify){
                if (!captcha_check($verify)) {
                    $res['msg'] = '验证码错误';
                    echo json_encode($res);die;
                }
            }
            try {
                \app\common\model\Sms::validVerify(1,$php_input['phone'],$php_input['code']);
            } catch (\Exception $e) {
                $res['msg'] = $e->getMessage();
                echo json_encode($res);
                die;
            }
            $res['code'] = 1;

            echo json_encode($res);die;
        }
    }
    //我的地址
    public function myaddress(){
        $addr_model = new \app\common\model\UserAddress();
        if($this->request->isAjax()) {
            $res = ['code' => 0, 'msg' => ''];
            $php_input = $this->request->param();
            $addr = $this->request->param('addr');
            $province = $this->request->param('province');
            $city = $this->request->param('city');
            $town = $this->request->param('town');
            $is_default = $this->request->param('is_default');
            if(!$addr){
                $php_input['addr'] = $php_input['province'].'-'.$php_input['city'].'-'.$php_input['town'];
            }
            unset($php_input['province']);
            unset($php_input['city']);
            unset($php_input['town']);
            if(empty($php_input['addr'])){
                $res['msg'] = '请选择收货地址';
                echo json_encode($res);
                die;
            }
            $php_input['uid'] = session('uid');
            try{
                if($is_default){
                    $addr_model->where(['is_default'=>1,'uid'=>session('uid')])->update(['is_default'=>0]);
                }
                $addr_model->actionAdd($php_input);
                if(empty($php_input['id'])) $res['id'] = $addr_model->id;
            } catch (\Exception $e) {
                $res['msg'] = $e->getMessage();
                echo json_encode($res);
                die;
            }
            $res['code'] = 1;
            echo json_encode($res);die;
        }
        $addr_list = $addr_model->where(['uid'=>session('uid')])->select();
        $from = $this->request->param('from');
        return view('address',['addr_list'=>$addr_list,'active'=>'addr','from'=>$from]);
    }
    //地址删除、默认
    public function addrchange(){
        if($this->request->isAjax()) {
            $addr_model = new \app\common\model\UserAddress();
            $res = ['code' => 0, 'msg' => ''];
            $php_input = $this->request->param();
            try{
                if($php_input['type'] == 'del'){
                    $addr_model->actionDel(['id'=>$php_input['id']]);
                }elseif($php_input['type'] == 'default'){
                    unset($php_input['type']);
                    $addr_model->where(['is_default'=>1,'uid'=>session('uid')])->update(['is_default'=>0]);
                    $addr_model->actionAdd($php_input);
                }
            } catch (\Exception $e) {
                $res['msg'] = $e->getMessage();
                echo json_encode($res);
                die;
            }
            $res['code'] = 1;
            echo json_encode($res);die;
        }
    }
    //我的消息
    public function message(){
        $model = new \app\common\model\OrderMsg();
        $orderModel = new \app\common\model\Order();
        $list = $model->where(['uid'=>session('uid')])->select();
        foreach ($list as &$v){
            $v['own_order'] = $orderModel->find($v['oid']);
        }
//        print_r($list);
        return view('message',['list'=>$list]);
    }
    //我的养分
    public function myintegral(){
        $use = \app\common\model\Users::field('raise_num')->get(session('uid'));
        $integralModel = new \app\common\model\UsersRaiseLogs();
        $addlog = $integralModel->where('num>0 and uid='.session('uid'))->order('create_time desc')->select();
        $lesslog = $integralModel->where('num<=0 and uid='.session('uid'))->order('create_time desc')->select();
        //$integral =  $integralModel->where(['uid'=>session('uid')])->sum('num');
        $integral = $use['raise_num'];//+$integralModel->where('num<=0 and uid='.session('uid'))->sum('num');
        return view('integral',['addlog'=>$addlog,'lesslog'=>$lesslog,'integral'=>$integral]);
    }
}