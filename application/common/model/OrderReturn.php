<?php
/**
 * Date: 2019/9/3
 * Time: 10:54
 */
namespace app\common\model;
class OrderReturn extends BaseModel
{
    protected $table = 'gd_order_return';
    public static $fields_state = ['新申请退货款','商家同意申请','用户货品寄出','商家拒绝'];
    public function ownUser()
    {
        return $this->hasOne('Users','uid')->order('id asc');
    }
    public function ownOrder()
    {
        return $this->hasOne('Order','oid')->order('id asc');
    }
    public function getImageAttr($value){
        $list = empty($value)?[]:explode(',',$value);
        foreach ($list as &$vo){
            $vo = self::handleFile($vo);
        }
        return $list;
    }
}