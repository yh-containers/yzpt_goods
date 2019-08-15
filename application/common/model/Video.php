<?php
namespace app\common\model;


class Video extends BaseModel
{

    protected $name='video';

    //发布时间
    protected function getReleaseDateAttr()
    {
        return $this->create_time;
    }
    //设置视频有效时间
    protected function setDurationAttr($value)
    {
        return empty($value)?'00:00:00':sprintf('%02d:%02d:%02d',substr(intval($value/60/60*100),0,-2),intval($value/60%60),intval($value%60));
    }

    //发布的文件组合
    protected function getFileGroupAttr()
    {
        $data = ['type'=>'','data'=>[]];
        $file = $this->file;
        $file = empty($file)?[]:explode(',',$file);
        $ext = $this->ext;
        $ext = empty($ext)?[]:explode(',',$ext);
        foreach ($file as $key=>$vo){
            empty($data['type']) && $data['type']=self::checkImg($ext[$key])?'image':'video';
            array_push($data['data'],[
                'file' => self::handleFile($vo),
                'ext'  => isset($ext[$key])?$ext[$key]:'',
            ]);
        }
//        $size = $this->size;
//        $mine_type = $this->mine_type;

        return $data;
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