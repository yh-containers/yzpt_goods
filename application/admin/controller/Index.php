<?php
namespace app\admin\controller;

use \app\common\model\SysManager;
use think\Request;

class Index extends Common
{
    protected $need_login = true;
    protected $ignore_action=['Index','login'];

    public function index()
    {

        //平台总用户
        $user_count = (int)\app\common\model\Users::count('id');

        //用户总积分
        $user_raise_count = (int)\app\common\model\Users::sum('raise_num');

        return view('index',[
            'user_count'=>$user_count,
            'user_raise_count'=>$user_raise_count,
        ]);
    }

    //用户登录
    public function login(Request $request)
    {
        if ($request->isPost()){
            $data[] = input('post.');
            $account = input('account');
            $password = input('password');
            $verify = input('verify');

            if (!captcha_check($verify)) {
                $this->error('验证码错误');
            };
            if ($account == '') {
                $this->error('用户名不能为空');
            }
            if ($password == '') {
                $this->error('密码不能为空');
            }
            $model = SysManager::with('linkRole')->where('account',$account)->find();
            if (!$model) $this->error('请检查账号是否正确');
            $status = $model['status'];
            if ($status!=1) $this->error('该账号已停用');
            //判断密码是否正确

            if(SysManager::entryPwd($password,$model['salt'])===$model->password){
                session('user_info',[
                    'user_id' => $model['id'],
                    'rid' => $model['rid'],
                    'is_spe' => $model['is_spe'], //是否拥有特殊权限
                    'role_name' => $model['link_role']['name'],
                    'user_name' => $model['name'],
                ]);
                //最后一次登陆时间
                $model->last_time = time();
                //登陆ip
                $model->last_login_ip = $this->request->ip();
                //登陆次数
                $model->login_times = $model->login_times+1;
                $model->save();
                $this->success('登录成功','index/index');
            }
            $this->error('请检查密码是否正确');
        }

        return view('login',[

        ]);

    }

    //退出登录
    public function logout()
    {
        session(null);
        $this->success("退出成功", "index/login");
    }

}
