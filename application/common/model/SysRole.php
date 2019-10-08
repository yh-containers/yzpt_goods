<?php
namespace app\common\model;

//管理员
use think\model\concern\SoftDelete;

class SysRole extends BaseModel
{
    use SoftDelete;
    //数据库表名
    protected $table = 'sys_role';



    //提供删除评论信息
    public function getDelIntro()
    {
        return '删除管理员角色,角色名:'.$this->getData('name');
    }
}