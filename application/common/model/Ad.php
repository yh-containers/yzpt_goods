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
        ['name'=>'首页banner'],
        ['name'=>'活动推荐轮播'],
        ['name'=>'福利拓展'],
    ];
    protected function getImgAttr($value)
    {
        return self::handleFile($value);
    }

}