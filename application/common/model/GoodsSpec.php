<?php
/**
 * Date: 2019/8/2
 * Time: 9:07
 */
namespace app\common\model;

use think\model\concern\SoftDelete;

class GoodsSpec extends BaseModel
{
    use SoftDelete;
    //public static $fields_state = ['关闭','正常'];
    //数据库表名
    protected $table = 'gd_spec';

    public function linkChild()
    {
        return $this->hasMany('GoodsSpec','pid')->order('sort asc');
    }


    //提供删除评论信息
    public function getDelIntro()
    {
        return '删除商品规格表:'.$this->getData('spec_name');
    }
}