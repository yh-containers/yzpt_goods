<?php
/**
 * Date: 2019/8/1
 * Time: 10:52
 */
namespace app\common\model;

use think\model\concern\SoftDelete;

class GoodsCategory extends BaseModel
{
    use SoftDelete;
    public static $fields_status = ['','正常','关闭'];
    //数据库表名
    protected $table = 'gd_category';
    public function getImageAttr($value){
        return self::handleFile($value);
    }
    public function getIconAttr($value){
        return self::handleFile($value);
    }
    public function linkChildCate()
    {
        return $this->hasMany('GoodsCategory','pid')->order('sort asc');
    }
    public function SuperCate()
    {
        return $this->hasMany('GoodsCategory','id','pid')->field('pid,cate_name,id')->order('sort asc');
    }
}