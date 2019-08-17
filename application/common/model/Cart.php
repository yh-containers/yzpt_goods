<?php
/**
 * Date: 2019/8/8
 * Time: 17:53
 */
namespace app\common\model;
class Cart extends BaseModel
{
    protected $table = 'gd_user_cart';

    //相同购物车合并
    public function checkCart($data){
        empty($data['uid']) && exception('请先登录');
        empty($data['gid']) && exception('加购的商品不存在');
        if($data['sid']){
            $gmodel = new \app\common\model\GoodsSpecStock();
            $stock = $gmodel->where(['id'=>$data['sid']])->sum('stock');
        }else{
            $gmodel = new \app\common\model\Goods();
            $stock = $gmodel->where(['id'=>$data['gid']])->sum('stock');
        }
        empty($stock) && exception('商品库存不足');
        ($stock < $data['num']) && exception('商品库存不足');

        $where['gid'] = $data['gid'];
        $where['uid'] = $data['uid'];
        if($data['sid']) $where['sid'] = $data['sid'];
        if($res = self::where($where)->find()){
            $info['id'] = $res['id'];
            $info['is_checked'] = $data['is_checked'];
            $info['num'] = $res['num']+$data['num'];//array(self::raw('num+'.$data['num']));
            return $info;
        }else{
            return $data;
        }
    }
    //检测商品状态及库存
    public function checkCartGoods(){
        $cart_list = self::alias('c')->leftJoin(['gd_goods'=>'g'],'c.gid=g.id')->where('c.uid='.session('uid').' and c.is_checked=1')->field('c.id,c.gid,c.uid,c.sid,c.num,g.goods_name,g.goods_image,g.price,g.stock,g.status')->select();
        empty($cart_list) && exception('未选中商品');
        $total = 0;
        $gmodel = new \app\common\model\GoodsSpecStock();
        foreach ($cart_list as &$cart){
            if($cart['sid']){
                $sku_attr = $gmodel->field('stock,price')->find($cart['sid']);
                $cart['stock'] = $sku_attr['stock'];
                $cart['price'] = $sku_attr['price'];
                if($sku_attr['stock'] > $cart['num']){
                    $gmodel->where(['id'=>$cart['sid']])->update(['stock'=>$gmodel->raw('stock-'.$cart['num'])]);
                }
            }else{
                $gmodel = new \app\common\model\Goods();
                if($cart['stock'] > $cart['num']){
                    $gmodel->where(['id'=>$cart['sid']])->update(['stock'=>$gmodel->raw('stock-'.$cart['num'])]);
                }
            }
            empty($cart['stock']) && exception('商品库存不足');
            ($cart['stock']<$cart['num']) && exception('商品库存不足');
            ($cart['status'] != 1) && exception('商品库存不足');
            $total += $cart['price']*$cart['num'];
        }
        return ['goods_list'=>$cart_list,'total'=>$total];
    }
}