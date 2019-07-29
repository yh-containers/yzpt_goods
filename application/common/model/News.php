<?php
namespace app\common\model;

//
use think\model\concern\SoftDelete;

class News extends BaseModel
{
    use SoftDelete;
    //数据库表名
    protected $name = 'news';



    public function linkCate()
    {
        return $this->belongsTo('NewsCate','cid');
    }
}