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
        $list = \app\common\model\NewsCate::with(['linkChild'=>function($query){
            return $query->field('id,name,pid')->with('linkCateNews');
        }])->field('id')->where('name like "%商城文章%"')->find();

        $aid = $this->request->param('id');
        $data = \app\common\model\News::field('id,title,content')->get($aid);
        return view('index/news',['article'=>$list['link_child'],'data'=>$data]);
    }
}