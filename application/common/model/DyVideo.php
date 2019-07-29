<?php
namespace app\common\model;

use think\Validate;

class DyVideo extends BaseModel
{

    protected $name='dy_video';

    //设置视频有效时间
    protected function setDurationAttr($value)
    {
        return empty($value)?'00:00:00':sprintf('%02d:%02d:%02d',substr(intval($value/60/60*100),0,-2),intval($value/60%60),intval($value%60));
    }
}