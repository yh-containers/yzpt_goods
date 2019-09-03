<?php
/**
 * Date: 2019/9/3
 * Time: 10:54
 */
namespace app\common\model;
class OrderReturn extends BaseModel
{
    protected $table = 'gd_order_return';
    public function ownUser()
    {
        return $this->hasOne('Users','uid')->order('id asc');
    }
    public function getImageAttr($value){
        $list = empty($value)?[]:explode(',',$value);
        foreach ($list as &$vo){
            $vo = self::handleFile($vo);
        }
        return $list;
    }
}