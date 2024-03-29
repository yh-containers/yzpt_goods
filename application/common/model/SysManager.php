<?php
namespace app\common\model;

//管理员
use think\model\concern\SoftDelete;

class SysManager extends BaseModel
{
    use SoftDelete;
    //数据库表名
    protected $table = 'sys_manager';

    public function setPasswordAttr($value)
    {
        $salt = rand(10000,99999);
        $this->setAttr('salt',$salt);
        return self::entryPwd($value,$salt);
    }

    //加密
    public static function entryPwd($value,$salt)
    {
        return md5($salt.md5($value.$salt).$value);
    }

    //角色
    public function linkRole()
    {
        return $this->belongsTo('SysRole','rid');
    }


    //提供删除评论信息
    public function getDelIntro()
    {
        return '删除管理员:'.$this->getData('name');
    }
}