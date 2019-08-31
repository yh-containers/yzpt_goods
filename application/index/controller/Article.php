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
        $columnModel = new \app\common\model\BottomColumn();
        $list = $columnModel->with(['ownColumns'=>function($query){
            return $query->where('status=1');
        }])->where('status=1 and pid=0')->select();
        $aid = $this->request->param('id');
        $search = $this->request->param('search');
        $data = array();
        if($aid){
            $data = $columnModel->field('id,name,content,pid')->get($aid);
        }else{
            $data = $columnModel->where('name like "'.$search.'"')->field('id,name,content,pid')->find();
        }
        $nk = 0;
        $acname = '';
        foreach ($list as $k=>$v){
            if($v['id'] == $data['pid']){
                $nk = $k;
                $acname = $v['name'];
            }
        }
        return view('index/news',['article'=>$list,'data'=>$data,'nk'=>$nk,'acname'=>$acname]);
    }
}