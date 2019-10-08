<?php
namespace app\common\model;

//
use think\model\concern\SoftDelete;

class Ad extends BaseModel
{
    use SoftDelete;
    //数据库表名
    protected $name = 'ad';

    public static $fields_type = [
        ['name'=>'启动图'],
        ['name'=>'引导页'],
        ['name'=>'PC首页banner'],
        ['name'=>'活动推荐轮播'],
        ['name'=>'福利拓展'],
        ['name'=>'PE首页banner'],
    ];
    protected function getImgAttr($value)
    {
        return self::handleFile($value);
    }


    //提供删除评论信息
    public function getDelIntro()
    {
        return '删除【'.self::getPropInfo('fields_type',$this->getData('type'),'name').'】广告图';
    }

}