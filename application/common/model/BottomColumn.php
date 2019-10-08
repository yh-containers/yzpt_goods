<?php
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


    //提供删除评论信息
    public function getDelIntro()
    {
        return '删除底部数据';
    }
}