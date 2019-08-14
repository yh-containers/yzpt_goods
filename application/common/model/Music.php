<?php
namespace app\common\model;

use think\model\concern\SoftDelete;
use think\Validate;

class Music extends BaseModel
{

    use SoftDelete;
    protected $name='music';


    protected function getFileAttr($value)
    {
        return self::handleFile($value);
    }


    protected function setDurationAttr($value)
    {
        return empty($value)?'00:00:00':sprintf('%2d:%2d:%2d',intval($value/60/60),intval($value/60),intval($value%60));
    }

}