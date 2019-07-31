<?php
namespace app\common\validate;

use think\Validate;

class Feedback extends Validate
{
    protected $rule = [
        'content'  => 'requireCallback:checkContent'
    ];

    protected $message  =   [
        'content.requireCallback' => '内容不能为空'

    ];

    //api忘记密码
    public function sceneApi_release()
    {
        return $this->only(['content','file'])
            ;
    }

    //验证内容
    protected function checkContent($value,$data=[])
    {
        if(empty($value) && empty($data['file'])){
            return true;
        }
        return false;
    }

}