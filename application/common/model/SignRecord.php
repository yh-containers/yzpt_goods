<?php
namespace app\common\model;

use think\model\concern\SoftDelete;

class SignRecord extends BaseModel
{
    use SoftDelete;
    //数据库表名
    protected $table = 'sign_record';

    
}