<?php
namespace app\common\model;

class SysOptLogs extends BaseModel
{
    protected $table='sys_opt_logs';

    protected $autoWriteTimestamp = 'datetime';

    protected $json = ['info'];

    protected function getFileAttr($value)
    {
        return self::handleFile($value);
    }

    public static function record(BaseModel $opt_model,$uid,$intro=false)
    {
        if($intro!==false || method_exists($opt_model,'getDelIntro')){
            $file = null;
            if($opt_model instanceof Video){
                //视频资源
                $file = $opt_model->getData('file');
            }
            $model = new self();
            $model->data([
                'uid'=>empty($uid)?0:$uid,
                'tab_class'=>get_class($opt_model),
                'intro'=>$intro!==false ? $intro : $opt_model->getDelIntro(),
                'info'=>$opt_model->getData(),//操作数据
                'file'=>$file,
            ],true);
            $model->save();
        }

    }

    public function linkManager()
    {
        return $this->belongsTo('SysManager','uid');
    }

}