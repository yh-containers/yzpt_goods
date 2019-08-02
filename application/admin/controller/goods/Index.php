<?php
namespace app\admin\controller\goods;

use app\admin\controller\Common;

class Index extends Common
{
    //商品列表
    public function index()
    {
        $list = \app\common\model\Goods::with('ownClass')->order('sort asc')->paginate();
        $page = $list->render();
        return view('index',['list'=>$list,'page'=>$page]);
    }
    //商品的新增/编辑
    public function goodsAdd(){
        $id  = $this->request->param('id');
        $model = new \app\common\model\Goods();
        $data = $model->get($id);
        //表单提交
        if($this->request->isAjax()){
            $php_input = $this->request->param();
            $validate = new \app\common\validate\Goods();
            return $model->actionAdd($php_input,$validate);
        }
        //分类
        $cate_list = \app\common\model\GoodsCategory::with(['linkChildCate'=>function($query){
            return $query->where(['state'=>1])->with('linkChildCate');
        }])->where(['state'=>1,'pid'=>0])->order('sort asc')->select();
        //规格属性
        $spec_list = \app\common\model\GoodsSpec::with(['linkChild'=>function($query){
            return $query->where([]);
        }])->where(['pid'=>0])->order('sort asc')->select();
        return view('goods_add',[
            'model'=>$data,
            'cate'=>$cate_list,
            'spec'=>$spec_list
        ]);
    }
    //分类
    public function category(){
        $where = ['pid'=>0];
        $list = \app\common\model\GoodsCategory::with(['linkChildCate'=>function($query){
            return $query->where(['state'=>1])->with('linkChildCate');
        }])->where($where)->order('sort asc')->paginate();
        $page = $list->render();
        return view('category',['list'=>$list,'page'=>$page]);
    }
    //分类添加编辑
    public function cateAdd(){
        $id  = $this->request->param('id');
        $model = new \app\common\model\GoodsCategory();
        $data = $model->get($id);
        //表单提交
        if($this->request->isAjax()){
            $php_input = $this->request->param();
            $validate = new \app\common\validate\GoodsCategory();
            return $model->actionAdd($php_input,$validate);
        }
        //获取分类
        $cate_list = \app\common\model\GoodsCategory::with(['linkChildCate'=>function($query){
            return $query->where(['state'=>1]);
        }])->where(['state'=>1,'pid'=>0])->order('sort asc')->select();
        return view('cate_add',[
            'model'=>$data,
            'cate'=>$cate_list
        ]);
    }
    //分类删除
    public function cateDel()
    {
        $id = $this->request->param('id',0,'int');
        $model = new \app\common\model\GoodsCategory();
        return $model->actionDel(['id'=>$id]);
    }
    //规格属性
    public function goods_spec(){
        $where = ['pid'=>0];
        $list = \app\common\model\GoodsSpec::with(['linkChild'=>function($query){
            return $query->where([]);
        }])->where($where)->order('sort asc')->paginate();
        $page = $list->render();
        return view('goods_spec',['list'=>$list,'page'=>$page]);
    }
    //规格属性添加编辑
    public function specAdd(){
        $id  = $this->request->param('id');
        $model = new \app\common\model\GoodsSpec();
        $data = $model->get($id);
        //表单提交
        if($this->request->isAjax()){
            $php_input = $this->request->param();
            $validate = new \app\common\validate\GoodsSpec();
            return $model->actionAdd($php_input,$validate);
        }
        //获取属性
        $cate_list = \app\common\model\GoodsSpec::where(['pid'=>0])->order('sort asc')->select();
        return view('spec_add',[
            'model'=>$data,
            'cate'=>$cate_list
        ]);
    }
    //规格删除
    public function specDel()
    {
        $id = $this->request->param('id',0,'int');
        $model = new \app\common\model\GoodsSpec();
        return $model->actionDel(['id'=>$id]);
    }
    public function test()
    {
        return 'goods/test';
    }
}