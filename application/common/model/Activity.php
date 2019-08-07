<?php
namespace app\common\model;

use think\Validate;

class Activity extends BaseModel
{

    protected $name='activity';

    //发布时间
    protected function getReleaseDateAttr()
    {
        return $this->create_time;
    }

    protected function getImgAttr($value)
    {
        return self::handleFile($value);
    }

    //动态用户
    public function linkUsers()
    {
        return $this->belongsTo('Users','uid');
    }

    //动态评论
    public function linkCommentCount()
    {
        return $this->hasOne('ActComment','aid')->field('aid,count(*) as comment_count')->group('aid');
    }

    //是否点过赞
    public function linkIsPraise()
    {
        return $this->hasOne('ActPraise','aid')->whereNotNull('praise_date');
    }

    //参加人
    public function linkJoinCount()
    {
        return $this->hasOne('ActJoin','aid')->field('aid,count(*) as join_count')->group('aid');
    }

}