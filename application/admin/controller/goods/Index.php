<?php
namespace app\admin\controller\goods;

use app\admin\controller\Common;

class Index extends Common
{
    public function index()
    {
        return view('index');
    }
    //分类
    public function category(){
        $where = ['pid'=>0];
        $list = \app\common\model\GoodsCategory::with(['cateChild'=>function($query){
            return $query->where(['state'=>1])->order('addtime asc')->with('linkChildCate');
        }])->where($where)->paginate();
        //print_r($list);//['linkChildCate'=>function($query){ return $query->where(['state'=>1])->order('addtime asc');}]
        $page = $list->render();
        return view('category',array('list'=>$list,'page'=>$page));
    }
    //分类添加编辑
    public function cateAdd(){
        $id  = $this->request->param('id');
        $model = new \app\common\model\GoodsCategory();
        $data = $model->get($id);
        //表单提交
        if($this->request->isAjax()){
            $php_input = $this->request->param();
            if(empty($php_input['id'])){
                $php_input['addtime'] = time();
            }
            $validate = new \app\common\validate\GoodsCategory();
            return $model->actionAdd($php_input,$validate);
        }
        //获取分类
        $cate_list = \app\common\model\GoodsCategory::with(['cateChild'=>function($query){
            return $query->where(['state'=>1])->order('addtime asc');
        }])->where(['state'=>1,'pid'=>0])->order('addtime asc')->select();
        return view('cate_add',array('model'=>$data,'cate'=>$cate_list));
    }
    public function test()
    {
        return 'goods/test';
    }
}