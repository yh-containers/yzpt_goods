<?php
namespace app\common\validate;

use think\Validate;

class Dynamic extends Validate
{
    protected $rule = [
        'content'  => 'requireCallback:checkContent',
        'file'  => 'checkFile'
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

    //验证文件
    protected function checkFile($value,$rule,$data=[])
    {
        if($value && (!isset($data['mine_type']) || isset($data['size']))){
            return '文件上传参数错误:type-size';
        }
        return true;
    }
}