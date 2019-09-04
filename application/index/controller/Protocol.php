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
        $type = input('type','','trim');
        $protocol_type = 'reg_protocol';
        if($type=='active'){
            //活动协议
            $protocol_type = 'act_protocol';
        }elseif ($type=='secret'){
            //隐私政策
            $protocol_type = 'secret_protocol';
        }

        $content=\app\common\model\SysSetting::getContent($protocol_type);
        return view('index',[
            'content'=>$content
        ]);
    }
}