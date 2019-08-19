<?php
namespace app\common\model;

class Welfare extends BaseModel
{

    protected $name='welfare';



    protected function getImgAttr($value)
    {
        return self::handleFile($value);
    }




}