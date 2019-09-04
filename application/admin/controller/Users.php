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

    public function add()
    {
        $id = input('id',0,'intval');

        if($this->request->isAjax()){
            $php_input = $this->request->param();
            if(empty($php_input['password']) && isset($php_input['password'])) unset($php_input['password']);

            $validate = new \app\common\validate\Users();
            $validate->scene('admin_opt');
            try{
                $model = new \app\common\model\Users();
                $model->actionAdd($php_input,$validate);//调用BaseModel中封装的添加/更新操作
            }catch (\Exception $e){
                return json(['code'=>0,'msg'=>$e->getMessage()]);
            }
            return json(['code'=>1,'msg'=>'操作成功']);
        }

        $model = \app\common\model\Users::get($id);

        return view('add',[
            'model'=>$model
        ]);
    }

    //用户详情
    public function detail()
    {
        $id = input('id',0,'intval');

        $model = \app\common\model\Users::get($id);

        return view('detail',[
            'model'=>$model
        ]);
    }
}
