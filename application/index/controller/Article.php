<?php
/**
 * Date: 2019/8/28
 * Time: 16:02
 */
namespace app\index\controller;

use \app\common\model\SysManager;
use think\Request;

class Article extends Common
{
    public function index(){
        $list = \app\common\model\BottomColumn::with(['ownColumns'=>function($query){
            return $query->where('status=1');
        }])->where('status=1 and pid=0')->select();

        $aid = $this->request->param('id');
        $data = \app\common\model\BottomColumn::field('id,name,content')->get($aid);
        return view('index/news',['article'=>$list,'data'=>$data]);
    }
}