<?php
namespace app\common\model;

//
use think\model\concern\SoftDelete;

class Merchant extends BaseModel
{
    use SoftDelete;
    //数据库表名
    protected $table = 'merchant';

    protected function getImgAttr($value)
    {
        return self::handleFile($value);
    }

    protected function getLogoAttr($value)
    {
        return self::handleFile($value);
    }

    protected function setConsumeNumAttr($value,$data)
    {
        return (empty($value)||$value<=0) ? 0 : $value;
    }
}