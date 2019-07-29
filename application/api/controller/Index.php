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
}