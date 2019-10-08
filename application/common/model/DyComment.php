<?php
namespace app\common\model;

class DyComment extends BaseModel
{

    protected $name='dy_comment';

    /**
     * 评论
     * @param Users $user_model;
     * @param array $data;
     * @throws
     * @return DyComPraise
     * */
    public static function praise(Users $user_model,array $data=[])
    {
        empty($data['id']) && exception('对象异常:id');
        $com_model = self::get($data['id']);
        empty($com_model) && exception('评论信息异常');

        $model = DyComPraise::where(['uid'=>$user_model->id,'com_id'=>$data['id']])->find();
        if(empty($model)){
            $model = new DyComPraise();
        }

        $model->uid = $user_model->id;
        $model->dy_id = $com_model['dy_id'];
        $model->com_id = $data['id'];
        $model->praise_date = empty($model->praise_date)?date('Y-m-d H:i:s'):null;
        $model->save();
        //点赞次数增加
        $model->praise_date?$com_model->setInc('praise_times'):$com_model->setDec('praise_times');

        return $model;
    }


    //评论返回内容
    public function structInfo()
    {

        try{
            $link_user = $this->getData('link_users');
        }catch (\Exception $e){
            $link_user = $this->linkUsers;
        }
        try{
            $link_to_user = $this->getData('link_to_users');
        }catch (\Exception $e){
            $link_to_user = $this->linkToUsers;
        }
        try{
            $link_praise = $this->getData('link_praise');
        }catch (\Exception $e){
            $link_praise = $this->linkPraise;
        }
        try{
            $link_is_praise = $this->getData('link_is_praise');
        }catch (\Exception $e){
            $link_is_praise = 0;
        }

        return [
            'id' => $this['id'],
            'uid' => $this['uid'],
            'pid' => $this['pid'],
            'praise_num' => empty($link_praise['comment_count'])?0:$link_praise['comment_count'],
            'is_praise' => empty($link_is_praise)?0:1,
            'u_name' => (string)$link_user['name'],
            'u_face' => (string)$link_user['face'],
            'to_uid' => $this['to_uid'],
            'to_name' => (string)$link_to_user['name'],
            'to_face' => (string)$link_to_user['face'],
            'content' => (string)$this['content'],
            'date' => $this['create_time'],
        ];
    }


    //提供删除评论信息
    public function getDelIntro()
    {
        return '删除动态评论';
    }

    //是否点过赞
    public function linkIsPraise()
    {
        return $this->hasOne('DyComPraise','com_id')->whereNotNull('praise_date');
    }

    //动态评论用户
    public function linkPraise()
    {
        return $this->hasOne('DyComPraise','com_id')->whereNotNull('praise_date')->field('com_id,count(*) as comment_count')->group('com_id');
    }
    //动态评论用户
    public function linkChild()
    {
        return $this->hasMany('DyComment','pid');
    }
    //动态评论用户
    public function linkUsers()
    {
        return $this->belongsTo('Users','uid');
    }
    //动态回复用户
    public function linkToUsers()
    {
        return $this->belongsTo('Users','to_uid');
    }
}