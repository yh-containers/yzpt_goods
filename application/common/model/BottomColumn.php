<?php
/**
 * Date: 2019/8/29
 * Time: 14:49
 */
namespace app\common\model;
use think\model\concern\SoftDelete;
class BottomColumn extends BaseModel
{
    use SoftDelete;
    protected $table = 'gd_bottom_column';
    public static $fields_status = ['','开启','关闭'];
    public function ownColumns()
    {
        return $this->hasMany('BottomColumn','pid')->order('id asc');
    }
}