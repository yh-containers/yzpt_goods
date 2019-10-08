<?php
namespace app\common\model;

//
use think\model\concern\SoftDelete;

class MchService extends BaseModel
{
    use SoftDelete;
    //数据库表名
    protected $table = 'mch_service';

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

    //提供删除评论信息
    public function getDelIntro()
    {
        return '删除商家服务:'.$this->getData('name');
    }
}