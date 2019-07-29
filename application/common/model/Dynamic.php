<?php
namespace app\common\model;

use think\Validate;

class Dynamic extends BaseModel
{

    protected $name='dynamic';

    //设置视频有效时间
    protected function setDurationAttr($value)
    {
        return empty($value)?'00:00:00':sprintf('%2d:%2d:%2d',intval($value/60/60),intval($value/60),intval($value%60));
    }
}