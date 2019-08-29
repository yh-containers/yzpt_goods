<?php
namespace app\index\controller;


use think\App;

class Protocol extends Common
{
    public function initialize()
    {
        parent::initialize();
        // 支持事先使用静态方法设置Request对象和Config对象
        $this->view->config('view_path', \think\facade\Env::get('app_path').'index/view/pc/');
    }
    public function index()
    {
        $content=\app\common\model\SysSetting::getContent('reg_protocol');
        return view('index',[
            'content'=>$content
        ]);
    }
}