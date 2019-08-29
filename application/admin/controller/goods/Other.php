<?php
/**
 * Date: 2019/8/29
 * Time: 14:52
 */
namespace app\admin\controller\goods;

use app\admin\controller\Common;

class Other extends Common
{
    public function column_index(){
        $list = \app\common\model\BottomColumn::with('ownColumns')->where('pid=0')->order('create_time asc')->paginate();
//        print_r($list);
        $page = $list->render();
        return view('column_index',['list'=>$list,'page'=>$page]);
    }
    public function column_add(){
        $model = new \app\common\model\BottomColumn();
        //表单提交
        if($this->request->isAjax()){
            $php_input = $this->request->param();
            $validate = new \app\common\validate\BottomColumn();
            try{
                $model->actionAdd($php_input,$validate);
            }catch (\Exception $e){
                return json(['code'=>0,'msg'=>$e->getMessage()]);
            }
            return json(['code'=>1,'msg'=>'操作成功']);
        }
        $id  = $this->request->param('id');
        $data = $model->get($id);
        //获取主栏目
        $cate_list = \app\common\model\BottomColumn::where(['status'=>1,'pid'=>0])->order('create_time asc')->select();
        return view('column_add',[
            'model'=>$data,
            'column'=>$cate_list
        ]);
    }

    public function column_del(){
        $id = $this->request->param('id',0,'int');
        $model = new \app\common\model\BottomColumn();
        return $model->actionDel(['id'=>$id]);
    }

    public function search_index(){
        $list = \app\common\model\SearchKey::order('create_time asc')->select();
//        print_r($list);
//        $page = $list->render();
        return view('search_index',['list'=>$list]);
    }
    public function search_add(){
        $model = new \app\common\model\SearchKey();
        //表单提交
        if($this->request->isAjax()){
            $php_input = $this->request->param();
            try{
                $model->actionAdd($php_input);
            }catch (\Exception $e){
                return json(['code'=>0,'msg'=>$e->getMessage()]);
            }
            return json(['code'=>1,'msg'=>'操作成功']);
        }
    }
    public function search_del(){
        $id = $this->request->param('id',0,'int');
        $model = new \app\common\model\SearchKey();
        return $model->actionDel(['id'=>$id]);
    }
}