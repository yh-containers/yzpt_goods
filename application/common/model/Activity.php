<?php
namespace app\common\model;

use think\Validate;

class Activity extends BaseModel
{

    protected $name='activity';

    //发布时间
    protected function getReleaseDateAttr()
    {
        return $this->create_time;
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
     * @return ActComment
     * */
    public static function addComment(Users $user_model,array $data=[])
    {
        empty($data['content']) && exception('内容不能为空');
        empty($data['id']) && exception('对象异常:id');

        $model = new ActComment();
        $model->uid = $user_model->id;
        $model->aid = $data['id'];
        $model->to_uid = empty($data['to_uid'])?0:$data['to_uid'];
        $model->content = $data['content'];
        $model->save();
        return $model;
    }


    /**
     * 评论
     * @param Users $user_model;
     * @param array $data;
     * @throws
     * @return ActComment
     * */
    public static function praise(Users $user_model,array $data=[])
    {
        empty($data['id']) && exception('对象异常:id');
        $model = ActComment::where(['uid'=>$user_model->id,'aid'=>$data['id']])->find();
        if(empty($model)){
            $model = new ActComment();
        }

        $model->uid = $user_model->id;
        $model->aid = $data['id'];
        $model->praise_date = empty($model->praise_date)?date('Y-m-d H:i:s'):null;
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
        return $this->hasOne('ActComment','aid')->field('aid,count(*) as comment_count')->group('aid');
    }

    //是否点过赞
    public function linkIsPraise()
    {
        return $this->hasOne('ActPraise','aid')->whereNotNull('praise_date');
    }

    //参加人
    public function linkJoinCount()
    {
        return $this->hasOne('ActJoin','aid')->field('aid,count(*) as join_count')->group('aid');
    }

}