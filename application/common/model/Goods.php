<?php
/**
 * Date: 2019/8/2
 * Time: 10:08
 */
namespace app\common\model;

use think\model\concern\SoftDelete;

class Goods extends BaseModel
{
    use SoftDelete;
    public static $fields_status = ['','是','否'];
    public static $fields_hot = ['','是','否'];
    public static $fields_best = ['','是','否'];
    public static $fields_special = ['','是','否'];
    //数据库表名
    protected $table = 'gd_goods';
    public function getGoodsImageAttr($value){
        return self::handleFile($value);
    }
    public function getImageArrAttr($value){
        $list = empty($value)?[]:explode(',',$value);
        foreach ($list as &$vo){
            $vo = self::handleFile($vo);
        }
        return $list;
    }
    public function ownSpecValue()
    {
        return $this->hasMany('GoodsSpecValue','goods_id')->order('id asc');
    }
}