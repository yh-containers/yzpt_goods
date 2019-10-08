<?php
namespace app\common\model;

class ActComment extends BaseModel
{

    protected $name='act_comment';

    //提供删除评论信息
    public function getDelIntro()
    {
        return '删除评论数据';
    }

}