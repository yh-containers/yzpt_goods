<?php
namespace app\common\model;

//
use think\model\concern\SoftDelete;

class NewsCate extends BaseModel
{
    use SoftDelete;
    //数据库表名
    protected $name = 'news_cate';



    public function linkCate()
    {
        return $this->belongsTo('NewsCate','pid');
    }
    public function linkCateNews()
    {
        return $this->hasMany('News','cid')->field('id,cid,title');
    }
    //
    public function linkChild()
    {
        return $this->hasMany('NewsCate','pid');
    }
}