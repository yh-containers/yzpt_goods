<?php
namespace app\index\controller;

use \app\index\controller\Common;
use think\Request;

class Goods extends Common
{
    public function index(){}
    //商品列表
    public function goods_list(){
        $cate_id  = $this->request->param('cate_id');
        $get_lift  = $this->request->param('lift');
        $sort  = $this->request->param('sort');
        if(empty($sort)) $sort = 'sort';
        if($get_lift == 'desc'){
            $lift = 'asc';
        }else{
            $lift = 'desc';
        }
        if($sort == 'sales') $sort = 'sort';//销量排序后续处理
        //获取面包屑导航及分类查询条件
        $bread = $this->breadcrumbs($cate_id);
        $sql_where = 'status=1';
        if($bread['sql_where']){
            $sql_where .= $bread['sql_where'];
        }
        $goods_model = new \app\common\model\Goods();
        $goods_list = $goods_model->where($sql_where)->field('id,goods_name,goods_image,price,original_price')->order($sort.' '.$lift)->paginate();
        $page = $goods_list->render();
        $tz_url = url('Goods/goods_list');
        if($cate_id){
            $tz_url .= '?cate_id='.$cate_id;
        }
        return view('goods_list',['goods_list'=>$goods_list,'bread'=>$bread['bread'],'page'=>$page,'auto_url'=>$tz_url,'lift'=>$lift,'sort'=>$sort]);
    }
    //分类
    public function cate(){
        $cate_model = new \app\common\model\GoodsCategory();
        $list = $cate_model->where(['status'=>1,'pid'=>0])->field('id,cate_name')->order('sort asc')->select();
        $cate_id  = $this->request->param('cate_id');
        if(!$cate_id) $cate_id = $list[0]['id'];
        $child_cate = $cate_model->with(['linkChildCate'=>function($query){
            return $query->where(['status'=>1])->field('id,cate_name,pid')->with('linkChildCate');
        }])->where(['id'=>$cate_id])->field('id')->find();
        //print_r($child_cate);
        return view('cate',['cate_list'=>$list,'child_cate'=>$child_cate['link_child_cate'],'cate_id'=>$cate_id]);
    }
    //商品详情
    public function detail(){
        $id  = $this->request->param('id');
        $goods_model = new \app\common\model\Goods();
        $data = $goods_model->with('ownSpecValue')->get($id);
        if($data['status'] != 1){
            $this->error('商品未上架');
        }
        $isok = 0;
        foreach ($data['own_spec_value'] as $v){
            if($v) $isok = 1;
        }
        if($isok == 0) $data['own_spec_value']=0;

        $data['image_arr'] = explode(',',$data['image_arr']);
        $bread = $this->breadcrumbs($data['cate_id']);
        //猜你喜欢
        $sql_where = 'status=1 and id not in('.$id.')';
        if($bread['sql_where']){
            $sql_where .= $bread['sql_where'];
        }
        $goods_list = $goods_model->where($sql_where)->field('id,goods_name,goods_image,price')->limit(4)->select();
        //商品规格属性
        $spec_model = new \app\common\model\GoodsSpecValue();
        $spec_count = $spec_model->where('goods_id='.$id)->count();
        $sku = array();
        if($spec_count){
            $spec_list = \app\common\model\GoodsSpec::where(['pid'=>$data['spec_id']])->field('id,spec_name')->select();
            foreach ($spec_list as $pk=>$spec){
                $sku[$pk]['spec_name'] = $spec['spec_name'];
                foreach ($data['own_spec_value'] as $sk=>$val){
                    if($val['spec_id']==$spec['id']){
                        $sku[$pk]['spec_val'][$sk]['id'] = $val['id'];
                        $sku[$pk]['spec_val'][$sk]['value_name'] = $val['value_name'];
                    }
                }
            }
        }
        //收藏
        $collect_model = new \app\common\model\Collect();
        $collect_count = $collect_model->where(['gid'=>$id])->count();
        return view('goods_detail',['goods'=>$data,'bread'=>$bread['bread'],'like_list'=>$goods_list,'sku_arr'=>$sku,'collect_count'=>$collect_count]);
    }
    //查询属性价格库存
    public function search_sku(){
        if($this->request->isAjax()) {
            $res = ['err'=>1,'price'=>'','stock'=>''];
            $goods_id = $this->request->param('goods_id');
            $sv_ids = $this->request->param('sv_ids');
            $sv_ids = trim($sv_ids,',');
            $addSpecStock = new \app\common\model\GoodsSpecStock();
            $sql_where = 'goods_id='.$goods_id.' and (';
            $arr = explode(',',$sv_ids);
            for ($i=0;$i<count($arr);$i++){
                shuffle($arr);
                $sql_where .= 'sv_ids="'.implode(',',$arr).'" or ';
            }
            $sql_where = rtrim($sql_where,'or ');
            $sql_where .= ')';
            //echo $sql_where;die;
            $sku = $addSpecStock->where($sql_where)->field('id,stock,price')->find();
            if($sku['id']){
                $res['err'] = 0;
                $res['price'] = $sku['price'];
                $res['stock'] = $sku['stock'];
                $res['id'] = $sku['id'];
            }
            echo json_encode($res);die;
        }
    }
    //加入收藏
    public function collect(){
        if($this->request->isAjax()) {
            $res = ['code' => 0, 'msg' => ''];
            if(empty(session('uid'))){
                $res['msg'] = '请先登录';
                echo json_encode($res);die;
            }
            $col_model = new \app\common\model\Collect();
            $post_data = $this->request->param();
            $post_data['uid'] = session('uid');
            $col = $col_model->where($post_data)->field('id')->find();
            if($col['id']){
                $col_model->actionDel(['id'=>$col['id']]);
                $res['msg'] = '已移除收藏';
            }else{
                $post_data['col_time'] = date('Y-m-d H:i:s');
                $col_model->actionAdd($post_data);
                $res['msg'] = '已收藏';
            }
            $res['code'] = 1;
            echo json_encode($res);die;
        }
    }
    //加入购物车
    public function addcart(){
        if($this->request->isAjax()) {
            $res = ['code'=>0,'msg'=>''];
            if(empty(session('uid'))){
                $res['msg'] = '请先登录';
                echo json_encode($res);die;
            }
            $post_data = $this->request->param();
            $post_data['uid'] = session('uid');
            try{
                $cart_model = new \app\common\model\Cart();
                $post_data = $cart_model->checkCart($post_data);
                $cart_model->actionAdd($post_data);
            } catch (\Exception $e) {
                $res['msg'] = $e->getMessage();
                echo json_encode($res);die;
            }
            echo json_encode(['code'=>1,'msg'=>'已加入购物车']);die;
        }
    }
}