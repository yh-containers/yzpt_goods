<?php
namespace app\common\model;

class UsersBlack extends BaseModel
{
    protected $name='users_black';


    /**
     * 加入或删除黑名单
     * @param $user_model Users 用户模型
     * @param $uid int 被操作的用户
     * @throws
     * @return int
     * */
    public static function JoinOrDel(Users $user_model,$uid)
    {
        if($user_model->id ==$uid){
            exception('无法操作自己');
        }
        $state = 0;
        $model = self::where(['uid'=>$user_model['id'],'b_uid'=>$uid])->find();
        if($model){
            $model->delete();
        }else{
            $model = new self();
            $model->uid = $user_model['id'];
            $model->b_uid = $uid;
            $model->save();
            $state=1;
        }
        return $state;
    }

    public function linkUsers()
    {
        return $this->belongsTo('Users','b_uid');
    }
}