<?php
namespace app\common\model;

class UsersNoInterest extends BaseModel
{

    protected $name='Users_no_interest';

    public static $fields_type = [
        ['name'=>'视频'],
        ['name'=>'动态'],
        ['name'=>'活动'],
    ];


}