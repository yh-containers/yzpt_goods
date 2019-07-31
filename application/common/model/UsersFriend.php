<?php
namespace app\common\model;

use think\model\concern\SoftDelete;

class UsersFriend extends BaseModel
{
    use SoftDelete;
    protected $name='users_friend';


    public function linkUser()
    {
//        return $this->b
    }
}