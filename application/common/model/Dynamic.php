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

    //设置文件信息
    protected function setFileAttr($value)
    {
        $file = [];
        if(!empty($value)){
//            $value = '[{"key":"/uploads/dynamic/20190801/_52c85aba0108396cfb99d7e48e3d7646.jpg","fsize":3300075,"ext":"jpg","mime_type":"image/jpeg"}]';
            $value = json_decode($value,true);
//            dump( $value);exit;

            if(!isset($value[0])){
                //一维数组
                $value = [$value];
            }
//            exception(json_encode($value));
            $fsize =[];
            $mine_type = [];//文件类型
            $ext = [];//文件后缀
//            dump($value);
            foreach ($value as $vo){
//                dump($vo);
                $key = isset($vo['key'])?$vo['key']:'';//文件地址
//                dump($key);exit;
                array_push($file,$key);
                array_push($fsize,isset($vo['fsize'])?$vo['fsize']:0);
                array_push($mine_type,isset($vo['mime_type'])?$vo['mime_type']:'');
                array_push($ext,isset($vo['ext'])?$vo['ext']:'');
            }
            $this->setAttr('size',implode(',',$fsize));
            $this->setAttr('ext',implode(',',$ext));
            $this->setAttr('mine_type',implode(',',$mine_type));
        }
//        dump($file);exit;
//        exception(implode(',',$file));
        return implode(',',$file);
    }

    //设置视频有效时间
//    protected function setDurationAttr($value)
//    {
//        return empty($value)?'00:00:00':sprintf('%2d:%2d:%2d',intval($value/60/60),intval($value/60),intval($value%60));
//    }



}