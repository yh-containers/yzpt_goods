<?php
/**
 * Date: 2019/8/9
 * Time: 10:53
 */
namespace app\common\model;
use think\model\concern\SoftDelete;
class Collect extends BaseModel
{
    use SoftDelete;
    protected $table = 'gd_user_col';
    public function getGoodsImageAttr($value){
        return self::handleFile($value);
    }
}