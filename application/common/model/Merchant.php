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

    //提供删除评论信息
    public function getDelIntro()
    {
        return '删除商家:'.$this->getData('name');
    }
}