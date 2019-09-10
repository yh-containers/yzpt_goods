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


//    protected function setDurationAttr($value)
//    {
//        return empty($value)?'00:00:00':sprintf('%2d:%2d:%2d',intval($value/60/60),intval($value/60),intval($value%60));
//    }


    public static function init()
    {
        parent::init();

        self::event('after_update',function($model){
            $model->handleMusicFile();
        });

        self::event('after_insert',function($model){
            $model->handleMusicFile();
        });

    }

    //保存音频到本地
    public function handleMusicFile()
    {
        save_music($this->file);


    }
}