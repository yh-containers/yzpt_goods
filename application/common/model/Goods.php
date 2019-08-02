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
    public static $fields_state = ['否','是'];
    public static $fields_hot = ['否','是'];
    public static $fields_best = ['否','是'];
    public static $fields_special = ['否','是'];
    //数据库表名
    protected $table = 'gd_goods';
    public function ownClass()
    {
        return $this->hasMany('GoodsCategory','id')->field('cate_name')->order('sort asc');
    }
}