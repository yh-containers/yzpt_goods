<?php
namespace app\common\model;

use think\Validate;

class Dynamic extends BaseModel
{

    protected $name='dynamic';

    //设置poi信息
    protected function setLocationPoiAttr($value)
    {
        if(!empty($value)){
//            exception($value);
            $poi = json_decode($value,true);

            $this->setAttr('coordinate',(isset($poi['lng']) && isset($poi['lat'])) ? ($poi['lng'].','. $poi['lat'] ):'');
            $this->setAttr('addr',isset($poi['address'])?$poi['address']:'');
        }
        return $value;
    }

    //设置视频有效时间
    protected function setDurationAttr($value)
    {
        return empty($value)?'00:00:00':sprintf('%2d:%2d:%2d',intval($value/60/60),intval($value/60),intval($value%60));
    }



}