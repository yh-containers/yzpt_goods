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

    public function del()
    {
        $id = $this->request->param('id',0,'int');
        $model = new \app\common\model\UsersComplaint();
        return $model->actionDel(['id'=>$id]);
    }

    public function auth()
    {
        $id = input('id',0,'intval');
        $state = input('state',2,'intval');
        $content = input('content','','trim');

        try{
            \app\common\model\UsersComplaint::proAuth($id,$state,$content);
        }catch (\Exception $e){
            return $this->_resData(0,$e->getMessage());
        }
        return $this->_resData(1,'操作成功');
    }
}