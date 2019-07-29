<?php
namespace app\common\validate;

use think\Validate;

class DyVideo extends Validate
{
    protected $rule = [
        'file'  => 'require',
        'size'  => 'require|gt:0',
        'mime_type'  => 'require',
    ];

    protected $message  =   [
        'file.require' => '请上传视频',
        'size.require' => '文件大小异常',
        'size.gt'      => '文件大小异常',
        'mime_type.require' => '视频类型异常',
    ];


    public function sceneApi_release()
    {
        return $this->only(['file','size','mime_type']);
    }
}