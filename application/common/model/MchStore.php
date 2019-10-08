<?php
namespace app\common\model;

//
use think\model\concern\SoftDelete;

class MchStore extends BaseModel
{
    use SoftDelete;
    //数据库表名
    protected $table = 'mch_store';

    protected function getImgAttr($value)
    {
        return self::handleFile($value);
    }

    protected function setConsumeNumAttr($value,$data)
    {
        return (empty($value)||$value<=0) ? 0 : $value;
    }

    public function linkMerchant()
    {
        return $this->belongsTo('Merchant','mch_id');
    }
    public function linkMchService()
    {
        return $this->belongsTo('MchService','ser_id');
    }

    //提供删除评论信息
    public function getDelIntro()
    {
        return '删除门店:'.$this->getData('name');
    }
}