<?php
namespace app\admin\controller\goods;

use app\admin\controller\Common;

class Index extends Common
{
    //商品列表
    public function index()
    {
        $list = \app\common\model\Goods::order('sort asc')->paginate();
        foreach ($list as &$v){
            $cate_name = \app\common\model\GoodsCategory::field('cate_name')->get($v['cate_id']);
            $v['cate_name'] = $cate_name['cate_name'];
        }
        $page = $list->render();
        return view('index',['list'=>$list,'page'=>$page]);
    }
    //商品的新增/编辑
    public function goodsAdd(){
        $id  = $this->request->param('id');
        $model = new \app\common\model\Goods();
        $data = $model->with('ownSpecValue')->get($id);
        //表单提交
        if($this->request->isAjax()){
            $php_input = $this->request->param();
            $php_input['image_arr'] = implode(',',$php_input['image_arr']);
            $php_input['special_time'] = implode(',',$php_input['special_time']);
            $validate = new \app\common\validate\Goods();
            //商品属性值
            if($php_input['value_name']){
                $spec['value_name'] = $php_input['value_name'];
                unset($php_input['value_name']);
                $spec['value_price'] = $php_input['value_price'];
                unset($php_input['value_price']);
                $spec['value_id'] = $php_input['value_id'];
                unset($php_input['value_id']);
            }
            $model->actionAdd($php_input,$validate);
            $goods_id = $php_input['id'];
            if(empty($php_input['id'])){
                $goods_id = $model->getLastInsID();
            }
            foreach ($spec['value_name'] as $sid=>$v){
                foreach ($v as $k=>$sv){
                    $specArr = array();
                    $specArr['id'] = $spec['value_id'][$sid][$k];
                    $specArr['spec_id'] = $sid;
                    $specArr['goods_id'] = $goods_id;
                    $specArr['value_name'] = $spec['value_name'][$sid][$k];
                    $specArr['value_price'] = $spec['value_price'][$sid][$k];
                    //if(empty($specArr['id'])) unset($specArr['id']);
                    $spec_model = new \app\common\model\GoodsSpecValue();
                    $spec_model->actionAdd($specArr);
                }
            }
            echo '';die;
        }
        $data['image_arr'] = explode(',',$data['image_arr']);
        $data['special_time'] = explode(',',$data['special_time']);
        //分类
        $cate_list = \app\common\model\GoodsCategory::with(['linkChildCate'=>function($query){
            return $query->where(['status'=>1])->with('linkChildCate');
        }])->where(['status'=>1,'pid'=>0])->order('sort asc')->select();
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
    //商品的属性库存
    public function spec_stock(){
        $id  = $this->request->param('id');
        $model = new \app\common\model\Goods();
        $data = $model->field('goods_name,spec_id')->get($id);
        if(empty($data['goods_name'])){
            $this->error('商品不存在');
        }
        if($this->request->isAjax()){
            $php_input = $this->request->param();
            foreach ($php_input['stock'] as $k=>$v){
                if($v){
                    $addInfo = [];
                    $addInfo['stock'] = $v;
                    $addInfo['sv_ids'] = $php_input['sv_ids'][$k];
                    $addInfo['goods_id'] = $php_input['goods_id'][$k];
                    $addInfo['id'] = $php_input['sid'][$k];
                    //if(!$addInfo['id']) unset($addInfo['id']);
                    $addSpecStock = new \app\common\model\GoodsSpecStock();
                    $addSpecStock->actionAdd($addInfo);
                }
            }
            echo '';die;
        }
        $spec_list = \app\common\model\GoodsSpec::where(['pid'=>$data['spec_id']])->field('id')->select();
        $newSpec = [];
        $j = 0;
        //foreach ($spec_list as $k=>$v){
            $arr1 = \app\common\model\GoodsSpecValue::where('spec_id='.$spec_list[0]['id'].' and goods_id='.$id)->field('id,value_name')->select();
            $arr2 = [];
            //if(1){
            $arr2 = \app\common\model\GoodsSpecValue::where('spec_id='.$spec_list[1]['id'].' and goods_id='.$id)->field('id,value_name')->select();
            //}
            $specStock = new \app\common\model\GoodsSpecStock();
            if($arr1 && $arr2){
                foreach ($arr1 as $k1=>$v1){
                    foreach ($arr2 as $k2=>$v2){
                        $newSpec[$j]['sv_ids'] = $v1['id'] .','.$v2['id'];
                        $newSpec[$j]['goods_id'] = $id;
                        $stocks = $specStock->where($newSpec[$j])->field('id,stock')->find();
                        if($stocks){
                            $newSpec[$j]['id'] = $stocks['id'];
                            $newSpec[$j]['stock'] = $stocks['stock'];
                        }else{
                            $newSpec[$j]['id'] = '0';
                            $newSpec[$j]['stock'] = '';
                        }
                        $newSpec[$j]['name'] = $v1['value_name'] .' + '.$v2['value_name'];
                        $j++;
                    }
                }
            }
        //}
        return view('sepc_stock',['id'=>$id,'stocks'=>$newSpec]);
    }
    //商品删除
    public function goodsDel()
    {
        $id = $this->request->param('id',0,'int');
        $model = new \app\common\model\Goods();
        $data = $model->field('goods_image,image_arr')->get($id);
        unlink($data['goods_image']);
        $image_arr = explode(',',$data['image_arr']);
        foreach ($image_arr as $iv){
            unlink($iv);
        }
        return $model->actionDel(['id'=>$id]);
    }
    //分类
    public function category(){
        $where = ['pid'=>0];
        $list = \app\common\model\GoodsCategory::with(['linkChildCate'=>function($query){
            return $query->where(['status'=>1])->with('linkChildCate');
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
            return $query->where(['status'=>1]);
        }])->where(['status'=>1,'pid'=>0])->order('sort asc')->select();
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