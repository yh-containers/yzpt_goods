<?php
namespace app\index\controller;

use think\Controller;

class Common extends Controller
{
    protected $uid = 0;
    public function initialize()
    {

    }
    //分类
    protected function getCate(){
        $list = \app\common\model\GoodsCategory::with(['linkChildCate'=>function($query){
            return $query->where(['status'=>1])->field('id,cate_name,pid')->with('linkChildCate');
        }])->where(['status'=>1,'pid'=>0])->field('id,cate_name,pid')->order('sort asc')->select();
        return $list;
    }
    protected function breadcrumbs($cate_id = false){
        $res = ['sql_where'=>'','bread'=>''];
        if($cate_id){
            $bread = array();
            $ids = array();
            $cate_model = new \app\common\model\GoodsCategory();
            $cate = $cate_model->where(['id'=>$cate_id])->field('pid,cate_name,id')->find();
            $ids[] = $cate['id'];
            if($cate['pid']>0){
                $cate2 = $cate_model->with('SuperCate')->where('id='.$cate['pid'])->field('pid,cate_name,id')->find();
                $cate3 = array();
                foreach($cate2['super_cate'] as $vl){
                    if($vl['id']){
                        $cate3['id'] = $vl['id'];
                        $cate3['cate_name'] = $vl['cate_name'];
                    }
                }
                if($cate3){
                    $bread[0]['url'] = url('Goods/goods_list',['cate_id'=>$cate3['id']]);
                    $bread[0]['name'] = $cate3['cate_name'];
                    $bread[1]['url'] = url('Goods/goods_list',['cate_id'=>$cate2['id']]);
                    $bread[1]['name'] = $cate2['cate_name'];
                    $bread[2]['url'] = url('Goods/goods_list',['cate_id'=>$cate['id']]);
                    $bread[2]['name'] = $cate['cate_name'];
                }else{
                    $bread[0]['url'] = url('Goods/goods_list',['cate_id'=>$cate2['id']]);
                    $bread[0]['name'] = $cate2['cate_name'];
                    $bread[1]['url'] = url('Goods/goods_list',['cate_id'=>$cate['id']]);
                    $bread[1]['name'] = $cate['cate_name'];
                }
            }else{
                $bread[0]['url'] = url('Goods/goods_list',['cate_id'=>$cate['id']]);
                $bread[0]['name'] = $cate['cate_name'];
            }
            $child = $cate_model->with(['linkChildCate'=>function($query){
                return $query->where(['status'=>1])->field('id,pid')->with('linkChildCate');
            }])->where('id='.$cate_id)->field('id,pid')->select();
            foreach ($child as $cv){
                if($cv['link_child_cate']){
                    foreach ($cv['link_child_cate'] as $lcv){
                        $ids[] = $lcv['id'];
                        if($lcv['link_child_cate']){
                            foreach ($lcv['link_child_cate'] as $clcv) {
                                $ids[] = $clcv['id'];
                            }
                        }
                    }
                }
            }
            $res['bread'] = $bread;
            $res['sql_where'] = ' and cate_id in('.implode(',',$ids).')';
        }
        return $res;
    }
    protected function checklogin()
    {
        $this->uid = session('?userinfo')?session('userinfo.uid'):0;
        //验证登录
        if(empty($this->uid)){
            //未登录跳转登录页
            if($this->request->isAjax()){
                $this->error('请先登录','Index/login');exit;
            }else{
                $this->redirect('Index/login');
            }
        }
    }
}