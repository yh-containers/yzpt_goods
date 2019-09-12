<?php
namespace app\admin\controller;

use \app\common\model\SysManager;
use think\Request;

class Merchant extends Common
{


    //设置
    public function index()
    {
        $keyword = input('keyword','');
        $where=[];
        !empty($keyword) && $where[]=['name|up_name|up_phone','like','%'.$keyword.'%'];
        $list = \app\common\model\Merchant::where($where)->order('sort asc')->paginate();
        // 获取分页显示
        $page = $list->render();
        return view('index',[
            'keyword' => $keyword,
            'list' => $list,
            'page'=>$page
        ]);
    }

    //新增/编辑
    public function add()
    {
        $id = $this->request->param('id');
        $model = new \app\common\model\Merchant();

        //表单提交
        if($this->request->isAjax()){
            $php_input = $this->request->param();
            $validate = new \app\common\validate\Merchant();
            try{

                $model->actionAdd($php_input,$validate);//调用BaseModel中封装的添加/更新操作
            }catch (\Exception $e){
                return json(['code'=>0,'msg'=>$e->getMessage()]);
            }
            return json(['code'=>1,'msg'=>'操作成功']);
        }
        $model = $model->get($id);

        return view('add',[
            'model' => $model,

        ]);

    }

    //删除数据
    public function del()
    {
        $id = $this->request->param('id',0,'int');
        $model = new \app\common\model\MchService();
        return $model->actionDel(['id'=>$id]);
    }
    //设置
    public function service()
    {
        $keyword = input('keyword','');
        $where=[];
        !empty($keyword) && $where[]=['name','like','%'.$keyword.'%'];
        $list = \app\common\model\MchService::where($where)->order('sort asc')->paginate();
        // 获取分页显示
        $page = $list->render();
        return view('service',[
            'keyword' => $keyword,
            'list' => $list,
            'page'=>$page
        ]);
    }

    //新增/编辑
    public function serviceAdd()
    {
        $id = $this->request->param('id');
        $model = new \app\common\model\MchService();

        //表单提交
        if($this->request->isAjax()){
            $php_input = $this->request->param();
            $validate = new \app\common\validate\MchService();
            try{

                $model->actionAdd($php_input,$validate);//调用BaseModel中封装的添加/更新操作
            }catch (\Exception $e){
                return json(['code'=>0,'msg'=>$e->getMessage()]);
            }
            return json(['code'=>1,'msg'=>'操作成功']);
        }
        $model = $model->get($id);

        return view('serviceAdd',[
            'model' => $model,

        ]);

    }

    //删除数据
    public function serviceDel()
    {
        $id = $this->request->param('id',0,'int');
        $model = new \app\common\model\MchService();
        return $model->actionDel(['id'=>$id]);
    }


    //轮播图设置
    public function store()
    {
        $mch_id = $this->request->param('mch_id',0,'intval');
        $ser_id = $this->request->param('ser_id',0,'intval');

        $keyword = input('keyword','','trim');
        $where=[];
        $mch_id && $where[]=['mch_id','=',$mch_id];
        $ser_id && $where[]=['ser_id','=',$ser_id];
        !empty($keyword) && $where[]=['name|up_name|up_phone','like','%'.$keyword.'%'];
        $list = \app\common\model\MchStore::with(['linkMchService'])->where($where)->order('sort asc')->paginate();
        // 获取分页显示
        $page = $list->render();
        return view('store',[
            'keyword' => $keyword,
            'list' => $list,
            'page'=>$page,
            'mch_id'=>$mch_id,
            'ser_id'=>$ser_id,
        ]);
    }

    //轮播图新增/编辑
    public function storeAdd()
    {
        $mch_id = $this->request->param('mch_id',0,'intval');
        $ser_id = $this->request->param('ser_id',0,'intval');

        $id = $this->request->param('id');
        $model = new \app\common\model\MchStore();

        //表单提交
        if($this->request->isAjax()){
            $php_input = $this->request->post();
            $validate = new \app\common\validate\MchStore();
            try{

                $model->actionAdd($php_input,$validate);//调用BaseModel中封装的添加/更新操作
            }catch (\Exception $e){
                return json(['code'=>0,'msg'=>$e->getMessage()]);
            }
            return json(['code'=>1,'msg'=>'操作成功']);
        }
        $model = $model->get($id);
        $mch_service = \app\common\model\MchService::where(['status'=>1])->order('sort asc')->select();
        return view('storeAdd',[
            'model' => $model,
            'mch_service' => $mch_service,
            'mch_id' => $mch_id,
            'ser_id' => $ser_id,

        ]);

    }

    //删除数据
    public function storeDel()
    {
        $id = $this->request->param('id',0,'int');
        $model = new \app\common\model\MchStore();
        return $model->actionDel(['id'=>$id]);
    }



}
