<?php
namespace app\common\validate;

use think\Validate;

class News extends Validate
{
    //验证规则
    protected $rule = [
        'cid'        =>  'require',
        'title'      =>  'require',
        'author'     =>  'require',
        'from'       =>  'require',
        'img'        =>  'require',
        'intro'      =>  'require',
        'content'    =>  'require',
    ];

    //提示信息
    protected $message = [
        'cid.require'      => '请选择文章分类',
        'title.require'    => '标题必须输入',
        'author.require'   => '作者必须输入',
        'from.require'     => '来源必须输入',
        'img.require'      => '图片必须输入',
        'intro.require'    => '简介必须输入',
        'content.require'  => '内容必须输入',
    ];

}