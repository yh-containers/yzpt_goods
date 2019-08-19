<?php
/**
 * Date: 2019/8/12
 * Time: 16:52
 */
namespace app\common\model;

class OrderGoods extends BaseModel
{
    protected $table = 'gd_order_goods';
    protected function getImgAttr($value)
    {
        return self::handleFile($value);
    }
    public function createOrderGoods($goods_list,$order_id){
        empty($order_id) && exception('订单不存在');
        empty($goods_list) && exception('商品不存在');
        $cmodel = new \app\common\model\Cart();
        foreach ($goods_list as $goods){
            $og = array();
            $og['oid'] = $order_id;
            $og['gid'] = $goods['gid'];
            $og['price'] = $goods['price'];
            $og['num'] = $goods['num'];
            $og['img'] = $goods['goods_image'];
            $og['name'] = $goods['goods_name'];
            $og['extra'] = 'sku_id:'.$goods['sid'];
            $model = new self();
            $model->data($og);
            $model->save();
            $cmodel->where(['id'=>$goods['id']])->delete();
        }
    }
}