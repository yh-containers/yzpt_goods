<?php
namespace app\admin\controller;


class Complaint extends Common
{
    public function index()
    {
        $keyword = input('keyword','','trim');
        $where=[];
        !empty($keyword) && $where[]=['title','like','%'.$keyword.'%'];
        $list = \app\common\model\UsersComplaint::where($where)->with('linkUsers')->order('id desc')->paginate();
        //dump($list);die;
        // 获取分页显示
        $page = $list->render();
        return view('index',[
            'keyword' => $keyword,
            'list' => $list,
            'page'=>$page
        ]);
    }
}