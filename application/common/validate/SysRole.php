<?php
namespace app\common\validate;

use think\Validate;

class SysRole extends Validate
{
    //验证规则
    protected $rule = [
        'name'      =>  'require',
    ];
    //提示信息
    protected $message = [
        'name.require'      => '用户名必须输入',
    ];

    //场景验证Admin_add中验证
    public function sceneAdmin_add_proxy()
    {
        return $this->only(['name','account','password']);
    }


    public function checkRequire($value, $data)
    {
        if(empty($data['id'])){
            return true;
        }
    }

    //自定义验证规则
    public function checkAccount($value, $rule,$data)
    {
        $model = new \app\common\model\SysManager();
        $model = $model->where('account',$value);
        if(!empty($data['id'])){
            $model =$model->where('id','neq',$data['id']);
        }

        if($model->count()){
            return '帐号已存在';
        }
        return true;
    }
}