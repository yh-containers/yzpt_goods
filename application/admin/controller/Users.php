<?php
namespace app\admin\controller;



class Users extends Common
{

    public function index()
    {
        $keyword = input('keyword','','trim');
        $where=[];
        !empty($keyword) && $where[]=['name|phone','like','%'.$keyword.'%'];
        $list = \app\common\model\Users::where($where)->paginate();
        // 获取分页显示
        $page = $list->render();
        return view('index',[
            'keyword' => $keyword,
            'list' => $list,
            'page'=>$page
        ]);
    }

}
