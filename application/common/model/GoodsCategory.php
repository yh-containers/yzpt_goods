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
    protected $deleteTime = 'delete_time';
    //数据库表名
    protected $table = 'gd_category';
    public function cateChild()
    {
        return $this->hasMany('GoodsCategory','pid');
    }
    public function linkChildCate()
    {
        return $this->hasMany('GoodsCategory','pid');
    }
}