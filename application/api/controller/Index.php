<?php
namespace app\api\controller;



class Index extends Common
{
    public function index()
    {
        abort(1,'abc');
        dump(config(''));
        echo 123;
    }

    //用户登录
    public function login()
    {
        $account = input('account');
        $mode_str = input('mode_str');
        $mode = input('mode',0,'intval');
        try{
            $model = \app\common\model\Users::handleLogin($account,$mode_str,$mode);
        }catch (\Exception $e){
            return $this->_resData(0,$e->getMessage());
        }

        return $this->_resData(1,'登录成功',[
            'user_token' => $model->generateUserToken(),
        ]);
    }

    //用户注册
    public function reg()
    {

        try{
            $input_data = input();
            $validate =new \app\common\validate\Users();
            $validate->scene('api_reg');
            \app\common\model\Users::handleReg($input_data,$validate);
        }catch (\Exception $e){
            return $this->_resData(0,$e->getMessage());
        }

        return $this->_resData(1,'注册成功');
    }

    //发送短信
    public function sendSms()
    {
        $type = input('type');
        $phone = input('phone');
        try{
            \app\common\model\Sms::send($type,$phone);
        }catch (\Exception $e){
            return $this->_resData(0,$e->getMessage());
        }
        return $this->_resData(1,'发送成功');
    }

    //忘记密码
    public function forget()
    {
        try{
            $input_data = input();
            $validate =new \app\common\validate\Users();
            $validate->scene('api_forget');
            \app\common\model\Users::handleForget($input_data,$validate);
        }catch (\Exception $e){
            return $this->_resData(0,$e->getMessage());
        }

        return $this->_resData(1,'找回成功');
    }

    //系统默认标签
    public function sysLabel()
    {
        $sys_label = \app\common\model\SysSetting::getContent('sys_label');
        $sys_label = empty($sys_label)?[]:explode(',',$sys_label);
        $data = [];
        foreach ($sys_label as $vo){
            $data[] =[
                'name' => $vo,
                'is_active' => 0
            ];
        }
        return $this->_resData(1,'获取成功',$data);
    }

    //用户
    public function searchUsers()
    {
        $keyword = input('keyword','','trim');
        empty($keyword) && abort(0,'请输入关键字');

        $data = \app\common\model\Users::field('id,name,face,py')
                ->where([['name','like','%'.$keyword.'%'],['status','=',1]])->select()->toArray();
        return $this->_resData(1,'获取成功',$data);
    }

    //反馈
    public function feedback()
    {
        try{
            $input_data = input();
            $input_data['uid'] = $this->user_id;
            $validate =new \app\common\validate\Feedback();
            $validate->scene('api_release');
            $model = new \app\common\model\Feedback();
            $model->actionAdd($input_data,$validate);
        }catch (\Exception $e){
            return $this->_resData(0,$e->getMessage());
        }
        return $this->_resData(1,'感谢您的反馈，我们会尽快处理');
    }
}