<?php
namespace app\common\validate;

use think\Validate;

class Users extends Validate
{
    protected $rule = [
        'password'  => 'min:6'
    ];
    protected $message  =   [
        'phone.require'=>'手机号码不能为空',
        'phone.validPhone'=>'手机号码异常',
        'password.require'=>'密码不能为空',
        'password.min'=>'密码不能低于6位',
        'password.confirm'=>'两次密码不一致',
        'password.regex'=>'密码只能为数字+字母',
        'verify.require'=>'验证码不能为空',
    ];

    //api注册
    public function sceneApi_reg()
    {
        return $this->only(['password','phone','verify'])
            ->append('password', 'require')
            ->append('verify', 'require')
            ->append('account', 'require')
            ->append('phone', 'require|validPhone|checkExist:2')
            ->append('account','checkExist')

            ->append('verify', 'checkVerify')
            ;
    }

    //api忘记密码
    public function sceneApi_forget()
    {
        return $this->only(['password','phone','verify'])
            ->append('password', 'require|confirm:re_password')
            ->append('verify', 'require')
            ->append('account', 'require')
            ->append('phone', 'require|validPhone|checkExist:1')
            ->append('account','checkExist')

            ->append('verify', 'checkVerify')
            ;
    }

    protected function checkExist($value,$rule,$data=[])
    {
        $model = \app\common\model\Users::where(['phone'=>$value])->find();

        if($rule==1){
            if(empty($model)){
                return '手机号未注册';
            }
        }elseif($rule==2){
            if(!empty($model)){
                return '手机号已被注册';
            }
        }

        return true;
    }

    protected function validPhone($value,$rule,$data=[])
    {
        if(!validPhone($value)){
            return '请输入正确的号码';
        }
        return true;
    }

    //app注册验证
    protected function checkVerify($value,$rule,$data=[])
    {
        try{
            \app\common\model\Sms::validVerify($rule,$data['phone'],$value);
        }catch (\Exception $e){
            return $e->getMessage();
        }
        return true;
    }
}