<?php
namespace app\common\validate;

use think\Validate;

class Users extends Validate
{
    protected $rule = [
        'phone'  => 'validPhone',
        'password'  => 'min:6',
        'raise_num'=>'egt:0',
        'money'=>'egt:0',
    ];
    protected $message  =   [
        'name.require'=>'用户昵称不能为空',
        'phone.require'=>'手机号码不能为空',
        'phone.validPhone'=>'手机号码异常',
        'password.require'=>'密码不能为空',
        'password.min'=>'密码不能低于6位',
        'password.confirm'=>'两次密码不一致',
        'password.regex'=>'密码只能为数字+字母',
        'verify.require'=>'验证码不能为空',
        'raise_num.gt'=>'养分不得低于:rule',
        'money.gt'=>'金额不得低于:rule',
    ];

    //api注册
    public function sceneApi_reg()
    {
        return $this->only(['password','phone','verify'])
            ->append('password', 'require')
            ->append('phone', 'require|checkExist:2')
            ->append('verify', 'require|checkVerify:2')
            ;
    }
    //api注册
    public function sceneAdmin_opt()
    {
        return $this->only(['name','password','phone','raise_num','money'])
            ->append('name', 'require')
            ->append('phone', 'require|checkExist:3')
            ;
    }

    //api忘记密码
    public function sceneApi_forget()
    {
        return $this->only(['password','phone','verify'])
            ->append('password', 'require|confirm:re_password')
            ->append('phone', 'require|checkExist:1')
            ->append('verify', 'require|checkVerify:1')
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
        }elseif($rule==3){
            //管理员修改手机号
            if(isset($data['id'])){
                if( $model['id']!=$data['id']){
                    return '手机号已被注册';
                }
            }elseif(!empty($model)){
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