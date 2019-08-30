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
        $goods['today'] = $goods_model->where($today_where)->field('id,goods_image')->limit(10)->select();
        //新品推荐
        $goods['new'] = $goods_model->where(['status'=>1,'is_best'=>1])->field('id,goods_name,price,original_price,goods_image')->limit(10)->select();
        //banner下产品
        $goods['bg'] = $goods_model->where(['status'=>1,'tuijian'=>1])->field('id,goods_name,price,original_price,goods_image')->limit(6)->select();
        $bg_left = array();
        foreach($goods['bg'] as $k=>&$bgv){
            if($k==0) {
                $bg_left = $bgv;
                unset($bgv);
            }
        }
        $goods['bg_left'] = $bg_left;
        //特价
        $goods['special'] = $goods_model->where(['status'=>1,'is_special'=>1])->field('id,goods_image')->limit(10)->select();
        //人气
        $goods['hot'] = $goods_model->where(['status'=>1,'is_hot'=>1])->field('id,goods_image')->limit(10)->select();
        //print_r($today_where);
        //首页banner
        $banner = \app\common\model\Ad::where('type=2 and status=1')->field('url,img')->select();
        $tuijianCate = array();
        if(isMobile()){
            $banner = \app\common\model\Ad::where('type=5 and status=1')->field('url,img')->select();
            $tuijianCate = \app\common\model\GoodsCategory::where(['status'=>1,'pid'=>0])->field('id,cate_name,pid,icon,image')->order('sort asc')->limit(5)->select();
        }
        //分类下商品
        $cate_lists = \app\common\model\GoodsCategory::with(['linkChildCate'=>function($query){
            return $query->where(['status'=>1])->field('id,pid')->with('linkChildCate');
        }])->where(['status'=>1,'pid'=>0])->field('id,cate_name,image,desc,pid')->order('sort asc')->limit(3)->select();
        foreach ($cate_lists as &$val){
            $val['inids'] = $val['id'];
            foreach ($val['link_child_cate'] as $cvl){
                $val['inids'] .= ','.$cvl['id'];
                foreach ($cvl['link_child_cate'] as $ccvl){
                    $val['inids'] .= ','.$ccvl['id'];
                }
            }
            unset($val['link_child_cate']);
            $goods_list = $goods_model->where('status=1 and cate_id in('.$val['inids'].') and tuijian=1')->field('id,tuijian_img,goods_name')->limit(5)->select();
            $val['goods_list'] = $goods_list;
        }
        //print_r($cate_lists);
        return view('index',['goods'=>$goods,'banner'=>$banner,'isIndex'=>1,'cate_goods'=>$cate_lists,'tuijianCate'=>$tuijianCate]);
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
    //手机号登录
    public function mobile_login(){
        if($this->request->isAjax()) {
            $res = ['code' => 0, 'msg' => ''];
            $code =  $this->request->param('code');
            $phone =  $this->request->param('phone');
            try{
                //\app\common\model\Sms::validVerify(0,$phone,$code);
                $model = \app\common\model\Users::handleLogin($phone,$code,1);
            } catch (\Exception $e) {
                $res['msg'] = $e->getMessage();
                return json($res);
            }
            session('userinfo',[
                'uid' => $model['id'],
                'uname' => $model['name'],
                'face' => $model['face']
            ]);
            session('uid',$model['id']);
            $res['code'] = 1;
            return json($res);
        }
    }
    //退出
    public function loginout(){
        session('userinfo',null);
        session('uid',null);
        //$this->success("退出成功");
        $this->redirect('Index/index');
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
                if(session('auth_info')){
                    $auth_info = session('auth_info');
                    $user_model = new \app\common\model\Users();
                    $data['phone'] = $phone;
                    $data['password'] = 000000;
                    if($auth_info['mode']=='weibo'){
                        $data['wb_openid'] = $auth_info['uid'];
                    }elseif($auth_info['mode']=='wechat'){
                        $data['wx_openid'] = $auth_info['openid'];
                    }elseif ($auth_info['mode']=='qq'){
                        $data['qq_openid'] = $auth_info['openid'];
                    }
                    $model = $user_model->handleReg($data);
                    session('auth_info',null);
                    session('userinfo',[
                        'uid' => $model['id'],
                        'uname' => $model['name'],
                        'face' => $model['face']
                    ]);
                    session('uid',$model['id']);
                }
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
        if(session('uid')){
            session('reg_info',null);
            session('step',null);
            $this->redirect(url('Index/index'));
        }
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
        if(session('uid')){
            session('reg_info',null);
            session('step',null);
            $this->redirect(url('Index/index'));
        }
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

//第三方登录
    public function thirdLogin(){
        $mode = input('mode');
        $code = input('code');
        if(empty($code)){
            return '授权异常';
        }

        try{
            if($mode=='weibo'){
                $auth_info = \app\common\service\third\Weibo::codeToAct('web',$code);
                $where['wb_openid'] = $auth_info['uid'];
            }elseif($mode=='wechat'){
                $auth_info = \app\common\service\third\OpenWx::codeToAct('web',$code);
                $where['wx_openid'] = $auth_info['openid'];
            }elseif ($mode=='qq'){
                $auth_info = \app\common\service\third\QQ::codeToAct($code);
                $where['qq_openid'] = $auth_info['openid'];
            }
            $user_model = new \app\common\model\Users();
            $models = $user_model->where($where)->find();
            if($models['id']){
                session('userinfo',[
                    'uid' => $models['id'],
                    'uname' => $models['name'],
                    'face' => $models['face']
                ]);
                session('uid',$models['id']);
                $this->redirect(url('Index/index'));
            }else{
                $auth_info['mode'] = $mode;
                session('auth_info',$auth_info);
                $this->redirect(url('Index/register'));
            }
            //dump($auth_info);
        }catch (\Exception $e){
            return $e->getMessage();
        }
    }

//忘记密码
    public function forget_pwd(){
        if(session('uid')){
            $this->redirect(url('Index/index'));
        }
        //手机号 验证码验证
        if($this->request->isAjax()) {
            $res = ['code' => 0, 'msg' => ''];
            $code =  $this->request->param('code');
            $phone =  $this->request->param('phone');
            $password =  $this->request->param('password');
            $confirm_password =  $this->request->param('confirm_password');
            if($password != $confirm_password){
                $res['msg'] = '两次密码不一致';
                return json($res);
            }
            try{
                \app\common\model\Sms::validVerify(1,$phone,$code);
                $model = \app\common\model\Users::handleForget(['phone'=>$phone,'password'=>$password]);
            } catch (\Exception $e) {
                $res['msg'] = $e->getMessage();
                return json($res);
            }
            $res['code'] = 1;
            return json($res);
        }
        return view('forget_pwd');
    }
}