<?php
namespace app\common\model;

use think\Validate;

class Dynamic extends BaseModel
{

    protected $name='dynamic';

    public static $fields_status =[
        ['name'=>'公开'],
        ['name'=>'私密'],
        ['name'=>'部分人可见'],
        ['name'=>'部分人不可见'],
    ];

    //发布时间
    protected function getReleaseDateAttr()
    {
        return $this->create_time;
    }
    protected function getFileAttr($value){
        return empty($value)?[]:explode(',',$value);
    }

    //发布的文件组合
    protected function getFileGroupAttr()
    {
        $data = ['type'=>'','data'=>[]];
        $file = $this->file;
//        $file = empty($file)?[]:explode(',',$file);
        $ext = $this->ext;
        $ext = empty($ext)?[]:explode(',',$ext);
        foreach ($file as $key=>$vo){
            empty($data['type']) && $data['type']=isset($ext[$key])?(self::checkImg($ext[$key])?'image':'video'):'image';
            array_push($data['data'],[
                'file' => self::handleFile($vo),
                'ext'  => isset($ext[$key])?$ext[$key]:'image',
            ]);
        }
//        $size = $this->size;
//        $mine_type = $this->mine_type;

        return $data;
    }


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
//    protected function setFileAttr($value)
//    {
////        $file = [];
////        if(!empty($value)){
//////            $value = '[{"key":"/uploads/dynamic/20190801/_52c85aba0108396cfb99d7e48e3d7646.jpg","fsize":3300075,"ext":"jpg","mime_type":"image/jpeg"}]';
////            $value = json_decode($value,true);
//////            dump( $value);exit;
////
////            if(!isset($value[0])){
////                //一维数组
////                $value = [$value];
////            }
//////            exception(json_encode($value));
////            $fsize =[];
////            $mine_type = [];//文件类型
////            $ext = [];//文件后缀
//////            dump($value);
////            foreach ($value as $vo){
//////                dump($vo);
////                $key = isset($vo['key'])?$vo['key']:'';//文件地址
//////                dump($key);exit;
////                array_push($file,$key);
////                array_push($fsize,isset($vo['fsize'])?$vo['fsize']:0);
////                array_push($mine_type,isset($vo['mime_type'])?$vo['mime_type']:'');
////                array_push($ext,isset($vo['ext'])?$vo['ext']:'');
////            }
////            $this->setAttr('size',implode(',',$fsize));
////            $this->setAttr('ext',implode(',',$ext));
////            $this->setAttr('mine_type',implode(',',$mine_type));
////        }
//////        dump($file);exit;
//////        exception(implode(',',$file));
//        return implode(',',$file);
//    }

    //设置视频有效时间
//    protected function setDurationAttr($value)
//    {
//        return empty($value)?'00:00:00':sprintf('%2d:%2d:%2d',intval($value/60/60),intval($value/60),intval($value%60));
//    }

    /**
     * 评论
     * @param Users $user_model;
     * @param array $data;
     * @throws
     * @return DyComment
     * */
    public static function addComment(Users $user_model,array $data=[])
    {
        empty($data['content']) && exception('内容不能为空');
        empty($data['id']) && exception('对象异常:id');

        $model = new DyComment();
        $model->uid = $user_model->id;
        $model->dy_id = $data['id'];
        $model->to_uid = empty($data['to_uid'])?0:$data['to_uid'];
        $model->pid = empty($data['pid'])?0:$data['pid'];
        $model->content = $data['content'];
        $model->save();
        return $model;
    }


    /**
     * 评论
     * @param Users $user_model;
     * @param array $data;
     * @throws
     * @return DyPraise
     * */
    public static function praise(Users $user_model,array $data=[])
    {
        empty($data['id']) && exception('对象异常:id');
        $dy_model = self::get($data['id']);
        empty($dy_model) && exception('动态异常');

        $model = DyPraise::where(['uid'=>$user_model->id,'dy_id'=>$data['id']])->find();
        if(empty($model)){
            $model = new DyPraise();
        }

        $model->uid = $user_model->id;
        $model->dy_id = $data['id'];
        $model->praise_date = empty($model->praise_date)?date('Y-m-d H:i:s'):null;
        $model->save();
        //点赞次数增加
        $model->praise_date?$dy_model->setInc('praise_times'):$dy_model->setDec('praise_times');

        return $model;
    }




    //动态用户
    public function linkUsers()
    {
        return $this->belongsTo('Users','uid');
    }

    //动态评论
    public function linkCommentCount()
    {
        return $this->hasOne('DyComment','dy_id')->field('dy_id,count(*) as comment_count')->group('dy_id');
    }

    //是否点过赞
    public function linkIsPraise()
    {
        return $this->hasOne('DyPraise','dy_id')->whereNotNull('praise_date');
    }


}