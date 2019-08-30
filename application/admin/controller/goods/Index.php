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
//        dump($data->getData('goods_image'));exit;
        //dump($data);exit;
        if(!empty($data)){
            $isok = 0;
            foreach ($data['own_spec_value'] as $v){
                if($v) $isok = 1;
            }
            if($isok == 0) $data['own_spec_value']=0;
        }
        //表单提交
        if($this->request->isAjax()){
            $php_input = $this->request->param();
            $image_arr = $this->request->param('image_arr');
            $sku = $this->request->param('sku');
            $new_sku = $this->request->param('new_sku');
            $spec['value_name'] = $this->request->param('value_name');
            $spec['value_id'] = $this->request->param('value_id');
            //$php_input['goods_image'] = isset($image_arr[0])?$image_arr[0]:'';
            $php_input['image_arr'] = implode(',',$php_input['image_arr']);
            $validate = new \app\common\validate\Goods();
            //商品属性
//            if($php_input['value_name']){
//                unset($php_input['value_name']);
//                unset($php_input['value_id']);
//            }
            $model->actionAdd($php_input,$validate);
            $goods_id = $php_input['id'];
            if(empty($php_input['id'])){
                $goods_id = $model->id;
            }
            $svIdsArr = array();
            if($spec['value_name']) {
                foreach ($spec['value_name'] as $sid => $v) {
                    if($v){
                        foreach ($v as $k => $sv) {
                            $specArr = array();
                            $specArr['id'] = $spec['value_id'][$sid][$k];
                            $specArr['spec_id'] = $sid;
                            $specArr['goods_id'] = $goods_id;
                            $specArr['value_name'] = $spec['value_name'][$sid][$k];
                            $spec_model = new \app\common\model\GoodsSpecValue();
                            $spec_model->actionAdd($specArr);
                            $svIdsArr[$specArr['value_name']] = $specArr['id'];
                            if (empty($specArr['id'])) {
                                $svIdsArr[$specArr['value_name']] = $spec_model->id;
                            }
                        }
                    }
                }
            }
            if($sku){
                if($sku['price'] || $sku['stock']){
                    foreach ($sku['price'] as $sk=>$row){
                        $upinfo = array();
                        $upinfo['price'] = $row;
                        $upinfo['stock'] = $sku['stock'][$sk];
                        $upinfo['id'] = $sku['id'][$sk];
                        $addSpecStock = new \app\common\model\GoodsSpecStock();
                        $addSpecStock->actionAdd($upinfo);
                    }
                }
            }
            if($new_sku) {
                if ($new_sku['price'] || $new_sku['stock']) {
                    foreach ($new_sku['price'] as $sk1 => $sku_price) {
                        $addSku = array();
                        $addSku['goods_id'] = $goods_id;
                        $addSku['price'] = $sku_price;
                        $addSku['stock'] = $new_sku['stock'][$sk1];
                        $nameArr = explode('|', $new_sku['name'][$sk1]);
                        $addSku['sv_ids'] = array();
                        foreach ($nameArr as $nk => $name) {
                            $addSku['sv_ids'][$nk] = $svIdsArr[trim($name)];
                        }
                        $addSku['sv_ids'] = implode(',', $addSku['sv_ids']);
                        $addSpecStockModel = new \app\common\model\GoodsSpecStock();
                        $addSpecStockModel->actionAdd($addSku);
                    }
                }
            }
            return json(['code'=>1,'msg'=>'操作成功']);
        }
//        if(empty($data['image_arr'])) $data['image_arr'] = explode(',',$data['image_arr']);
        $image_arr_str = !empty($data) ?$data->getData('image_arr'):'';
        $image_arr = empty($image_arr_str)?[]:explode(',',$image_arr_str);

        //分类
        $cate_list = \app\common\model\GoodsCategory::with(['linkChildCate'=>function($query){
            return $query->where(['status'=>1])->with('linkChildCate');
        }])->where(['status'=>1,'pid'=>0])->order('sort asc')->select();
        //规格属性
        $spec_list = \app\common\model\GoodsSpec::with(['linkChild'=>function($query){
            return $query->where([]);
        }])->where(['pid'=>0])->order('sort asc')->select();
        $sku_list = array();
        if($id){
            $sku_list  = \app\common\model\GoodsSpecStock::where('goods_id='.$id)->field('id,sv_ids,stock,price')->select();
            if($sku_list){
                $specModel = new \app\common\model\GoodsSpecValue();
                foreach ($sku_list as &$skv){
                    $spv = $specModel->where('id in('.$skv['sv_ids'].') and goods_id='.$id)->field('value_name')->select();
                    $skv['name'] = '';
                    foreach ($spv as $res){
                        $skv['name'] .= $res['value_name'].' | ';
                    }
                    $skv['name'] = trim( $skv['name'],' | ');
                }
            }
        }
        return view('goods_add',[
            'model'=>$data,
            'image_arr'=>$image_arr,
            'cate'=>$cate_list,
            'spec'=>$spec_list,
            'sku'=>$sku_list
        ]);
    }
    //商品删除
    public function goodsDel()
    {
        $id = $this->request->param('id',0,'int');
        $model = new \app\common\model\Goods();
//        $data = $model->field('goods_image,image_arr')->get($id);
//        @unlink($data['goods_image']);
//        $image_arr = explode(',',$data['image_arr']);
//        foreach ($image_arr as $iv){
//            @unlink($iv);
//        }
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
            try{
                $model->actionAdd($php_input,$validate);//调用BaseModel中封装的添加/更新操作
            }catch (\Exception $e){
                return json(['code'=>0,'msg'=>$e->getMessage()]);
            }
            return json(['code'=>1,'msg'=>'操作成功']);
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
            try{
                $model->actionAdd($php_input,$validate);//调用BaseModel中封装的添加/更新操作
            }catch (\Exception $e){
                return json(['code'=>0,'msg'=>$e->getMessage()]);
            }
            return json(['code'=>1,'msg'=>'操作成功']);
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