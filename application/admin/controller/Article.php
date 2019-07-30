<?php
namespace app\admin\controller;

class Article extends Common
{
    public function news()
    {
        $keyword = input('keyword','','trim');
        $where=[];
        !empty($keyword) && $where[]=['title','like','%'.$keyword.'%'];
        $list = \app\common\model\News::where($where)->with('linkCate')->paginate();
        //dump($list);die;
        // 获取分页显示
        $page = $list->render();
        return view('news',[
            'keyword' => $keyword,
            'list' => $list,
            'page'=>$page
        ]);
    }

    //添加文章
    public function newsAdd()
    {

        $id = $this->request->param('id');
        $model = new \app\common\model\News();

        //表单提交
        if($this->request->isAjax()){
            $php_input = $this->request->param();//获取当前请求的参数
            $validate = new \app\common\validate\News();
            return $model->actionAdd($php_input,$validate);//调用BaseModel中封装的添加/更新操作
        }
        $model = $model->get($id);
        //获取分类
        $nav = \app\common\model\NewsCate::with(['linkChild'=>function($query){
            return $query->where(['status'=>1])->order('sort asc');
        }])->where(['status'=>1,'pid'=>0])->order('sort asc')->select();
        return view('newsAdd',[
            'model'=>$model,
            'nav'=>$nav,
        ]);
    }

    //删除文章
    public function newsDel()
    {
        $id = $this->request->param('id',0,'int');
        $model = new \app\common\model\News();
        return $model->actionDel(['id'=>$id]);
    }

    public function newsCate()
    {
        $list = \app\common\model\NewsCate::with(['linkChild'=>function($query){
            return $query->order('sort asc');
        }])->where(['pid'=>0])->paginate();
        // 获取分页显示
        $page = $list->render();
        return view('newsCate',[
            'list' => $list,'page'=>$page
        ]);
    }

    //
    public function newsCateAdd()
    {
        $id = $this->request->param('id');
        $model = new \app\common\model\NewsCate();

        //表单提交
        if($this->request->isAjax()){
            $php_input = $this->request->param();
            if(empty($php_input['password']) && isset($php_input['password'])) unset($php_input['password']);

            $validate = new \app\common\validate\NewsCate();
            return $model->actionAdd($php_input,$validate);
        }

        $model = $model->get($id);
        $roles = \app\common\model\NewsCate::where(['status'=>1,'pid'=>0])->select();
        return view('newsCateAdd',[
            'model' => $model,
            'roles' => $roles,
        ]);

    }

    //删除数据
    public function newsCateDel()
    {
        $id = $this->request->param('id',0,'int');
        $model = new \app\common\model\NewsCate();
        return $model->actionDel(['id'=>$id]);
    }
}