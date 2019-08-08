<?php
namespace app\common\validate;

use think\Validate;

class Music extends Validate
{
    protected $rule = [
        'name'      => 'require',
        'author'    => 'require',
        'file'      => 'require',
    ];

    protected $message  =   [
        'name.require' => '请输入音乐名称',
        'author.require' => '请输入音乐作者',
        'file.require' => '请上传文件',
    ];

}