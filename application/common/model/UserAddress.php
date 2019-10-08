<?php
/**
 * Date: 2019/8/9
 * Time: 17:56
 */
namespace app\common\model;
use think\model\concern\SoftDelete;
class UserAddress extends BaseModel
{
    use SoftDelete;
    protected $table = 'gd_user_addr';



    //提供删除评论信息
    public function getDelIntro()
    {
        return '删除用户地址';
    }
}