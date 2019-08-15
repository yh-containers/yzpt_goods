<?php
namespace app\index\controller;

use \app\common\model\SysManager;
use think\Request;

class Index extends Common
{
    public function index(){
        $goods_model = new \app\common\model\Goods();
        //今日特惠
        $et = strtotime(date('Y-m-d').' 23:59:59');
        $st = strtotime(date('Y-m-d').' 00:00:00');
        $today_where = 'status=1 and is_special=1 and update_time between '.$st.' and '.$et;
        $goods['today'] = $goods_model->where($today_where)->field('id,goods_image')->select();
        //新品推荐
        $goods['new'] = $goods_model->where(['status'=>1,'is_best'=>1])->field('id,goods_name,price,original_price,goods_image')->select();
        //特价
        $goods['special'] = $goods_model->where(['status'=>1,'is_special'=>1])->field('id,goods_image')->select();
        //人气
        $goods['hot'] = $goods_model->where(['status'=>1,'is_hot'=>1])->field('id,goods_image')->select();
        //print_r($today_where);
        //首页banner
        $banner = \app\common\model\Ad::where('type=2 and status=1')->field('url,img')->select();
        return view('index',['goods'=>$goods,'banner'=>$banner,'isIndex'=>1]);
    }
//登录登出
    public function login(){
        if(session('uid')){
            $this->redirect(url('Index/index'));
        }
        return view('login');
    }
    public function dologin(){
        if($this->request->isAjax()) {
            $res = ['err'=>1,'msg'=>''];
            $account = $this->request->param('account');
            $password = $this->request->param('password');
            try{
                $model = \app\common\model\Users::handleLogin($account,$password,0);
            }catch (\Exception $e){
                $res['msg'] = $e->getMessage();
                echo json_encode($res);die;
            }
            session('userinfo',[
                'uid' => $model['id'],
                'uname' => $model['name'],
                'face' => $model['face']
            ]);
            session('uid',$model['id']);
            $res['err'] = 0;
            $res['msg'] = '登录成功';
            echo json_encode($res);die;
        }
    }
    public function loginout(){
        session('userinfo',null);
        session('uid',null);
        $this->success("退出成功");
    }
//注册三步
    public function register(){
        if(session('uid')){
            $this->redirect(url('Index/index'));
        }
        session('step',1);
        //手机号 验证码验证
        if($this->request->isAjax()) {
            $res = ['code' => 0, 'msg' => ''];
            $code =  $this->request->param('code');
            $phone =  $this->request->param('phone');
            try{
                \app\common\model\Sms::validVerify(2,$phone,$code);
            } catch (\Exception $e) {
                $res['msg'] = $e->getMessage();
                echo json_encode($res);
                die;
            }
            session('reg_info',['phone'=>$phone,'code'=>$code]);
            session('step',2);
            $res['code'] = 1;
            echo json_encode($res);
            die;
        }
        return view('register',['step'=>1,'content' =>\app\common\model\SysSetting::getContent('reg_protocol')]);
    }
    public function register2(){
        if(session('step') != 2){
            $this->redirect(url('Index/register'));
        }
        if($this->request->isAjax()) {
            try{
                $php_input = $this->request->param();
                $validate =new \app\common\validate\Users();
                $validate->scene('web_reg');
                $user_model = new \app\common\model\Users();
                //$user_model->changeInsert('name');
                $model = $user_model->handleReg($php_input,$validate);
            } catch (\Exception $e) {
                $res['msg'] = $e->getMessage();
                echo json_encode($res);
                die;
            }
            session('userinfo',[
                'uid' => $model['id'],
                'uname' => $model['name'],
                'face' => $model['face']
            ]);
            session('uid',$model['id']);
            session('reg_info',null);
            session('step',3);
            $res['code'] = 1;
            echo json_encode($res);
            die;
        }
        return view('register',['step'=>2]);
    }
    public function register3(){
        if(session('step') != 3){
            $this->redirect(url('Index/register2'));
        }
        session('step',null);
        return view('register',['step'=>3]);
    }
    public function mobile_register(){
        if($this->request->isAjax()) {
            $res = ['code' => 0, 'msg' => ''];
            $code =  $this->request->param('code');
            $phone =  $this->request->param('phone');
            try{
                \app\common\model\Sms::validVerify(2,$phone,$code);
                $php_input = $this->request->param();
                //$validate =new \app\common\validate\Users();
                //$validate->scene('web_reg');
                $user_model = new \app\common\model\Users();
                $model = $user_model->handleReg($php_input);
            } catch (\Exception $e) {
                $res['msg'] = $e->getMessage();
                echo json_encode($res);
                die;
            }
            session('userinfo',[
                'uid' => $model['id'],
                'uname' => $model['name'],
                'face' => $model['face']
            ]);
            session('uid',$model['id']);
            $res['code'] = 1;
            echo json_encode($res);die;
        }
    }
    //发送短信
    public function sendSms()
    {
        if($this->request->isAjax()) {
            $res = ['code' => 0, 'msg' => ''];
            $type =  $this->request->param('type');
            $phone =  $this->request->param('phone');
            try {
                \app\common\model\Sms::send($type, $phone);
            } catch (\Exception $e) {
                $res['msg'] = $e->getMessage();
                echo json_encode($res);
                die;
            }
            $res['code'] = 1;
            $res['msg'] = '发送成功';
            echo json_encode($res);
            die;
        }
    }
    public function phpinfo(){
        phpinfo();
    }
}