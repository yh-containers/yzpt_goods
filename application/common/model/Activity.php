<?php
namespace app\common\model;

use think\model\concern\SoftDelete;
use think\Validate;

class Activity extends BaseModel
{
    use SoftDelete;
    protected $name='activity';
    public static $fields_online=['线上','线下'];

    //发布时间
    protected function getReleaseDateAttr()
    {
        return $this->create_time;
    }

    protected function getImgAttr($value)
    {
        return self::handleFile($value);
    }

    protected function getUserNumAttr($value)
    {
        return empty($value)?[]:explode('-',$value);
    }
    protected function getUserNumStrAttr()
    {
        $num = $this->getAttr('user_num');
        $str = '';
        if(count($num)==2){
            $str = ''.$num[0].'-'.$num[1].'名';
        }elseif(count($num)==1){
            $str = ''.$num[0].'名';
        }
        return $str;
    }

    protected function getStartDateAttr($value)
    {
        return empty($value)?'':substr($value,0,10);
    }


    protected function getEndDateAttr($value)
    {
        return empty($value)?'':substr($value,0,10);
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



    /**
     * 参加活动
     * @param Users $user_model;
     * @param array $data;
     * @throws
     * @return ActJoin
     * */
    public static function joinUs(Users $user_model,array $data=[])
    {
        empty($data['aid']) && exception('对象异常:id');
        $model = ActJoin::where(['uid'=>$user_model->id,'aid'=>$data['aid']])->find();
        !empty($model) && exception('您已报名活动,无需再次操作');

        unset($data['id']);
        $data['uid'] = $user_model->id;
        $model = new ActJoin();
        $validate =new \app\common\validate\ActJoin();
        $validate->scene('api_join');
        $model->actionAdd($data,$validate);
//        $model->aid = $data['id'];
//        $model->praise_date = empty($model->praise_date)?date('Y-m-d H:i:s'):null;
//        $model->save();
        return $model;
    }


    /**
     * 删除
     * @param Users $user_model|null;
     * @param array $data;
     * @throws
     * */
    public static function Del(Users $user_model=null,array $data=[])
    {
        empty($data['id']) && exception('对象异常:id');
        $model = self::get($data['id']);
        empty($model) && exception('已删除');
        if(!empty($user_model) && $user_model['id']!=$model['uid']){
            exception('无法进行此操作');
        }
        //直接删除
        $model->delete();
    }


    /**
     * 取消
     * @param Users $user_model|null;
     * @param array $data;
     * @throws
     * */
    public static function cancel(Users $user_model=null,array $data=[])
    {
        empty($data['aid']) && exception('对象异常:id',0);
        $model = ActJoin::where(['uid'=>$user_model->id,'aid'=>$data['aid']])->find();
        empty($model) && exception('已取消',1);
        //直接删除
        $model->delete();
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

    //参加状态
    public function linkIsJoin()
    {
        return $this->hasOne('ActJoin','aid');
    }

}