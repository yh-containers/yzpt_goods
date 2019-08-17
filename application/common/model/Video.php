<?php
namespace app\common\model;


use think\model\concern\SoftDelete;

class Video extends BaseModel
{
    use SoftDelete;
    protected $name='video';

    protected $insert = ['uid'];

    protected function setUidAttr($value,$data)
    {
        return empty($value)?1:(int)$value;
    }

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


        $data=[
            'type'=>'video',
            'data'=>[
                [
                    'file' => self::handleFile($this->file),
                    'img' => self::handleFile($this->getData('img')),
                ]
            ]
        ];
        return $data;
    }

    protected function getImgAttr($value)
    {
        return self::handleFile($value);
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
        $ex_model = self::get($data['id']);
        empty($ex_model) && exception('对象异常:null');

        $model = VideoPraise::where(['uid'=>$user_model->id,'vid'=>$data['id']])->find();
        if(empty($model)){
            $model = new VideoPraise();
        }

        $model->uid = $user_model->id;
        $model->vid = $data['id'];
        $model->praise_date = empty($model->praise_date)?date('Y-m-d H:i:s'):null;
        $model->save();
        //点赞次数增加
        $model->praise_date?$ex_model->setInc('praise_times'):$ex_model->setDec('praise_times');

        return $model;
    }


    /**
     * 评论
     * @param Users $user_model;
     * @param array $data;
     * @throws
     * @return VideoComment
     * */
    public static function addComment(Users $user_model,array $data=[])
    {
        empty($data['content']) && exception('内容不能为空');
        empty($data['id']) && exception('对象异常:id');

        $model = new VideoComment();
        $model->uid = $user_model->id;
        $model->vid = $data['id'];
        $model->to_uid = empty($data['to_uid'])?0:$data['to_uid'];
        $model->pid = empty($data['pid'])?0:$data['pid'];
        $model->content = $data['content'];
        $model->save();
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
        return $this->hasOne('VideoComment','vid')->field('vid,count(*) as comment_count')->group('vid');
    }

    //是否点过赞
    public function linkIsPraise()
    {
        return $this->hasOne('VideoPraise','vid')->whereNotNull('praise_date');
    }
}