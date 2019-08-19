<?php
namespace app\common\validate;

use think\Validate;

class ActJoin extends Validate
{
    protected $rule = [
        'phone'=>'validPhone',
    ];

    protected $message  =   [
        'name.require' => '请输入用户名',
        'sex.require' => '请选择性别',
        'age.require' => '请输入年龄',
        'addr.require' => '请输入地址',
        'phone.require' => '手机号码必须输入',

    ];

    //api忘记密码
    public function sceneApi_join()
    {
        return $this->only(['name','sex','age','addr','phone'])
            ->append('name','require')
            ->append('sex','require')
            ->append('age','require')
            ->append('addr','require')
            ->append('phone','require')
            ;
    }
    
    protected function validPhone($value,$rule,$data=[])
    {
        if(!validPhone($value)){
            return '请输入正确的号码';
        }
        return true;
    }
}