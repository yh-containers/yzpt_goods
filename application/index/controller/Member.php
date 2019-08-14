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
        $this->checklogin();
    }
    public function index(){
        //self::orderlist();
        $this->redirect('Member/orderlist');
    }
    //我的订单列表
    public function orderlist(){
        $sql_where = 'uid='.session('uid');
        $status = $this->request->param('status');
        if($status){

        }
        $order_model = new \app\common\model\Order();
        $order_list = $order_model->field('id,no,money,pay_time,cancel_time,complete_time,send_start_time,receive_start_time,create_time,step_flow,status,is_send,is_receive')->with('ownGoods')->where($sql_where)->paginate();
        $page = $order_list->render();
        foreach ($order_list as &$order){
            $order_model->getPropInfo('fields_step',$order['step_flow']);
        }
        return view('myorder',['active'=>'order','order_list'=>$order_list,'status'=>$status,'page'=>$page]);
    }
    //我的收藏
    public function collect(){
        $col_model = new \app\common\model\Collect();
        $col_list = $col_model->alias('c')->leftJoin(['gd_goods'=>'g'],'c.gid=g.id')->where('c.uid='.session('uid'))->field('c.*,g.goods_name,g.goods_image,g.price')->paginate();
        $page = $col_list->render();
        return view('mycollect',['col_list'=>$col_list,'page'=>$page,'active'=>'col']);
    }
    //我的评论
    public function mycomment(){
        return view('mycom',['active'=>'com']);
    }
    //我的退款单
    public function myretreat(){
        return view('myretreat',['active'=>'ret']);
    }
    //我的基本信息
    public function center(){
        $user_model = new \app\common\model\Users();
        if($this->request->isAjax()) {
            $res = ['code' => 0, 'msg' => ''];
            $php_input = $this->request->param();
            try{
                $user_model->where(['id'=>session('uid')])->update($php_input);
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
        $member = $user_model->field('phone,salt')->get(session('uid'));
        if($this->request->isAjax()) {
            $res = ['code' => 0, 'msg' => ''];
            $php_input = $this->request->param();
            try {
                if($php_input['type'] == 'phone'){
                    if (!captcha_check($php_input['verify'])) {
                        $res['msg'] = '验证码错误';
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
        return view('mysafe',['member'=>$member,'active'=>'safe']);
    }
    //账户验证
    public function checkuser(){
        if($this->request->isAjax()) {
            $res = ['code' => 0, 'msg' => ''];
            $php_input = $this->request->param();
            if (!captcha_check($php_input['verify'])) {
                $res['msg'] = '验证码错误';
                echo json_encode($res);die;
            };
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
            $php_input['addr'] = $php_input['province'].'-'.$php_input['city'].'-'.$php_input['town'];
            unset($php_input['province']);
            unset($php_input['city']);
            unset($php_input['town']);
            $php_input['uid'] = session('uid');
            try{
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
        return view('address',['addr_list'=>$addr_list,'active'=>'addr']);
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
}